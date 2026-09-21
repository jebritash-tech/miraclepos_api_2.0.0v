<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{
    Medicine, MedicineBatch, Purchase, PurchaseItem,
    Sale, SaleItem, Supplier, Category, User, Shift,
    Expense, Withdrawal, EmployeeDebt, Salary, Inventory,
    Refund, RefundItem, Debt
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * نقطة دخول موحدة: /api/search/details/{type}/{id}
     */
    public function details($type, $id)
    {
        return match ($type) {
            'medicine' => $this->medicineDetails((int) $id),
            'sale'     => $this->saleDetails((int) $id),
            'purchase' => $this->purchaseDetails((int) $id),
            'supplier' => $this->supplierDetails((int) $id),
            'category' => $this->categoryDetails((int) $id),
            'user'     => $this->userDetails((int) $id),
            'batch'    => $this->batchDetails((int) $id),
            default    => response()->json(['message' => 'نوع غير مدعوم'], 400),
        };
    }

    // ============================================================
    // 1. الدواء — كل شيء عنه
    // ============================================================
    private function medicineDetails(int $id)
    {
        $medicine = Medicine::with(['category', 'units.unit'])->find($id);
        if (!$medicine) {
            return response()->json(['message' => 'الدواء غير موجود'], 404);
        }

        // الدفعات الحالية
        $batches = MedicineBatch::where('medicine_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($b) => [
                'id'                => $b->id,
                'batch_number'      => $b->batch_number,
                'expiry_date'       => $b->expiry_date,
                'buy_price'         => (float) $b->buy_price,
                'quantity'          => (float) $b->quantity,
                'remaining_quantity'=> (float) $b->remaining_quantity,
                'branch_id'         => $b->branch_id,
                'is_expired'        => $b->expiry_date && strtotime($b->expiry_date) < time(),
            ]);

        // المشتريات
        $purchases = PurchaseItem::where('medicine_id', $id)
            ->with(['purchase.supplier', 'unit'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'purchase_id'    => $p->purchase_id,
                'supplier_id'    => $p->purchase?->supplier_id,
                'supplier_name'  => $p->purchase?->supplier?->name,
                'quantity'       => (float) $p->quantity,
                'buy_price'      => (float) $p->buy_price,
                'subtotal'       => (float) $p->subtotal,
                'unit'           => $p->unit?->name ?? $p->unit?->symbol,
                'purchase_date'  => $p->purchase?->purchase_date,
                'created_at'     => $p->created_at?->toIso8601String(),
            ]);

        // الفواتير التي بيع فيها
        $sales = SaleItem::whereHas('batch', fn($q) => $q->where('medicine_id', $id))
            ->with(['sale.user'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'sale_id'        => $s->sale_id,
                'quantity'       => (int) $s->quantity,
                'unit'           => $s->unit,
                'price'          => (float) $s->price,
                'profit'         => (float) ($s->profit ?? 0),
                'payment_method' => $s->sale?->payment_method,
                'user_name'      => $s->sale?->user?->name,
                'created_at'     => $s->created_at?->toIso8601String(),
            ]);

        // الموردون
        $suppliers = Supplier::whereIn('id',
                PurchaseItem::where('medicine_id', $id)
                    ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                    ->pluck('purchases.supplier_id')
                    ->unique()
                    ->filter()
            )->get(['id', 'name', 'phone']);

        // المخزون حسب الفرع
        $stock = Inventory::where('medicine_id', $id)
            ->with('branch:id,name')
            ->get()
            ->map(fn($i) => [
                'branch_id'        => $i->branch_id,
                'branch_name'      => $i->branch?->name,
                'quantity'         => (int) $i->quantity,
                'minimum_quantity' => (int) $i->minimum_quantity,
                'is_low'           => $i->quantity <= $i->minimum_quantity,
            ]);

        // إحصائيات
        $stats = [
            'total_purchased'  => (float) PurchaseItem::where('medicine_id', $id)->sum('quantity'),
            'total_sold'       => (int)   SaleItem::whereHas('batch', fn($q) => $q->where('medicine_id', $id))->sum('quantity'),
            'total_revenue'    => (float) DB::table('sale_items')
                ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
                ->where('medicine_batches.medicine_id', $id)
                ->selectRaw('SUM(sale_items.quantity * sale_items.price) as t')->value('t') ?? 0,
            'total_profit'     => (float) SaleItem::whereHas('batch', fn($q) => $q->where('medicine_id', $id))->sum('profit'),
            'current_stock'    => (float) MedicineBatch::where('medicine_id', $id)->sum('remaining_quantity'),
            'suppliers_count'  => $suppliers->count(),
            'batches_count'    => $batches->count(),
        ];

        return response()->json([
            'type'      => 'medicine',
            'medicine'  => [
                'id'              => $medicine->id,
                'name'            => $medicine->name,
                'scientific_name' => $medicine->scientific_name,
                'notes'           => $medicine->notes,
                'category'        => $medicine->category ? [
                    'id'   => $medicine->category->id,
                    'name' => $medicine->category->name,
                ] : null,
                'units' => $medicine->units->map(fn($u) => [
                    'id'     => $u->id,
                    'factor' => $u->factor,
                    'name'   => $u->unit?->name,
                    'barcode'=> $u->barcode,
                ]),
            ],
            'stats'     => $stats,
            'stock'     => $stock,
            'batches'   => $batches,
            'purchases' => $purchases,
            'sales'     => $sales,
            'suppliers' => $suppliers,
        ]);
    }

    // ============================================================
    // 2. الفاتورة — إعادة استخدام منطق getSaleDetails
    // ============================================================
    private function saleDetails(int $id)
    {
        $sale = Sale::with(['items.batch.medicine', 'user', 'shift'])->find($id);
        if (!$sale) return response()->json(['message' => 'الفاتورة غير موجودة'], 404);

        $totalRefunded = (float) Refund::where('sale_id', $id)->sum('amount');

        $sale->items->each(function ($item) {
            $rq = (int) RefundItem::where('sale_item_id', $item->id)->sum('quantity');
            $item->refunded_quantity = $rq;
            $item->remaining_quantity_for_refund = max(0, (int) $item->quantity - $rq);
        });

        return response()->json([
            'type' => 'sale',
            'sale' => [
                'id'                 => $sale->id,
                'created_at'         => $sale->created_at?->toIso8601String(),
                'total_amount'       => (float) $sale->total_amount,
                'profit_amount'      => (float) ($sale->profit_amount ?? 0),
                'payment_method'     => $sale->payment_method ?? 'cash',
                'bank_name'          => $sale->bank_name,
                'bank_reference'     => $sale->bank_reference,
                'bank_transfer_date' => $sale->bank_transfer_date,
                'bank_notes'         => $sale->bank_notes,
                'shift_id'           => $sale->shift_id,
                'user'               => $sale->user ? ['id' => $sale->user->id, 'name' => $sale->user->name] : null,
                'shift'              => $sale->shift ? ['id' => $sale->shift->id, 'status' => $sale->shift->status] : null,
                'total_refunded'     => $totalRefunded,
                'items'              => $sale->items->map(fn($item) => [
                    'id'                => $item->id,
                    'medicine_batch_id' => $item->medicine_batch_id,
                    'quantity'          => (int) $item->quantity,
                    'quantity_base'     => (int) ($item->quantity_base ?? 0),
                    'unit'              => $item->unit,
                    'price'             => (float) $item->price,
                    'profit'            => (float) ($item->profit ?? 0),
                    'refunded_quantity' => $item->refunded_quantity,
                    'remaining_quantity_for_refund' => $item->remaining_quantity_for_refund,
                    'batch' => $item->batch ? [
                        'id'           => $item->batch->id,
                        'batch_number' => $item->batch->batch_number,
                        'expiry_date'  => $item->batch->expiry_date,
                        'medicine'     => $item->batch->medicine ? [
                            'id'              => $item->batch->medicine->id,
                            'name'            => $item->batch->medicine->name,
                            'scientific_name' => $item->batch->medicine->scientific_name,
                        ] : null,
                    ] : null,
                ]),
            ],
        ]);
    }

    // ============================================================
    // 3. المورد — كل فواتيره
    // ============================================================
    private function supplierDetails(int $id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) return response()->json(['message' => 'المورد غير موجود'], 404);

        $purchases = Purchase::where('supplier_id', $id)
            ->with(['items.medicine'])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'invoice_number' => $p->invoice_number,
                'purchase_date'  => $p->purchase_date,
                'total_amount'   => (float) $p->total_amount,
                'discount'       => (float) $p->discount,
                'notes'          => $p->notes,
                'items_count'    => $p->items->count(),
                'items'          => $p->items->take(5)->map(fn($it) => [
                    'medicine_name' => $it->medicine?->name,
                    'quantity'      => (float) $it->quantity,
                    'subtotal'      => (float) $it->subtotal,
                ]),
                'created_at'     => $p->created_at?->toIso8601String(),
            ]);

        $stats = [
            'total_purchases' => $purchases->count(),
            'total_amount'    => (float) Purchase::where('supplier_id', $id)->sum('total_amount'),
            'total_items'     => (int)   PurchaseItem::whereHas('purchase', fn($q) => $q->where('supplier_id', $id))->sum('quantity'),
            'last_purchase'   => $purchases->first()['purchase_date'] ?? null,
        ];

        return response()->json([
            'type'      => 'supplier',
            'supplier'  => [
                'id'             => $supplier->id,
                'name'           => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'phone'          => $supplier->phone,
                'email'          => $supplier->email,
                'address'        => $supplier->address,
            ],
            'stats'     => $stats,
            'purchases' => $purchases,
        ]);
    }

    // ============================================================
    // 4. التصنيف — كل الأدوية المرتبطة
    // ============================================================
    private function categoryDetails(int $id)
    {
        $category = Category::find($id);
        if (!$category) return response()->json(['message' => 'التصنيف غير موجود'], 404);

        $medicines = Medicine::where('category_id', $id)
            ->with(['units.unit'])
            ->get()
            ->map(function ($m) {
                $stock = (float) MedicineBatch::where('medicine_id', $m->id)->sum('remaining_quantity');
                return [
                    'id'               => $m->id,
                    'name'             => $m->name,
                    'scientific_name'  => $m->scientific_name,
                    'current_stock'    => $stock,
                    'units_count'      => $m->units->count(),
                ];
            });

        return response()->json([
            'type'     => 'category',
            'category' => ['id' => $category->id, 'name' => $category->name],
            'stats'    => ['medicines_count' => $medicines->count()],
            'medicines'=> $medicines,
        ]);
    }

    // ============================================================
    // 5. المستخدم — كل شيء مرتبط به
    // ============================================================
    private function userDetails(int $id)
    {
        $user = User::with('branch')->find($id);
        if (!$user) return response()->json(['message' => 'المستخدم غير موجود'], 404);

        $shifts = Shift::where('user_id', $id)->orderByDesc('opened_at')->limit(20)
            ->get(['id', 'opening_cash', 'closing_cash', 'cash_sales', 'card_sales',
                   'sales_count', 'status', 'opened_at', 'closed_at']);

        $sales = Sale::where('user_id', $id)->orderByDesc('created_at')->limit(20)
            ->get(['id', 'total_amount', 'profit_amount', 'payment_method', 'created_at']);

        $expenses = Expense::where('user_id', $id)->orderByDesc('created_at')->limit(20)
            ->get(['id', 'title', 'amount', 'notes', 'created_at']);

        $withdrawals = Withdrawal::where('user_id', $id)->orderByDesc('created_at')->limit(20)
            ->get(['id', 'amount', 'reason', 'created_at']);

        $employeeDebts = EmployeeDebt::where('user_id', $id)->orderByDesc('created_at')->limit(20)
            ->get(['id', 'amount', 'paid_amount', 'reason', 'status', 'created_at']);

        $salaries = Salary::where('user_id', $id)->orderByDesc('year')->orderByDesc('month')->limit(12)
            ->get(['id', 'month', 'year', 'net_salary', 'status', 'paid_at']);

        $stats = [
            'total_sales'         => (float) Sale::where('user_id', $id)->sum('total_amount'),
            'total_profit'        => (float) Sale::where('user_id', $id)->sum('profit_amount'),
            'invoices_count'      => Sale::where('user_id', $id)->count(),
            'shifts_count'        => Shift::where('user_id', $id)->count(),
            'expenses_total'      => (float) Expense::where('user_id', $id)->sum('amount'),
            'withdrawals_total'   => (float) Withdrawal::where('user_id', $id)->sum('amount'),
            'employee_debts_open' => (float) EmployeeDebt::where('user_id', $id)->where('status', '!=', 'paid')
                ->sum(DB::raw('amount - paid_amount')),
        ];

        return response()->json([
            'type'  => 'user',
            'user'  => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'salary'     => (float) $user->salary,
                'is_active'  => (bool) $user->is_active,
                'branch'     => $user->branch ? ['id' => $user->branch->id, 'name' => $user->branch->name] : null,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'stats'          => $stats,
            'shifts'         => $shifts,
            'sales'          => $sales,
            'expenses'       => $expenses,
            'withdrawals'    => $withdrawals,
            'employee_debts' => $employeeDebts,
            'salaries'       => $salaries,
        ]);
    }

    // ============================================================
    // 6. الدفعة (LOT) — حركاتها
    // ============================================================
    private function batchDetails(int $id)
    {
        $batch = MedicineBatch::with('medicine')->find($id);
        if (!$batch) return response()->json(['message' => 'الدفعة غير موجودة'], 404);

        $sales = SaleItem::where('medicine_batch_id', $id)
            ->with(['sale.user'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'sale_id'    => $s->sale_id,
                'quantity'   => (int) $s->quantity,
                'price'      => (float) $s->price,
                'user_name'  => $s->sale?->user?->name,
                'created_at' => $s->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'type'  => 'batch',
            'batch' => [
                'id'                 => $batch->id,
                'batch_number'       => $batch->batch_number,
                'expiry_date'        => $batch->expiry_date,
                'buy_price'          => (float) $batch->buy_price,
                'quantity'           => (float) $batch->quantity,
                'remaining_quantity' => (float) $batch->remaining_quantity,
                'medicine'           => $batch->medicine ? [
                    'id'   => $batch->medicine->id,
                    'name' => $batch->medicine->name,
                ] : null,
            ],
            'stats' => [
                'sold_quantity'   => (int) $sales->sum('quantity'),
                'remaining'       => (float) $batch->remaining_quantity,
                'revenue'         => (float) $sales->sum(fn($s) => $s['quantity'] * $s['price']),
            ],
            'sales' => $sales,
        ]);
    }
}