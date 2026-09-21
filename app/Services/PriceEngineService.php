<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicinePrice;
use App\Services\PricingService;
use Illuminate\Http\Request;
use App\Models\MedicineUnit;
class PriceEngineService
{
    protected PricingService $pricingService;

    public function __construct(
        PricingService $pricingService
    ) {
        $this->pricingService = $pricingService;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Prices For Batch
    |--------------------------------------------------------------------------
    |
    | A batch is created from a purchase.
    | The medicine's assigned pricing rule is used.
    | Every medicine unit gets its own MedicinePrice.
    |
    */

   public function generate(MedicineBatch $batch): int
    {
        $batch->loadMissing([
            'medicine.pricingRule',
            'medicine.units',
            'purchaseUnit',
        ]);

        $medicine = $batch->medicine;
        if (!$medicine) {
            throw new \RuntimeException('الدواء المرتبط بالدفعة غير موجود.');
        }

        $purchaseFactor = max(1, (float) ($batch->purchaseUnit?->factor ?? 1));
        $baseBuyPrice = (float) $batch->buy_price / $purchaseFactor;
        $updated = 0;

        foreach ($medicine->units as $unit) {
            $unitFactor = max(1, (float) ($unit->factor ?? 1));
            $unitBuyPrice = $baseBuyPrice * $unitFactor;

            // ✅ مرّر الدفعة لحساب التسعير الصحيح
            $result = $this->pricingService->calculateSellPrice(
                $unitBuyPrice,
                $medicine,
                $batch
            );

            // ✅ احترم القفل اليدوي
            $existing = MedicinePrice::where('batch_id', $batch->id)
                ->where('unit_id', $unit->unit_id)
                ->first();

            $isManualLock = $existing && $existing->price_mode === 'manual';

            MedicinePrice::updateOrCreate(
                [
                    'batch_id' => $batch->id,
                    'unit_id'  => $unit->unit_id,
                ],
                [
                    'medicine_id'    => $batch->medicine_id,
                    'buy_price'      => round($unitBuyPrice, 2),
                    'sell_price'     => $isManualLock
                        ? $existing->sell_price  // ← احترم القفل
                        : $result['sell_price'],
                    'profit_amount'  => $isManualLock
                        ? $existing->profit_amount
                        : $result['profit_amount'],
                    'profit_percent' => $isManualLock
                        ? $existing->profit_percent
                        : $result['profit_percent'],
                    'is_active'      => true,
                    // لا تغيّر price_mode — احتفظ بالوضع الحالي
                ]
            );

            $updated++;
        }

        return $updated;
    }
    /*
    |--------------------------------------------------------------------------
    | Simulation
    |--------------------------------------------------------------------------
    */
    public function simulate(Request $request): array
    {
        $validated = $request->validate([

            'medicine_id' => [
                'required',
                'exists:medicines,id'
            ],

            'buy_price' => [
                'required',
                'numeric',
                'min:0'
            ],

        ]);

        $medicine = Medicine::with([
            'pricingRule',
            'units.unit',
        ])->findOrFail(
            $validated['medicine_id']
        );

        return $this->pricingService
            ->calculateSellPrice(

                (float) $validated['buy_price'],

                $medicine

            );
    }

    /*
    |--------------------------------------------------------------------------
    | Simulation By Values
    |--------------------------------------------------------------------------
    |
    | Useful when the controller already has validated values.
    |
    */

    public function simulateValues(
        Medicine $medicine,
        float $buyPrice
    ): array {

        return $this->pricingService
            ->calculateSellPrice(

                $buyPrice,

                $medicine

            );
    }

    /*
    |--------------------------------------------------------------------------
    | Regenerate All Prices
    |--------------------------------------------------------------------------
    */

    // public function regenerateAll(): void
    // {
    //     MedicineBatch::query()

    //         ->with([

    //             'medicine.pricingRule',

    //             'medicine.units',

    //             'purchaseUnit',

    //         ])

    //         ->chunk(

    //             100,

    //             function ($batches) {

    //                 foreach ($batches as $batch) {

    //                     $this->generate(

    //                         $batch

    //                     );

    //                 }

    //             }

    //         );
    // }

    // public function regenerateCurrentPrices(): array
    // {
    //     $updated = 0;
    //     $skipped = 0;
    //     $failed = 0;

    //     MedicineBatch::query()

    //         ->where('remaining_quantity', '>', 0)

    //         ->whereHas(
    //             'medicine'
    //         )

    //         ->with([
    //             'medicine.pricingRule',
    //             'medicine.units',
    //             'purchaseUnit',
    //         ])

    //         ->chunkById(
    //             100,
    //             function ($batches) use (
    //                 &$updated,
    //                 &$skipped,
    //                 &$failed
    //             ) {

    //                 foreach ($batches as $batch) {

    //                     try {

    //                         $updated +=

    //                             $this->generate(
    //                                 $batch
    //                             );

    //                     }

    //                     catch (\Throwable $e) {

    //                         $failed++;

    //                         report($e);

    //                     }
    //                 }
    //             }
    //         );

    //     return [

    //         'updated' => $updated,

    //         'skipped' => $skipped,

    //         'failed' => $failed,

    //     ];
    // }

    /*
|--------------------------------------------------------------------------
| Regenerate Current Prices
|--------------------------------------------------------------------------
|
| Updates prices only for batches that still have stock.
| Every MedicineUnit belonging to the medicine is processed directly.
|
*/

public function regenerateCurrentPrices(): array
{
    $stats = [
        'batches' => 0,
        'units' => 0,
        'prices' => 0,
        'failed' => 0,
    ];

    MedicineBatch::query()

        ->where('remaining_quantity', '>', 0)

        ->with([
            'medicine.pricingRule',
            'purchaseUnit',
        ])

        ->chunkById(
            100,
            function ($batches) use (&$stats) {

                foreach ($batches as $batch) {

                    try {

                        $stats['batches']++;

                        $medicine = $batch->medicine;

                        if (!$medicine) {
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Get ALL Units Directly
                        |--------------------------------------------------------------------------
                        */

                        $units = MedicineUnit::query()

                            ->where(
                                'medicine_id',
                                $batch->medicine_id
                            )

                            ->orderBy('sort_order')

                            ->get();

                        $stats['units'] +=
                            $units->count();

                        /*
                        |--------------------------------------------------------------------------
                        | Purchase Unit Factor
                        |--------------------------------------------------------------------------
                        */

                        $purchaseFactor = max(

                            1,

                            (float) (
                                $batch
                                    ->purchaseUnit
                                    ?->factor ?? 1
                            )

                        );

                        $baseBuyPrice =
                            (float) $batch->buy_price
                            /
                            $purchaseFactor;

                        /*
                        |--------------------------------------------------------------------------
                        | Generate Each Unit
                        |--------------------------------------------------------------------------
                        */

                        foreach ($units as $unit) {

                            $unitFactor = max(

                                1,

                                (float) (
                                    $unit->factor ?? 1
                                )

                            );

                            $unitBuyPrice =
                                $baseBuyPrice *
                                $unitFactor;

                            $result =
                                $this
                                    ->pricingService
                                    ->calculateSellPrice(
                                        $unitBuyPrice,
                                        $medicine
                                    );

                            MedicinePrice::updateOrCreate(

                                [
                                    'batch_id' =>
                                        $batch->id,

                                    'unit_id' =>
                                        $unit->unit_id,
                                ],

                                [
                                    'medicine_id' =>
                                        $batch->medicine_id,

                                    'buy_price' =>
                                        round(
                                            $unitBuyPrice,
                                            2
                                        ),

                                    'sell_price' =>
                                        $result['sell_price'],

                                    'profit_amount' =>
                                        $result['profit_amount'],

                                    'profit_percent' =>
                                        $result['profit_percent'],

                                    'is_active' => true,
                                ]

                            );

                            $stats['prices']++;
                        }

                    } catch (\Throwable $e) {

                        $stats['failed']++;

                        report($e);
                    }
                }
            }
        );

    return $stats;
}



public function regenerateAll(): array
{
    $stats = [
        'batches' => 0,
        'units' => 0,
        'prices' => 0,
        'failed' => 0,
    ];

    MedicineBatch::query()

        ->with([
            'medicine.pricingRule',
            'purchaseUnit',
        ])

        ->chunkById(
            100,
            function ($batches) use (&$stats) {

                foreach ($batches as $batch) {

                    try {

                        $stats['batches']++;

                        $medicine =
                            $batch->medicine;

                        if (!$medicine) {
                            continue;
                        }

                        $units =
                            MedicineUnit::query()
                                ->where(
                                    'medicine_id',
                                    $batch->medicine_id
                                )
                                ->orderBy('sort_order')
                                ->get();

                        $stats['units'] +=
                            $units->count();

                        $purchaseFactor = max(
                            1,
                            (float) (
                                $batch
                                    ->purchaseUnit
                                    ?->factor ?? 1
                            )
                        );

                        $baseBuyPrice =
                            (float) $batch->buy_price
                            /
                            $purchaseFactor;

                        foreach ($units as $unit) {

                            $unitFactor = max(
                                1,
                                (float) (
                                    $unit->factor ?? 1
                                )
                            );

                            $unitBuyPrice =
                                $baseBuyPrice *
                                $unitFactor;

                            $result =
                                $this
                                    ->pricingService
                                    ->calculateSellPrice(
                                        $unitBuyPrice,
                                        $medicine
                                    );

                            MedicinePrice::updateOrCreate(

                                [
                                    'batch_id' =>
                                        $batch->id,

                                    'unit_id' =>
                                        $unit->unit_id,
                                ],

                                [
                                    'medicine_id' =>
                                        $batch->medicine_id,

                                    'buy_price' =>
                                        round(
                                            $unitBuyPrice,
                                            2
                                        ),

                                    'sell_price' =>
                                        $result['sell_price'],

                                    'profit_amount' =>
                                        $result['profit_amount'],

                                    'profit_percent' =>
                                        $result['profit_percent'],

                                    'is_active' => true,
                                ]

                            );

                            $stats['prices']++;
                        }

                    } catch (\Throwable $e) {

                        $stats['failed']++;

                        report($e);
                    }
                }
            }
        );

    return $stats;
}
    }