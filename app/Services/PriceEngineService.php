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

/**
 * معاينة إعادة الحساب الشاملة — بدون حفظ
 * تُرجع تفاصيل كل سعر سيُحدَّث، مجمّعاً حسب القاعدة المستخدمة
 *
 * @param array $filters فلاتر: only_imported, only_with_stock, skip_locked, branch_id
 * @return array مصفوفة من المجموعات (كل مجموعة = قاعدة)
 */
public function previewBulkRecalculation(array $filters = []): array
{
    $query = $this->buildBulkQuery($filters);

    $groups = [];

    $query->chunkById(200, function ($batches) use (&$groups) {

        foreach ($batches as $batch) {

            $batch->loadMissing([
                'medicine.pricingRule',
                'medicine.units.unit',
                'purchaseUnit',
            ]);

            $medicine = $batch->medicine;
            if (!$medicine) continue;

            $purchaseFactor = max(1, (float) ($batch->purchaseUnit?->factor ?? 1));
            $baseBuyPrice = (float) $batch->buy_price / $purchaseFactor;

            $existingPrices = \App\Models\MedicinePrice::where('batch_id', $batch->id)
                ->get()
                ->keyBy('unit_id');

            foreach ($medicine->units as $unit) {
                $unitFactor = max(1, (float) ($unit->factor ?? 1));
                $unitBuyPrice = $baseBuyPrice * $unitFactor;

                // احسب السعر الجديد (حتى لو مقفل، نحتاج القاعدة لعرضها)
                $result = $this->pricingService->calculateSellPrice(
                    $unitBuyPrice,
                    $medicine,
                    $batch
                );

                $existing = $existingPrices->get($unit->unit_id);
                $isManualLock = $existing && $existing->price_mode === 'manual';

                // ⚠️ لا نتجاهل المقفل — نعرضه بعلامة is_locked
                // لكن في apply، لن نلمسه

                $oldSellPrice = $existing?->sell_price ?? 0;

                // السعر الجديد: إذا كان مقفل، اترك السعر الحالي
                $newSellPrice = $isManualLock
                    ? (float) $existing->sell_price
                    : (float) $result['sell_price'];

                $ruleId   = $result['rule_id'] ?? 0;
                $ruleName = $result['rule_name'] ?? 'قاعدة عامة';

                if (!isset($groups[$ruleId])) {
                    $groups[$ruleId] = [
                        'rule_id'       => $ruleId,
                        'rule_name'     => $ruleName,
                        'rule_type'     => $this->getRuleType($ruleId),
                        'rule_value'    => $this->getRuleValue($ruleId),
                        'rule_settings' => $this->getRuleSettings($ruleId),
                        'items'         => [],
                    ];
                }

                $groups[$ruleId]['items'][] = [
                    'batch_id'         => $batch->id,
                    'price_id'         => $existing?->id,
                    'medicine_id'      => $medicine->id,
                    'medicine_name'    => $medicine->name,
                    'medicine_barcode' => $medicine->barcode,
                    'unit_id'          => $unit->unit_id,
                    'unit_name'        => $unit->unit?->name ?? 'وحدة',
                    'unit_symbol'      => $unit->unit?->symbol ?? '',
                    'factor'           => (float) $unitFactor,
                    'buy_price'        => round($unitBuyPrice, 2),
                    'old_sell_price'   => round((float) $oldSellPrice, 2),
                    'new_sell_price'   => round($newSellPrice, 2), // ← للسعر المقفل = السعر الحالي
                    'is_locked'        => $isManualLock,
                    'lock_reason'      => $existing?->lock_reason,
                    'locked_at'        => $existing?->locked_at?->toIso8601String(),
                ];
            }
        }
    });

    ksort($groups);

    foreach ($groups as &$group) {
        usort($group['items'], function ($a, $b) {
            return strcmp($a['medicine_name'], $b['medicine_name'])
                ?: strcmp($a['unit_name'], $b['unit_name']);
        });
    }

    $totalItems = array_sum(array_map(fn($g) => count($g['items']), $groups));

    $lockedItems = 0;
    foreach ($groups as $g) {
        foreach ($g['items'] as $it) {
            if ($it['is_locked']) $lockedItems++;
        }
    }

    $totalBatches = collect($groups)
        ->pluck('items')
        ->flatten(1)
        ->pluck('batch_id')
        ->unique()
        ->count();

    return [
        'groups'         => array_values($groups),
        'total_items'    => $totalItems,
        'total_batches'  => $totalBatches,
        'locked_items'   => $lockedItems,
        'unlockable_items' => $totalItems - $lockedItems,
    ];
}

/**
 * بناء الاستعلام مع الفلاتر (مستخدم من preview و apply)
 */
private function buildBulkQuery(array $filters): \Illuminate\Database\Eloquent\Builder
{
    $query = \App\Models\MedicineBatch::query()
        ->with(['medicine.pricingRule', 'medicine.units.unit', 'purchaseUnit'])
        ->whereHas('medicine')
        ->whereNotNull('buy_price');

    if ($filters['only_imported'] ?? false) {
        $query->whereHas('medicine', function ($q) {
            $q->where('pricing_method', 'imported');
        });
    }

    if ($filters['only_with_stock'] ?? false) {
        $query->where('remaining_quantity', '>', 0);
    }

    if (!empty($filters['branch_id'])) {
        $query->where('branch_id', $filters['branch_id']);
    }

    // ❌ حذفنا: whereHas('prices', price_mode=auto) عند skip_locked
    // لأننا الآن نعرض المقفل أيضاً

    return $query;
}

