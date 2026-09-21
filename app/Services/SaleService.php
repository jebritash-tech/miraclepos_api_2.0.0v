<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicinePrice;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SaleService
{
   public function loadMedicines(int $branchId)
    {
        return Medicine::query()
            ->with([
                'units.unit', 
                'inventory' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                },
                // Scoped strictly to the user's branch batches
                'batches' => function ($q) use ($branchId) {
                    $q->where('remaining_quantity', '>', 0)
                      ->where('branch_id', $branchId) // <--- Fixed: restricted to user branch
                      ->orderBy('expiry_date', 'asc'); // FIFO batch priority
                },
                'batches.prices.unit',
            ])
            ->whereHas('batches', function ($q) use ($branchId) {
                $q->where('remaining_quantity', '>', 0)
                  ->where('branch_id', $branchId); // <--- Fixed: restricted to user branch
            })
            ->get();
    }
    /*
    |--------------------------------------------------------------------------
    | FEFO
    |--------------------------------------------------------------------------
    */

   public function getBatch(
    int $medicineId,
    int $requiredBaseQuantity,
    int $branchId
    )
    {
        return MedicineBatch::query()
            ->where('medicine_id', $medicineId)
            ->where('branch_id', $branchId) 
            ->where('remaining_quantity', '>=', $requiredBaseQuantity)
            ->orderBy('expiry_date')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Price
    |--------------------------------------------------------------------------
    */

    public function getPrice(
        int $batchId,
        int $medicineUnitId
    )
    {
        return MedicinePrice::query()
            ->where('batch_id', $batchId)
            ->where('unit_id', $medicineUnitId)
            ->where('is_active', true)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Create Sale
    |--------------------------------------------------------------------------
    */

    public function create(array $data)
    {
        \Log::info('POS Sale Request Payload:', $data);

        return DB::transaction(function () use ($data) {
            $sale = Sale::create([
                'branch_id'           => $data['branch_id'],
                'user_id'             => $data['user_id'],
                'shift_id'            => $data['shift_id'],
                'total_amount'        => $data['total_amount'],
                'profit_amount'       => 0, // سيُحدَّث لاحقاً
                'payment_method'      => $data['payment_method'],
                'bank_name'           => $data['bank_name'] ?? null,
                'bank_reference'      => $data['bank_reference'] ?? null,
                'bank_transfer_date'  => $data['bank_transfer_date'] ?? null,
                'bank_notes'          => $data['bank_notes'] ?? null,
            ]);

            $totalProfit = 0;

            foreach ($data['items'] as $itemData) {
                $batch = MedicineBatch::with('prices')->findOrFail($itemData['medicine_batch_id']);

                // 1. احصل على factor الوحدة
                $medicineUnit = \App\Models\MedicineUnit::where('medicine_id', $batch->medicine_id)
                    ->where('unit_id', $itemData['medicine_unit_id'])
                    ->first();

                $factor = $medicineUnit ? $medicineUnit->factor : 1;
                $quantityBase = $itemData['quantity'] * $factor;

                // 2. تحقق من المخزون
                if ($batch->remaining_quantity < $quantityBase) {
                    throw new \Exception('الكمية غير متوفرة في المخزون');
                }

                // 3. احصل على السعر (من الحقل أو من price record)
                $sellingPrice = $itemData['selling_price'] ?? null;

                if ($sellingPrice === null) {
                    $priceRecord = $batch->prices
                        ->where('unit_id', $itemData['medicine_unit_id'])
                        ->first()
                        ?? $batch->prices->first();

                    $sellingPrice = $priceRecord?->sell_price ?? 0;
                }

                // 4. احسب تكلفة الشراء (لسعر الوحدة)
                $buyPricePerUnit = ($batch->buy_price ?? 0); // buy_price للدفعة كاملة الوحدة الأساسية

                // إذا كانت الوحدة المبيعة ليست الأساسية، اضرب في factor
                $costPrice = $buyPricePerUnit * $factor;

                // 5. احسب الربح
                $lineTotal  = $itemData['quantity'] * $sellingPrice;
                $lineCost   = $itemData['quantity'] * $costPrice;
                $lineProfit = $lineTotal - $lineCost;

                $totalProfit += $lineProfit;

                // 6. خصم المخزون
                app(\App\Services\InventoryService::class)->decreaseBatch($batch, $quantityBase);

                // 7. إنشاء SaleItem — ✅ price بدلاً من selling_price + profit
                SaleItem::create([
                    'sale_id'            => $sale->id,
                    'medicine_batch_id'  => $batch->id,
                    'medicine_unit_id'   => $itemData['medicine_unit_id'],
                    'quantity'           => $itemData['quantity'],
                    'unit'               => $itemData['unit'],
                    'quantity_base'      => $quantityBase,
                    'price'              => $sellingPrice,
                    'profit'             => $lineProfit,
                ]);
            }

            // ✅ تحديث الربح الإجمالي
            $sale->update([
                'profit_amount' => $totalProfit,
            ]);

            return $sale->fresh();
        });
    }
}