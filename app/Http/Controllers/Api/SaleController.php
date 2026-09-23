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

    public function store(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | Resolve authenticated user
        |--------------------------------------------------------------------------
        */

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
        | Resolve Branch
        |--------------------------------------------------------------------------
        */

        $branchId =
            $request->input(
                'branch_id',
                $user->branch_id
            );


        /*
        |--------------------------------------------------------------------------
        | Resolve Shift
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | When Offline sales are synchronized, shift_id is the
        | original server shift ID. It MUST be respected.
        |
        */

        $requestedShiftId =
            $request->input(
                'shift_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Case 1:
        | Client supplied shift_id
        |--------------------------------------------------------------------------
        |
        | The shift may be OPEN or CLOSED.
        | We only verify ownership and branch.
        |
        */

        if (
            $requestedShiftId !== null
            &&
            $requestedShiftId !== ''
        ) {

            $shift =
                Shift::where(
                    'id',
                    $requestedShiftId
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'branch_id',
                    $branchId
                )
                ->first();


            if (!$shift) {

                return response()->json([

                    'message' =>
                        'The selected shift does not belong to the authenticated user or branch.'

                ], 422);

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Case 2:
        | No shift_id supplied
        |--------------------------------------------------------------------------
        |
        | Normal ONLINE sale:
        | use the current OPEN shift.
        |
        */

        else {

            $shift =
                Shift::where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'branch_id',
                    $branchId
                )
                ->whereNull(
                    'closed_at'
                )
                ->where(
                    'status',
                    'open'
                )
                ->latest(
                    'id'
                )
                ->first();


            if (!$shift) {

                return response()->json([

                    'message' =>
                        'No open shift is available.'

                ], 422);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Normalize request
        |--------------------------------------------------------------------------
        */

        $request->merge([

            'shift_id' =>
                $shift->id,

            'branch_id' =>
                $shift->branch_id

        ]);


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'shift_id' =>
                'required|exists:shifts,id',

            'branch_id' =>
                'required|exists:branches,id',

            'payment_method' =>
                'required|string',

            'bank_transfer' =>
                'nullable',

            'items' =>
                'required|array|min:1',

            'items.*.medicine_batch_id' =>
                'required|exists:medicine_batches,id',

            'items.*.medicine_unit_id' => [

                'required',

                Rule::exists(
                    'medicine_units',
                    'unit_id'
                )

            ],

            'items.*.quantity' =>
                'required|numeric|min:0.01',

            'items.*.unit' =>
                'sometimes|string',

            'items.*.quantity_base' =>
                'sometimes|numeric',
            'created_at' => 'nullable|date', // ✅ جديد

        ]);


        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | Totals
            |--------------------------------------------------------------------------
            */

            $subtotal =
                0;

            $totalDiscount =
                $request->input(
                    'discount',
                    0
                );

            $processedItems =
                [];


            /*
            |--------------------------------------------------------------------------
            | Process Items
            |--------------------------------------------------------------------------
            */

            foreach (
                $request->items
                as $itemData
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Batch
                |--------------------------------------------------------------------------
                */

                $batch =
                    MedicineBatch::with(
                        'prices'
                    )
                    ->lockForUpdate()
                    ->findOrFail(
                        $itemData[
                            'medicine_batch_id'
                        ]
                    );


                /*
                |--------------------------------------------------------------------------
                | Validate Medicine Unit
                |--------------------------------------------------------------------------
                */

                $medicineUnit =
                    MedicineUnit::where(

                        'unit_id',

                        $itemData[
                            'medicine_unit_id'
                        ]

                    )
                    ->where(

                        'medicine_id',

                        $batch->medicine_id

                    )
                    ->firstOrFail();


                /*
                |--------------------------------------------------------------------------
                | Conversion Factor
                |--------------------------------------------------------------------------
                */

                $conversionFactor =
                    $medicineUnit->factor
                    ?? 1;


                $quantityBase =
                    $itemData['quantity']
                    *
                    $conversionFactor;


                /*
                |--------------------------------------------------------------------------
                | Stock Column
                |--------------------------------------------------------------------------
                */

                $columnToDecrement =

                    Schema::hasColumn(
                        'medicine_batches',
                        'remaining_quantity'
                    )

                        ? 'remaining_quantity'

                        : 'current_stock';


                $availableStock =
                    $batch->{$columnToDecrement}
                    ?? 0;


                /*
                |--------------------------------------------------------------------------
                | Stock Validation
                |--------------------------------------------------------------------------
                */

                if (
                    $availableStock <= 0
                    ||
                    $availableStock
                    <
                    $quantityBase
                ) {

                    DB::rollBack();


                    return response()->json([

                        'message' =>
                            'Selected batch is out of stock or has insufficient quantity.'

                    ], 422);

                }


                /*
                |--------------------------------------------------------------------------
                | Frontend Price
                |--------------------------------------------------------------------------
                */

                $frontendPrice =

                    $itemData['unit_price']
                    ??
                    $itemData['price']
                    ??
                    null;


                /*
                |--------------------------------------------------------------------------
                | Exact Unit Price Configuration
                |--------------------------------------------------------------------------
                */

                $priceConfig =
                    $batch
                        ->prices
                        ->where(
                            'unit_id',
                            $medicineUnit->unit_id
                        )
                        ->first();


                /*
                |--------------------------------------------------------------------------
                | Fallback Price
                |--------------------------------------------------------------------------
                */

                if (!$priceConfig) {

                    $priceConfig =
                        $batch
                            ->prices
                            ->first();


                    if (!$priceConfig) {

                        DB::rollBack();


                        return response()->json([

                            'message' =>
                                "Data Integrity Error: No price configuration found for batch #{$batch->batch_number}."

                        ], 422);

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Price / Cost
                |--------------------------------------------------------------------------
                */

                $unitPrice =
                    $frontendPrice
                    ??
                    $priceConfig->sell_price;


                $costPrice =
                    $priceConfig->buy_price
                    ??
                    $batch->buy_price
                    ??
                    0;


                /*
                |--------------------------------------------------------------------------
                | Line Total
                |--------------------------------------------------------------------------
                */

                $lineTotal =
                    $itemData['quantity']
                    *
                    $unitPrice;


                $subtotal +=
                    $lineTotal;


                /*
                |--------------------------------------------------------------------------
                | Profit
                |--------------------------------------------------------------------------
                */

                $lineCost =
                    $itemData['quantity']
                    *
                    $costPrice;


                $lineProfit =
                    $lineTotal
                    -
                    $lineCost;


                /*
                |--------------------------------------------------------------------------
                | Unit Type
                |--------------------------------------------------------------------------
                */

                $unitSymbol =
                    strtolower(

                        $medicineUnit
                            ->unit
                            ->symbol
                        ??
                        ''

                    );


                $unitType =
                    match (true) {

                        str_contains(
                            $unitSymbol,
                            'box'
                        )
                            =>
                            'box',

                        str_contains(
                            $unitSymbol,
                            'str'
                        )
                            =>
                            'strip',

                        default
                            =>
                            'piece'

                    };


                /*
                |--------------------------------------------------------------------------
                | Store processed item
                |--------------------------------------------------------------------------
                */

                $processedItems[] = [

                    'batch' =>
                        $batch,

                    'column_to_decrement' =>
                        $columnToDecrement,

                    'medicine_unit_id' =>
                        $itemData[
                            'medicine_unit_id'
                        ],

                    'quantity' =>
                        $itemData[
                            'quantity'
                        ],

                    'quantity_base' =>
                        $quantityBase,

                    'price' =>
                        $unitPrice,

                    'profit' =>
                        $lineProfit,

                    'unit' =>
                        $unitType

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Grand Total / Profit
            |--------------------------------------------------------------------------
            */

            $grandTotal =
                $subtotal
                -
                $totalDiscount;


            $totalProfit =
                collect(
                    $processedItems
                )
                ->sum(
                    'profit'
                );


            /*
            |--------------------------------------------------------------------------
            | Bank Transfer
            |--------------------------------------------------------------------------
            */

            $bank =
                $request->input(
                    'bank_transfer'
                );


            $isBank =
                $request->payment_method === 'bank'
                &&
                !empty(
                    $bank
                );


            /*
            |--------------------------------------------------------------------------
            | Create Sale
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | The resolved original shift ID is persisted here.
            |
            */

            $sale =
                Sale::create([

                    'branch_id' =>
                        $shift->branch_id,

                    'user_id' =>
                        $user->id,

                    'shift_id' =>
                        $shift->id,

                    'total_amount' =>
                        $grandTotal,

                    'profit_amount' =>
                        $totalProfit,

                    'payment_method' =>
                        $request->input(
                            'payment_method',
                            'cash'
                        ),

                    'bank_name' =>
                        $isBank
                            ? (
                                $bank[
                                    'bank_name'
                                ]
                                ??
                                null
                            )
                            : null,

                    'bank_reference' =>
                        $isBank
                            ? (
                                $bank[
                                    'reference_number'
                                ]
                                ??
                                null
                            )
                            : null,

                    'bank_transfer_date' =>
                        $isBank
                            ? (
                                $bank[
                                    'transfer_date'
                                ]
                                ??
                                null
                            )
                            : null,

                    'bank_notes' =>
                        $isBank
                            ? (
                                $bank[
                                    'notes'
                                ]
                                ??
                                null
                            )
                            : null,
                    'created_at' => $request->filled('created_at')
                        ? \Carbon\Carbon::parse($request->created_at)
                        : now(),

                ]);


            /*
            |--------------------------------------------------------------------------
            | Sale Items + Inventory
            |--------------------------------------------------------------------------
            */

            foreach (
                $processedItems
                as $item
            ) {

                /*
                |--------------------------------------------------------------------------
                | Decrement Batch
                |--------------------------------------------------------------------------
                */

                // ✅ الجديد:
                app(\App\Services\InventoryService::class)
                    ->decreaseBatch($item['batch'], $item['quantity_base']);



                /*
                |--------------------------------------------------------------------------
                | Create Sale Item
                |--------------------------------------------------------------------------
                */

                SaleItem::create([

                    'sale_id' =>
                        $sale->id,

                    'medicine_batch_id' =>
                        $item[
                            'batch'
                        ]->id,

                    'medicine_unit_id' =>
                        $item[
                            'medicine_unit_id'
                        ],

                    'quantity' =>
                        $item[
                            'quantity'
                        ],

                    'unit' =>
                        $item[
                            'unit'
                        ],

                    'quantity_base' =>
                        $item[
                            'quantity_base'
                        ],

                    'price' =>
                        $item[
                            'price'
                        ],

                    'profit' =>
                        $item[
                            'profit'
                        ]

                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Update Original Shift
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | This is the exact shift associated with the sale,
            | even if that shift has already been closed.
            |
            */

            if ($shift) {

                $totalAmount =
                    $sale->total_amount;


                if (
                    $request->payment_method ===
                    'cash'
                ) {

                    $shift->cash_sales +=
                        $totalAmount;


                    $shift->expected_cash +=
                        $totalAmount;

                }

                elseif (
                    $request->payment_method ===
                    'bank'
                    ||
                    $request->payment_method ===
                    'card'
                ) {

                    $shift->card_sales +=
                        $totalAmount;

                }


                $shift->sales_count += 1;


                $shift->save();

            }


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'message' =>
                    'Sale completed successfully.',

                'sale' =>
                    $sale->load(
                        'items'
                    )

            ], 201);

        }

        catch (\Throwable $e) {

            DB::rollBack();


            Log::error(

                'POS Sale Store Error',

                [

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'user_id' =>
                        $user->id,

                    'shift_id' =>
                        $request->input(
                            'shift_id'
                        ),

                    'branch_id' =>
                        $request->input(
                            'branch_id'
                        )

                ]

            );


            return response()->json([

                'message' =>
                    'Failed to process sale.',

                'error' =>
                    $e->getMessage()

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