/**
 * جلب نوع القاعدة (للعرض)
 */
private function getRuleType(int $ruleId): ?string
{
    if ($ruleId <= 0) return null;
    return \App\Models\PriceEngineRule::where('id', $ruleId)->value('type');
}

/**
 * جلب قيمة القاعدة (للعرض)
 */
private function getRuleValue(int $ruleId): ?float
{
    if ($ruleId <= 0) return null;
    $value = \App\Models\PriceEngineRule::where('id', $ruleId)->value('value');
    return $value !== null ? (float) $value : null;
}

/**
 * جلب إعدادات القاعدة (للعرض)
 */
private function getRuleSettings(int $ruleId): ?array
{
    if ($ruleId <= 0) return null;
    $settings = \App\Models\PriceEngineRule::where('id', $ruleId)->value('settings');
    if (!$settings) return null;
    return is_string($settings) ? json_decode($settings, true) : $settings;
}

/**
 * تطبيق إعادة الحساب الشاملة مع تجاوزات يدوية
 *
 * @param array $filters الفلاتر
 * @param array $overrides [['price_id' => int, 'new_sell_price' => float], ...]
 * @return array إحصائيات
 */
public function applyBulkRecalculationWithOverrides(array $filters, array $overrides = [], array $unlockPriceIds = []): array
{
    $stats = [
        'batches_processed' => 0,
        'batches_skipped'   => 0,
        'prices_updated'    => 0,
        'prices_skipped'    => 0,
        'overrides_applied' => 0,
        'unlocked'          => 0,
        'failed'            => 0,
    ];

    // خزّن الـ overrides في map للوصول السريع
    $overridesMap = [];
    foreach ($overrides as $override) {
        if (isset($override['price_id']) && isset($override['new_sell_price'])) {
            $overridesMap[(int) $override['price_id']] = (float) $override['new_sell_price'];
        }
    }

    // خزّن الـ unlock في Set
    $unlockSet = array_flip(array_map('intval', $unlockPriceIds));

    // ⚠️ أولاً: نفّذ unlock قبل الحساب
    if (!empty($unlockSet)) {
        $toUnlock = \App\Models\MedicinePrice::whereIn('id', array_keys($unlockSet))->get();
        foreach ($toUnlock as $price) {
            $price->update([
                'price_mode'  => 'auto',
                'locked_by'   => null,
                'locked_at'   => null,
                'lock_reason' => null,
            ]);
            $stats['unlocked']++;
        }
    }

    $query = $this->buildBulkQuery($filters);

    $query->chunkById(100, function ($batches) use (&$stats, $overridesMap, $filters) {

        foreach ($batches as $batch) {

            try {
                $lockedCount = \App\Models\MedicinePrice::where('batch_id', $batch->id)
                    ->where('price_mode', 'manual')
                    ->count();

                $autoCount = \App\Models\MedicinePrice::where('batch_id', $batch->id)
                    ->where('price_mode', 'auto')
                    ->count();

                // ⚠️ إذا skip_locked مفعّل وكل الأسعار مقفلة → تجاوز
                if (($filters['skip_locked'] ?? false) && $autoCount === 0 && $lockedCount > 0) {
                    $stats['batches_skipped']++;
                    $stats['prices_skipped'] += $lockedCount;
                    continue;
                }

                // شغّل المحرك
                $updated = $this->generate($batch);

                // طبّق الـ overrides
                $batchOverrides = \App\Models\MedicinePrice::where('batch_id', $batch->id)
                    ->where('price_mode', 'auto')
                    ->get()
                    ->filter(fn($p) => isset($overridesMap[$p->id]));

                foreach ($batchOverrides as $price) {
                    $newPrice = $overridesMap[$price->id];

                    if (abs($newPrice - (float) $price->sell_price) < 0.01) {
                        continue;
                    }

                    $profitAmount = $newPrice - (float) $price->buy_price;
                    $profitPercent = (float) $price->buy_price > 0
                        ? ($profitAmount / (float) $price->buy_price) * 100
                        : 0;

                    $price->update([
                        'sell_price'     => $newPrice,
                        'profit_amount'  => round($profitAmount, 2),
                        'profit_percent' => round($profitPercent, 2),
                        'price_mode'     => 'manual',
                        'locked_by'      => auth()->id(),
                        'locked_at'      => now(),
                        'lock_reason'    => 'تعديل يدوي بعد إعادة الحساب الشاملة',
                    ]);

                    $stats['overrides_applied']++;
                }

                $stats['batches_processed']++;
                $stats['prices_updated']  += $updated;
                $stats['prices_skipped']  += $lockedCount;

            } catch (\Throwable $e) {
                $stats['failed']++;
                report($e);
            }
        }
    });

    return $stats;
}
}