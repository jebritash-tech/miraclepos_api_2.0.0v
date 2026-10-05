<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineBatch;
use App\Models\MedicinePrice;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryCleanupController extends Controller
{
    /* ============================================================
       POST /api/inventory/cleanup/batch/{id} — حذف دفعة كاملة
       ============================================================ */
    public function deleteBatch(Request $request, int $id)
    {
        $batch = MedicineBatch::with('medicine')->find($id);
        if (!$batch) {
            return response()->json(['message' => 'الدفعة غير موجودة'], 404);
        }

        // 🔒 تحقق أمني: هل تم البيع من هذه الدفعة؟
        $salesCount = DB::table('sale_items')
            ->where('medicine_batch_id', $id)
            ->count();

        if ($salesCount > 0) {
            return response()->json([
                'message' => "لا يمكن حذف الدفعة — تم إجراء {$salesCount} عملية بيع منها. حذفها سيؤدي إلى فقدان البيانات المحاسبية.",
                'code'    => 'HAS_SALES',
                'sales_count' => $salesCount,
            ], 409);
        }

        // 🔒 تحقق إضافي: هل تم إرجاع من هذه الدفعة؟
        $refundsCount = DB::table('refund_items')
            ->join('sale_items', 'refund_items.sale_item_id', '=', 'sale_items.id')
            ->where('sale_items.medicine_batch_id', $id)
            ->count();

        if ($refundsCount > 0) {
            return response()->json([
                'message' => 'لا يمكن حذف الدفعة — توجد مرتجعات مرتبطة بها.',
                'code'    => 'HAS_REFUNDS',
            ], 409);
        }

        DB::beginTransaction();
        try {
            $summary = [
                'batch_number' => $batch->batch_number,
                'medicine'     => $batch->medicine?->name,
                'quantity'     => (float) $batch->remaining_quantity,
            ];

            // حذف السجلات المرتبطة بالدفعة
            DB::table('inventory_movements')->where('batch_id', $id)->delete();
            DB::table('inventory_logs')->where('medicine_batch_id', $id)->delete();
            DB::table('medicine_prices')->where('batch_id', $id)->delete();

            // تحديث المخزون الإجمالي (inventories.quantity)
            $baseQty = (float) $batch->remaining_quantity;
            if ($batch->medicine_id && $baseQty > 0) {
                DB::table('inventories')
                    ->where('medicine_id', $batch->medicine_id)
                    ->where('branch_id', $batch->branch_id)
                    ->decrement('quantity', $baseQty);
            }

            // حذف الدفعة
            $batch->delete();

            AuditLogger::critical(
                'batch_deleted',
                null,
                "حذف دفعة ...",
                [],
                [],
                ['branch_id' => $batch->branch_id]
            );

            DB::commit();

            return response()->json([
                'message' => 'تم حذف الدفعة بنجاح',
                'summary' => $summary,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'تعذر حذف الدفعة: ' . $e->getMessage()], 500);
        }
    }

    /* ============================================================
       POST /api/inventory/cleanup/purchase/{id} — حذف فاتورة شراء كاملة
       ============================================================ */
    public function deletePurchase(Request $request, int $id)
    {
        $purchase = Purchase::with(['items', 'supplier'])->find($id);
        if (!$purchase) {
            return response()->json(['message' => 'فاتورة الشراء غير موجودة'], 404);
        }

        // 🔒 جمع كل الـ batch IDs المرتبطة بهذه الفاتورة
        $batchIds = DB::table('medicine_batches')
            ->where('purchase_item_id', '!=', null)
            ->whereIn('purchase_item_id', $purchase->items->pluck('id'))
            ->pluck('id');

        if ($batchIds->isEmpty()) {
            // لا توجد دفعات — يمكن الحذف مباشرة
            return $this->performPurchaseDelete($purchase, collect([]));
        }

        // 🔒 هل تم البيع من أي دفعة؟
        $salesCount = DB::table('sale_items')
            ->whereIn('medicine_batch_id', $batchIds)
            ->count();

        if ($salesCount > 0) {
            return response()->json([
                'message' => "لا يمكن حذف الفاتورة — تم البيع من دفعاتها ({$salesCount} عملية بيع). حذف الفاتورة سيؤدي إلى فقدان البيانات المحاسبية.",
                'code'    => 'HAS_SALES',
                'sales_count' => $salesCount,
                'batches_count' => $batchIds->count(),
            ], 409);
        }

        DB::beginTransaction();
        try {
            $summary = [
                'invoice_number' => $purchase->invoice_number,
                'supplier'       => $purchase->supplier?->name,
                'total_amount'   => (float) $purchase->total_amount,
                'items_count'    => $purchase->items->count(),
                'batches_count'  => $batchIds->count(),
            ];

            // حذف الجداول المرتبطة
            DB::table('inventory_movements')->whereIn('batch_id', $batchIds)->delete();
            DB::table('inventory_logs')->whereIn('medicine_batch_id', $batchIds)->delete();
            DB::table('medicine_prices')->whereIn('batch_id', $batchIds)->delete();

            // تصفير المخزون المرتبط
            foreach ($purchase->items as $item) {
                $batchQty = DB::table('medicine_batches')
                    ->where('purchase_item_id', $item->id)
                    ->sum('remaining_quantity');

                if ($item->medicine_id && $batchQty > 0) {
                    DB::table('inventories')
                        ->where('medicine_id', $item->medicine_id)
                        ->where('branch_id', $purchase->branch_id)
                        ->decrement('quantity', $batchQty);
                }
            }

            // حذف الدفعات
            DB::table('medicine_batches')
                ->whereIn('id', $batchIds)
                ->delete();

            // حذف بنود الفاتورة
            DB::table('purchase_items')->where('purchase_id', $id)->delete();

            // حذف الفاتورة
            $purchase->delete();

            AuditLogger::critical(
                'purchase_deleted',
                null,
                "حذف فاتورة شراء {$summary['invoice_number']} من {$summary['supplier']} — إجمالي: {$summary['total_amount']}",
                ['branch_id' => $purchase->branch_id]
            );

            DB::commit();

            return response()->json([
                'message' => 'تم حذف فاتورة الشراء وجميع سجلاتها بنجاح',
                'summary' => $summary,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'تعذر حذف الفاتورة: ' . $e->getMessage()], 500);
        }
    }

    private function performPurchaseDelete(Purchase $purchase, $batchIds)
    {
        DB::beginTransaction();
        try {
            DB::table('purchase_items')->where('purchase_id', $purchase->id)->delete();
            $purchase->delete();
            DB::commit();
            return response()->json(['message' => 'تم حذف الفاتورة (بدون دفعات)']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* ============================================================
       GET /api/inventory/cleanup/batch/{id}/check — فحص إمكانية الحذف
       ============================================================ */
    public function checkBatch(int $id)
    {
        $batch = MedicineBatch::with('medicine')->find($id);
        if (!$batch) return response()->json(['message' => 'الدفعة غير موجودة'], 404);

        $salesCount = DB::table('sale_items')->where('medicine_batch_id', $id)->count();
        $refundsCount = DB::table('refund_items')
            ->join('sale_items', 'refund_items.sale_item_id', '=', 'sale_items.id')
            ->where('sale_items.medicine_batch_id', $id)
            ->count();

        return response()->json([
            'batch'         => [
                'id'           => $batch->id,
                'batch_number' => $batch->batch_number,
                'medicine'     => $batch->medicine?->name,
                'quantity'     => (float) $batch->remaining_quantity,
                'expiry_date'  => $batch->expiry_date,
            ],
            'can_delete'    => $salesCount === 0 && $refundsCount === 0,
            'sales_count'   => $salesCount,
            'refunds_count' => $refundsCount,
            'reason'        => $salesCount > 0 ? 'HAS_SALES'
                              : ($refundsCount > 0 ? 'HAS_REFUNDS' : null),
        ]);
    }

    /* ============================================================
       GET /api/inventory/cleanup/purchase/{id}/check
       ============================================================ */
    public function checkPurchase(int $id)
    {
        $purchase = Purchase::with(['items', 'supplier'])->find($id);
        if (!$purchase) return response()->json(['message' => 'الفاتورة غير موجودة'], 404);

        $itemIds = $purchase->items->pluck('id');
        $batchIds = DB::table('medicine_batches')
            ->whereIn('purchase_item_id', $itemIds)
            ->pluck('id');

        $salesCount = $batchIds->isEmpty() ? 0
            : DB::table('sale_items')->whereIn('medicine_batch_id', $batchIds)->count();

        return response()->json([
            'purchase' => [
                'id'             => $purchase->id,
                'invoice_number' => $purchase->invoice_number,
                'supplier'       => $purchase->supplier?->name,
                'total_amount'   => (float) $purchase->total_amount,
                'items_count'    => $purchase->items->count(),
            ],
            'batches_count' => $batchIds->count(),
            'sales_count'   => $salesCount,
            'can_delete'    => $salesCount === 0,
            'reason'        => $salesCount > 0 ? 'HAS_SALES' : null,
        ]);
    }
}