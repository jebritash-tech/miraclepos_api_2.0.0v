<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\MedicineBatch;
use App\Models\InventoryLog;
use App\Models\Shift;
use App\Models\MedicineUnit;
use App\Models\Inventory;
use Illuminate\Support\Carbon;
use App\Services\SaleService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    protected SaleService $saleService;

    public function __construct(
        SaleService $saleService
    ) {
        $this->saleService =
            $saleService;
    }

   public function getSaleDetails($id)
    {
        $sale = Sale::with(['items.batch.medicine', 'items.unit'])->find($id);
        if (!$sale) {
            return response()->json(['message' => 'الفاتورة غير موجودة'], 404);
        }

        // حساب إجمالي المبلغ المرتجع
        $totalRefunded = Refund::where('sale_id', $id)->sum('total_amount');
        $sale->total_refunded = $totalRefunded;

        // حساب الكمية المرتجعة لكل صنف
        foreach ($sale->items as $item) {
            $refundedQty = RefundItem::where('sale_item_id', $item->id)->sum('quantity');
            $item->refunded_quantity = $refundedQty;
            $item->remaining_quantity_for_refund = $item->quantity - $refundedQty;
        }

        return response()->json($sale);
    }
    /*
    |--------------------------------------------------------------------------
    | Store Sale
    |--------------------------------------------------------------------------
    */

   public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $branchId = $request->input('branch_id', $user->branch_id);
        $requestedShiftId = $request->input('shift_id');

        /* ============================================================
        Resolve Shift
        ============================================================ */
        if ($requestedShiftId !== null && $requestedShiftId !== '') {
            $shift = Shift::where('id', $requestedShiftId)
                ->where('user_id', $user->id)
                ->where('branch_id', $branchId)
                ->first();

            if (!$shift) {
                return response()->json([
                    'message' => 'The selected shift does not belong to the authenticated user or branch.'
                ], 422);
            }
        } else {
            $shift = Shift::where('user_id', $user->id)
                ->where('branch_id', $branchId)
                ->whereNull('closed_at')
                ->where('status', 'open')
                ->latest('id')
                ->first();

            if (!$shift) {
                return response()->json(['message' => 'No open shift is available.'], 422);
            }
        }

        $request->merge([
            'shift_id'  => $shift->id,
            'branch_id' => $shift->branch_id,
        ]);

        /* ============================================================
        Validation — يدعم الخصم المزدوج
        ============================================================ */
        $request->validate([
            'shift_id'                => 'required|exists:shifts,id',
            'branch_id'               => 'required|exists:branches,id',
            'payment_method'          => 'required|string',
            'bank_transfer'           => 'nullable',
            'items'                   => 'required|array|min:1',

            'items.*.medicine_batch_id' => 'required|exists:medicine_batches,id',
            'items.*.medicine_unit_id'  => ['required', Rule::exists('medicine_units', 'unit_id')],
            'items.*.quantity'          => 'required|numeric|min:0.01',
            'items.*.unit'              => 'sometimes|string',
            'items.*.quantity_base'     => 'sometimes|numeric',

            // ✅ خصم مباشر على البند (اختياري)
            'items.*.line_discount'     => 'nullable|numeric|min:0',

            'created_at'              => 'nullable|date',

            // ✅ خصم الفاتورة
            'discount_type'           => 'nullable|in:fixed,percent',
            'discount_value'          => 'nullable|numeric|min:0',
            'discount_reason'         => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            /* ============================================================
            STEP 1 — معالجة البنود الأولية (بدون خصم بعد)
            ============================================================ */
            $subtotalRaw = 0;
            $processedItems = [];

            foreach ($request->items as $itemData) {
                $batch = MedicineBatch::with('prices')
                    ->lockForUpdate()
                    ->findOrFail($itemData['medicine_batch_id']);

                $medicineUnit = MedicineUnit::where('unit_id', $itemData['medicine_unit_id'])
                    ->where('medicine_id', $batch->medicine_id)
                    ->firstOrFail();

                $conversionFactor = $medicineUnit->factor ?? 1;
                $quantityBase = $itemData['quantity'] * $conversionFactor;

                $columnToDecrement = Schema::hasColumn('medicine_batches', 'remaining_quantity')
                    ? 'remaining_quantity'
                    : 'current_stock';

                $availableStock = $batch->{$columnToDecrement} ?? 0;

                if ($availableStock <= 0 || $availableStock < $quantityBase) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Selected batch is out of stock or has insufficient quantity.'
                    ], 422);
                }

                $frontendPrice = $itemData['unit_price'] ?? $itemData['price'] ?? null;
                $priceConfig = $batch->prices->where('unit_id', $medicineUnit->unit_id)->first();

                if (!$priceConfig) {
                    $priceConfig = $batch->prices->first();
                    if (!$priceConfig) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Data Integrity Error: No price configuration found for batch #{$batch->batch_number}."
                        ], 422);
                    }
                }

                $unitPrice = $frontendPrice ?? $priceConfig->sell_price;
                $costPrice = $priceConfig->buy_price ?? $batch->buy_price ?? 0;

                $lineTotal = (float) $itemData['quantity'] * (float) $unitPrice;

                $unitSymbol = strtolower($medicineUnit->unit->symbol ?? '');
                $unitType = match (true) {
                    str_contains($unitSymbol, 'box')   => 'box',
                    str_contains($unitSymbol, 'str')   => 'strip',
                    default                             => 'piece',
                };

                // ✅ الخصم المباشر على البند
                $lineDiscount = (float) ($itemData['line_discount'] ?? 0);

                if ($lineDiscount > $lineTotal) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "خطأ: خصم البند لا يمكن أن يتجاوز سعره ({$lineTotal})."
                    ], 422);
                }

                $subtotalRaw += $lineTotal;

                $processedItems[] = [
                    'batch'                => $batch,
                    'column_to_decrement'  => $columnToDecrement,
                    'medicine_unit_id'     => $itemData['medicine_unit_id'],
                    'quantity'             => (float) $itemData['quantity'],
                    'quantity_base'        => (float) $quantityBase,
                    'price'                => (float) $unitPrice,
                    'cost_price'           => (float) $costPrice,
                    'unit'                 => $unitType,
                    'line_total'           => $lineTotal,
                    'line_discount'        => $lineDiscount,
                    // تُملأ لاحقاً:
                    'invoice_share'        => 0,
                    'effective_total'      => 0,
                    'profit'               => 0,
                ];
            }

            /* ============================================================
            STEP 2 — حساب خصم الفاتورة (invoice discount)
            ============================================================ */
            $discountType   = $request->input('discount_type');     // fixed | percent | null
            $discountValue  = (float) $request->input('discount_value', 0);
            $discountReason = $request->input('discount_reason');

            // subtotal بعد خصومات البنود المباشرة
            $subtotalAfterLineDiscounts = $subtotalRaw
                - collect($processedItems)->sum('line_discount');

            $invoiceDiscountAmount = 0;

            if ($discountType && $discountValue > 0) {
                if ($discountType === 'fixed') {
                    $invoiceDiscountAmount = min($discountValue, $subtotalAfterLineDiscounts);
                } elseif ($discountType === 'percent') {
                    $invoiceDiscountAmount = ($subtotalAfterLineDiscounts * $discountValue) / 100;
                }
                $invoiceDiscountAmount = round($invoiceDiscountAmount, 2);
            }

            /* ============================================================
            STEP 3 — توزيع خصم الفاتورة على البنود
            ============================================================
            
            التوزيع يعتمد على "الوزن النسبي" لكل بند.
            
            الوزن = line_total − line_discount
                    (لأن خصومات البنود لا يُوزَّع عليها خصم الفاتورة مجدداً)
            
            آخر بند يستوعب الفرق التقريبي لضمان التطابق الدقيق.
            ============================================================ */
            $itemsCount = count($processedItems);
            $totalWeight = 0;

            foreach ($processedItems as $it) {
                $totalWeight += ($it['line_total'] - $it['line_discount']);
            }

            $allocatedInvoiceShare = 0;

            foreach ($processedItems as $index => &$item) {
                $weight = $item['line_total'] - $item['line_discount'];

                if ($totalWeight > 0 && $invoiceDiscountAmount > 0) {
                    if ($index === $itemsCount - 1) {
                        // آخر بند: يستوعب الباقي لضمان sum == invoiceDiscountAmount
                        $share = round($invoiceDiscountAmount - $allocatedInvoiceShare, 2);
                    } else {
                        $share = round(($weight / $totalWeight) * $invoiceDiscountAmount, 2);
                        $allocatedInvoiceShare += $share;
                    }
                } else {
                    $share = 0;
                }

                $item['invoice_share'] = $share;

                // ✅ المبلغ الفعلي الذي دفعه العميل لهذا البند
                $item['effective_total'] = round(
                    $item['line_total'] - $item['line_discount'] - $share,
                    2
                );

                // ✅ الربح الفعلي = ما دفعه فعلاً − تكلفة الشراء
                $lineCost = $item['quantity'] * $item['cost_price'];
                $item['profit'] = round($item['effective_total'] - $lineCost, 2);
            }
            unset($item);

            /* ============================================================
            STEP 4 — حساب الإجماليات النهائية
            ============================================================ */
            $grandTotal    = collect($processedItems)->sum('effective_total');
            $totalProfit   = collect($processedItems)->sum('profit');
            $lineDiscountTotal = collect($processedItems)->sum('line_discount');

            $grandTotal  = round($grandTotal, 2);
            $totalProfit = round($totalProfit, 2);

            // ✅ تحديد نطاق الخصم
            $hasInvoiceDiscount = $invoiceDiscountAmount > 0;
            $hasLineDiscount    = $lineDiscountTotal > 0;

            $discountScope = match (true) {
                $hasInvoiceDiscount && $hasLineDiscount => 'mixed',
                $hasInvoiceDiscount                     => 'invoice',
                $hasLineDiscount                        => 'items',
                default                                 => 'none',
            };

            /* ============================================================
            STEP 5 — حفظ الفاتورة
            ============================================================ */
            $bank = $request->input('bank_transfer');
            $isBank = $request->payment_method === 'bank' && !empty($bank);

            $sale = Sale::create([
                'branch_id'           => $shift->branch_id,
                'user_id'             => $user->id,
                'shift_id'            => $shift->id,
                'total_amount'        => $grandTotal,
                'profit_amount'       => $totalProfit,

                // خصم الفاتورة
                'discount_type'       => $discountType,
                'discount_value'      => $discountValue,
                'discount_amount'     => $invoiceDiscountAmount,
                'discount_reason'     => $discountReason,
                'discount_by'         => $invoiceDiscountAmount > 0 ? $user->id : null,

                // ✅ جديد
                'line_discount_total' => $lineDiscountTotal,
                'discount_scope'      => $discountScope,

                'payment_method'      => $request->input('payment_method', 'cash'),
                'bank_name'           => $isBank ? ($bank['bank_name'] ?? null) : null,
                'bank_reference'      => $isBank ? ($bank['reference_number'] ?? null) : null,
                'bank_transfer_date'  => $isBank ? ($bank['transfer_date'] ?? null) : null,
                'bank_notes'          => $isBank ? ($bank['notes'] ?? null) : null,

                'created_at' => $request->filled('created_at')
                    ? \Carbon\Carbon::parse($request->created_at)
                    : now(),
            ]);

            /* ============================================================
            STEP 6 — حفظ البنود + خصم المخزون
            ============================================================ */
            foreach ($processedItems as $item) {
                app(\App\Services\InventoryService::class)
                    ->decreaseBatch($item['batch'], $item['quantity_base']);

                SaleItem::create([
                    'sale_id'             => $sale->id,
                    'medicine_batch_id'   => $item['batch']->id,
                    'medicine_unit_id'    => $item['medicine_unit_id'],
                    'quantity'            => $item['quantity'],
                    'unit'                => $item['unit'],
                    'quantity_base'       => $item['quantity_base'],
                    'price'               => $item['price'],
                    'profit'              => $item['profit'],

                    // ✅ جديد
                    'line_discount'       => $item['line_discount'],
                    'invoice_share'       => $item['invoice_share'],
                    'effective_total'     => $item['effective_total'],
                ]);
            }

            /* ============================================================
            STEP 7 — تحديث الوردية
            ============================================================ */
            if ($shift) {
                if ($request->payment_method === 'cash') {
                    $shift->cash_sales += $grandTotal;
                    $shift->expected_cash += $grandTotal;
                } elseif (in_array($request->payment_method, ['bank', 'card'])) {
                    $shift->card_sales += $grandTotal;
                }

                $shift->sales_count += 1;
                $shift->save();
            }

            /* ============================================================
            STEP 8 — Audit Log
            ============================================================ */
            if ($discountScope !== 'none') {
                \App\Services\AuditLogger::log(
                    'sale_discount_applied',
                    $sale,
                    "تطبيق خصومات على فاتورة #{$sale->id} — " .
                    "النطاق: {$discountScope} — " .
                    "خصم البنود: {$lineDiscountTotal} — " .
                    "خصم الفاتورة: {$invoiceDiscountAmount}" .
                    ($discountReason ? " — السبب: {$discountReason}" : ''),
                    [],
                    [
                        'discount_scope'      => $discountScope,
                        'line_discount_total' => $lineDiscountTotal,
                        'invoice_discount'    => $invoiceDiscountAmount,
                        'grand_total'         => $grandTotal,
                    ],
                    'info',
                    [
                        'branch_id' => $shift->branch_id,
                        'shift_id'  => $shift->id,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Sale completed successfully.',
                'sale'    => $sale->load('items'),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('POS Sale Store Error', [
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'user_id'   => $user->id,
                'shift_id'  => $request->input('shift_id'),
                'branch_id' => $request->input('branch_id'),
            ]);

            return response()->json([
                'message' => 'Failed to process sale.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Return Medicine
    |--------------------------------------------------------------------------
    */

    public function returnMedicine(Request $request, $sale_id)
    {
        return DB::transaction(function () use ($request, $sale_id) {
            $sale = Sale::findOrFail($sale_id);

            foreach ($request->items as $item) {
                // 1. استعادة كمية الدفعة
                $batch = MedicineBatch::findOrFail($item['medicine_batch_id']);

                // ✅ استخدام $item بدلاً من $itemData
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new \InvalidArgumentException('كمية الإرجاع غير صالحة');
                }

                $batch->increment('remaining_quantity', $quantity);

                // 2. استعادة المخزون الإجمالي
                $inventory = \App\Models\Inventory::where('medicine_id', $batch->medicine_id)
                    ->where('branch_id', $batch->branch_id)
                    ->first();

                if ($inventory) {
                    $inventory->increment('quantity', $quantity);
                }

                // 3. تسجيل الحركة
                InventoryLog::create([
                    'medicine_batch_id' => $batch->id,
                    'type'              => 'return',
                    'quantity_changed'  => $quantity,
                    'notes'             => 'Returned from Sale ID: ' . $sale_id,
                ]);
            }

            return response()->json([
                'message' => 'تمت عملية الإرجاع بنجاح',
            ], 200);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Medicines
    |--------------------------------------------------------------------------
    */

    public function medicines(
        Request $request
    ) {

        $user =
            $request->user();


        if (!$user) {

            return response()->json([

                'message' =>
                    'Unauthenticated.'

            ], 401);

        }


        /*
        |--------------------------------------------------------------------------
        | Authenticated user's branch only
        |--------------------------------------------------------------------------
        */

        $branchId =
            $user->branch_id;


        return response()->json(

            $this->saleService
                ->loadMedicines(
                    $branchId
                )

        );

    }
    /*
|--------------------------------------------------------------------------
| Recent Sales — Current Open Shift Only (POS Interface)
|--------------------------------------------------------------------------
|
| تُستخدم من واجهة البيع (pos.js) لجلب مبيعات الوردية المفتوحة فقط.
|
| ⚠️ لا علاقة لها بـ ReportController::getRecentSales
|    (المستخدمة في لوحة التحكم لجلب كل المبيعات)
|
*/

public function getRecentSalesForCurrentShift(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);
    }

    $branchId = $user->branch_id;

    /*
    |--------------------------------------------------------------------------
    | Resolve the current OPEN shift for this user/branch
    |--------------------------------------------------------------------------
    */
    $shift = Shift::where('user_id', $user->id)
        ->where('branch_id', $branchId)
        ->whereNull('closed_at')
        ->where('status', 'open')
        ->latest('id')
        ->first();

    // لا توجد وردية مفتوحة → لا مبيعات
    if (!$shift) {
        return response()->json([
            'shift'  => null,
            'recent' => [],
            'data'   => [],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch sales belonging ONLY to this open shift
    |--------------------------------------------------------------------------
    */
    $sales = Sale::with(['items.batch.medicine', 'items.unit'])
        ->where('shift_id', $shift->id)
        ->where('branch_id', $branchId)
        ->orderBy('id', 'desc')
        ->limit(50)
        ->get()
        ->map(function ($sale) {
            return [
                'id'             => $sale->id,
                'total_amount'   => $sale->total_amount,
                'profit_amount'  => $sale->profit_amount,
                'payment_method' => $sale->payment_method,
                'created_at'     => $sale->created_at,
                'is_refunded'    => (bool) $sale->is_refunded,
                'items'          => $sale->items,
            ];
        });

    return response()->json([
        'shift' => [
            'id'          => $shift->id,
            'opened_at'   => $shift->opened_at,
            'cash_sales'  => $shift->cash_sales,
            'card_sales'  => $shift->card_sales,
            'sales_count' => $shift->sales_count,
        ],
        'recent' => $sales,
        'data'   => $sales,   // دعم كلا الصيغتين
    ]);
}
}
