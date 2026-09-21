<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\SaleItem;
use App\Models\MedicineBatch;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    protected ShiftService $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'shift_id' => 'nullable|exists:shifts,id',  // ✅ جديد
            'reason' => 'nullable|string',
            'created_at' => 'nullable|date',  // ✅ جديد
            'items' => 'required|array|min:1',
            'items.*.medicine_batch_id' => 'required|integer|exists:medicine_batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            
        ]);

        $sale = Sale::find($request->sale_id);
        if (!$sale) {
            return response()->json(['message' => 'الفاتورة غير موجودة'], 404);
        }

        $originalPaymentMethod = $sale->payment_method ?? 'cash';

        $totalRefunded = Refund::where('sale_id', $sale->id)->sum('amount');
        if ($totalRefunded >= $sale->total_amount) {
            return response()->json(['message' => 'هذه الفاتورة تم إرجاعها بالكامل مسبقاً.'], 409);
        }

        DB::beginTransaction();
        try {
            $refundTime = $request->filled('created_at')
                ? \Carbon\Carbon::parse($request->created_at)
                : now();

            $refund = Refund::create([
                'sale_id'    => $sale->id,
                'amount'     => 0,
                'reason'     => $request->reason ?? 'إرجاع',
                'created_at' => $refundTime,  // ✅
            ]);

            $totalRefundAmount = 0;

            foreach ($request->items as $itemData) {
                $saleItem = SaleItem::where('sale_id', $sale->id)
                    ->where('medicine_batch_id', $itemData['medicine_batch_id'])
                    ->first();

                if (!$saleItem) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'هذا الصنف غير موجود ضمن هذه الفاتورة'
                    ], 422);
                }

                $refundedQty = RefundItem::where('sale_item_id', $saleItem->id)->sum('quantity');
                $maxRefundable = $saleItem->quantity - $refundedQty;

                if ($maxRefundable <= 0) {
                    DB::rollBack();
                    return response()->json(['message' => 'تم إرجاع هذا الصنف بالكامل مسبقاً'], 409);
                }

                if ($itemData['quantity'] > $maxRefundable) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "الكمية المرتجعة تتجاوز الكمية المتاحة (الحد الأقصى: {$maxRefundable})"
                    ], 409);
                }

                $refundAmount = $saleItem->price * $itemData['quantity'];
                $totalRefundAmount += $refundAmount;

                RefundItem::create([
                    'refund_id' => $refund->id,
                    'sale_item_id' => $saleItem->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $saleItem->price
                ]);

                $batch = MedicineBatch::find($saleItem->medicine_batch_id);
                if ($batch) {
                    $factor = $saleItem->quantity > 0
                        ? ($saleItem->quantity_base / $saleItem->quantity)
                        : 1;
                    $baseQty = (int) round($itemData['quantity'] * $factor);

                    // ✅ استعادة الكمية إلى الدفعة
                    $batch->increment('remaining_quantity', $baseQty);

                    // ✅ إصلاح #1: استعادة الكمية إلى inventories.quantity
                    $inventory = Inventory::where('medicine_id', $batch->medicine_id)
                        ->where('branch_id', $batch->branch_id)
                        ->first();
                    if ($inventory) {
                        $inventory->increment('quantity', $baseQty);
                    }

                    InventoryLog::create([
                        'medicine_batch_id' => $batch->id,
                        'type'              => 'REFUND',
                        'quantity_changed'  => $baseQty,
                        'notes'             => "إرجاع صنف من فاتورة #{$sale->id}",
                    ]);
                }
            }

            $refund->update(['amount' => $totalRefundAmount]);

            /*
            |--------------------------------------------------------------------------
            | تحديث الوردية عبر ShiftService — المصدر الوحيد للحقيقة
            |--------------------------------------------------------------------------
            | registerRefund يضيف إلى refund_amount فقط، ثم يُعيد الحساب.
            | لا نخصم من cash_sales — الخصم يحدث ضمن المعادلة الموحّدة.
            |--------------------------------------------------------------------------
            */
           $updatedShift = null;

            // ✅ أولاً: shift_id المُرسَل (من المزامنة Offline)
            $targetShiftId = $request->input('shift_id');

            // ✅ ثانياً: الوردية المفتوحة حالياً (البيع الأصلي كان نقدي ولكن ضمن وردية مفتوحة)
            if (!$targetShiftId) {
                $currentOpenShift = Shift::where('user_id', auth()->id())
                    ->where('status', 'open')
                    ->latest('id')
                    ->first();
                $targetShiftId = $currentOpenShift?->id;
            }

            // ✅ ثالثاً: كحل احتياطي (نادر)، الوردية الأصلية للفاتورة
            if (!$targetShiftId) {
                $targetShiftId = $sale->shift_id;
            }

            if ($targetShiftId) {
                $shift = Shift::find($targetShiftId);
                if ($shift) {
                    $this->shiftService->registerRefund(
                        $totalRefundAmount,
                        $shift,
                        $originalPaymentMethod
                    );
                    $updatedShift = $shift->fresh();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'تم الإرجاع بنجاح',
                'refund' => $refund->load('items'),
                'original_payment_method' => $originalPaymentMethod,
                'refund_amount' => $totalRefundAmount,
                'shift' => $updatedShift
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'message' => 'حدث خطأ غير متوقع أثناء تنفيذ الإرجاع'
            ], 500);
        }
    }

    public function getSaleDetails($id)
    {
        $sale = Sale::with(['items.batch.medicine', 'items.unit'])->find($id);
        if (!$sale) {
            return response()->json(['message' => 'الفاتورة غير موجودة'], 404);
        }

        $totalRefunded = Refund::where('sale_id', $id)->sum('amount');
        $sale->total_refunded = $totalRefunded;

        foreach ($sale->items as $item) {
            $refundedQty = RefundItem::where('sale_item_id', $item->id)->sum('quantity');
            $item->refunded_quantity = $refundedQty;
            $item->remaining_quantity_for_refund = $item->quantity - $refundedQty;
        }

        return response()->json($sale);
    }
}