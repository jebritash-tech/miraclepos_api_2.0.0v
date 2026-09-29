<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicinePrice;
use App\Models\PriceEngineRule;
use App\Services\AuditLogger;
use App\Services\PriceEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchPricingController extends Controller
{
    /* ============================================================
       GET /api/medicines/{medicine}/available-batches
       يُرجع كل الدفعات المتاحة مع أسعارها (لـ POS + Admin)
       ============================================================ */
    /* ============================================================
    GET /api/medicines/{medicine}/available-batches
    ============================================================ */
    public function availableBatches(Medicine $medicine, Request $request)
    {
        $branchId = $request->input('branch_id');

        $medicine->load(['units.unit']);
        $baseUnit = $medicine->units->firstWhere('is_base', true)
                ?? $medicine->units->first();
        $baseUnitId = $baseUnit?->unit_id;
        $baseUnitFactor = max(1, (float) ($baseUnit?->factor ?? 1));

        $batches = MedicineBatch::query()
            ->where('medicine_id', $medicine->id)
            ->where('remaining_quantity', '>', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['prices'])
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->get()
            ->map(function ($batch) use ($baseUnitId, $baseUnitFactor, $medicine) {  // ✅ أضف $medicine
                $price = $batch->prices->firstWhere('unit_id', $baseUnitId)
                    ?? $batch->prices->sortByDesc('buy_price')->first();

                $lockedPrices = $batch->prices->where('price_mode', 'manual');
                $anyLocked = $lockedPrices->isNotEmpty();
                $lockedPrice = $lockedPrices->first();

                return [
                    'id'                    => $batch->id,
                    'batch_number'          => $batch->batch_number,
                    'expiry_date'           => $batch->expiry_date,
                    'remaining_quantity'    => (float) $batch->remaining_quantity,
                    'remaining_packs'       => (int) floor($batch->remaining_quantity / $baseUnitFactor),
                    'buy_price'             => (float) $batch->buy_price,
                    'sell_price'            => $price ? (float) $price->sell_price : 0,
                    'price_id'              => $price?->id,
                    'unit_name'             => $baseUnit?->unit?->name ?? 'وحدة',
                    'price_mode'            => $anyLocked ? 'manual' : ($price?->price_mode ?? 'auto'),
                    'is_locked'             => $anyLocked,
                    'lock_reason'           => $lockedPrice?->lock_reason,
                    'locked_at'             => $lockedPrice?->locked_at?->toIso8601String(),
                    'pricing_rule_id'       => $batch->pricing_rule_id,
                    'custom_markup_percent' => $batch->custom_markup_percent,
                    'expires_in_days'       => $batch->expiry_date
                        ? now()->diffInDays($batch->expiry_date, false)
                        : null,
                    'prices' => $batch->prices->map(function ($p) use ($medicine) {  // ✅ function مع use
                        $unit = $medicine->units->firstWhere('unit_id', $p->unit_id);
                        return [
                            'id'         => $p->id,
                            'unit_id'    => $p->unit_id,
                            'buy_price'  => (float) $p->buy_price,
                            'sell_price' => (float) $p->sell_price,
                            'factor'     => (float) ($unit?->factor ?? 1),
                            'barcode'    => $unit?->barcode,
                            'is_locked'  => $p->price_mode === 'manual',
                            'lock_reason'=> $p->lock_reason,
                        ];
                    })->values()->toArray(),
                ];
            });

        $uniquePrices = $batches->pluck('sell_price')->unique()->values();

        return response()->json([
            'medicine' => ['id' => $medicine->id, 'name' => $medicine->name],
            'batches'  => $batches,
            'has_multiple_prices' => $uniquePrices->count() > 1,
            'price_range' => [
                'min' => $uniquePrices->min(),
                'max' => $uniquePrices->max(),
            ],
        ]);
    }

    /* ============================================================
       POST /api/batches/{batch}/override-pricing
       تخصيص تسعير دفعة معينة
       ============================================================ */
    public function overrideBatchPricing(Request $request, MedicineBatch $batch)
    {
        $data = $request->validate([
            'mode' => 'required|in:rule,markup,reset',

            // عند mode=rule
            'pricing_rule_id' => 'required_if:mode,rule|exists:price_engine_rules,id',

            // عند mode=markup
            'custom_markup_percent' => 'required_if:mode,markup|numeric|min:-100|max:1000',

            'notes' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $old = [
                'pricing_rule_id'       => $batch->pricing_rule_id,
                'custom_markup_percent' => $batch->custom_markup_percent,
            ];

            if ($data['mode'] === 'reset') {
                $batch->pricing_rule_id = null;
                $batch->custom_markup_percent = null;
            } elseif ($data['mode'] === 'rule') {
                $batch->pricing_rule_id = $data['pricing_rule_id'];
                $batch->custom_markup_percent = null;
            } elseif ($data['mode'] === 'markup') {
                $batch->pricing_rule_id = null;
                $batch->custom_markup_percent = $data['custom_markup_percent'];
            }

            $batch->pricing_notes = $data['notes'] ?? null;
            $batch->save();

            // ✅ أعد حساب أسعار هذه الدفعة
            app(PriceEngineService::class)->generate($batch);

            AuditLogger::critical(
                'batch_pricing_override',
                $batch,
                "تخصيص تسعير دفعة {$batch->batch_number} — الوضع: {$data['mode']}",
                $old,
                [
                    'pricing_rule_id'       => $batch->pricing_rule_id,
                    'custom_markup_percent' => $batch->custom_markup_percent,
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'تم تخصيص التسعير وإعادة الحساب',
                'batch'   => $batch->fresh(['prices']),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* ============================================================
    POST /api/medicine-prices/{price}/lock
    قفل سعر وحدة معينة يدوياً
    ============================================================ */
    public function lockPrice(Request $request, MedicinePrice $price)
    {
        $data = $request->validate([
            'sell_price' => 'required|numeric|min:0',
            'reason'     => 'required|string|max:255',
        ]);

        $old = [
            'sell_price' => (float) $price->sell_price,
            'price_mode' => $price->price_mode,
        ];

        $profitAmount = $data['sell_price'] - (float) $price->buy_price;
        $profitPercent = (float) $price->buy_price > 0
            ? ($profitAmount / (float) $price->buy_price) * 100
            : 0;

        $price->update([
            'sell_price'     => $data['sell_price'],
            'profit_amount'  => round($profitAmount, 2),
            'profit_percent' => round($profitPercent, 2),
            'price_mode'     => 'manual',
            'locked_by'      => auth()->id(),
            'locked_at'      => now(),
            'lock_reason'    => $data['reason'],
        ]);

       AuditLogger::critical(
            'price_locked',
            $price,
            "قفل سعر {$price->medicine?->name} عند {$data['sell_price']} — السبب: {$data['reason']}",
            $old,
            ['sell_price' => $data['sell_price']]
        );

        return response()->json([
            'message' => 'تم قفل السعر',
            'price'   => $price->fresh(),
        ]);
    }

    /* ============================================================
    POST /api/medicine-prices/{price}/unlock
    فتح القفل وإعادة الحساب التلقائي
    ============================================================ */
    public function unlockPrice(MedicinePrice $price)
    {
        $price->update([
            'price_mode'  => 'auto',
            'locked_by'   => null,
            'locked_at'   => null,
            'lock_reason' => null,
        ]);

        // ✅ أعد حساب أسعار الدفعة كاملة (سيُعيد لهذه الوحدة سعرها التلقائي)
        try {
            app(PriceEngineService::class)->generate($price->batch);
        } catch (\Throwable $e) {
            report($e);
        }

        AuditLogger::log(
            'price_unlocked',
            $price,
            "فتح قفل سعر {$price->medicine?->name} — أعيد الحساب تلقائياً",
            [],
            [],
            'warning'
        );

        return response()->json([
            'message' => 'تم فتح القفل وإعادة الحساب',
            'price'   => $price->fresh(),
        ]);
    }

    /* ============================================================
       POST /api/medicines/{medicine}/override-pricing
       تخصيص تسعير الدواء كاملاً (ينشئ Rule سرية)
       ============================================================ */
    public function overrideMedicinePricing(Request $request, Medicine $medicine)
    {
        $data = $request->validate([
            'mode' => 'required|in:custom,reset',

            // عند mode=custom
            'type'          => 'required_if:mode,custom|in:percentage,fixed,multiply',
            'value'         => 'required_if:mode,custom|numeric',
            'rounding_mode' => 'nullable|in:none,nearest,up,down',
            'rounding_unit' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            if ($data['mode'] === 'reset') {
                // فك الربط مع قاعدة خاصة
                $oldRuleId = $medicine->pricing_rule_id;

                // احذف القاعدة السرية إن كانت خاصة بهذا الدواء
                if ($oldRuleId) {
                    $oldRule = PriceEngineRule::find($oldRuleId);
                    if ($oldRule && str_starts_with($oldRule->name, 'خاص - ')) {
                        $oldRule->delete();
                    }
                }

                $medicine->update(['pricing_rule_id' => null]);

                AuditLogger::log(
                    'medicine_pricing_reset',
                    $medicine,
                    "إعادة تسعير {$medicine->name} للقاعدة العامة",
                    [],
                    [],
                    'warning'
                );

            } else {
                // أنشئ / حدّث قاعدة سرية
                $ruleName = "خاص - {$medicine->name}";

                $rule = PriceEngineRule::updateOrCreate(
                    ['name' => $ruleName],
                    [
                        'type'       => $data['type'],
                        'value'      => $data['value'],
                        'apply_on'   => 'buy_price',
                        'sort_order' => 999,
                        'is_active'  => true,
                        'is_default' => false,
                        'settings'   => [
                            'rounding' => [
                                'mode' => $data['rounding_mode'] ?? 'none',
                                'unit' => $data['rounding_unit'] ?? 0,
                            ],
                        ],
                    ]
                );

                $medicine->update(['pricing_rule_id' => $rule->id]);

                AuditLogger::critical(
                    'medicine_pricing_override',
                    $medicine,
                    "تخصيص تسعير {$medicine->name}: {$data['type']} = {$data['value']}",
                    [],
                    ['rule_id' => $rule->id]
                );
            }

            // ✅ أعد حساب كل دفعات الدواء
            $batches = MedicineBatch::where('medicine_id', $medicine->id)->get();
            $engine = app(PriceEngineService::class);
            foreach ($batches as $batch) {
                $engine->generate($batch);
            }

            DB::commit();

            return response()->json([
                'message' => 'تم تحديث تسعير الدواء وإعادة حساب ' . $batches->count() . ' دفعة',
                'medicine' => $medicine->fresh(['pricingRule', 'units.unit']),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /* ============================================================
       GET /api/medicines/{medicine}/pricing-info
       معلومات التسعير الحالية
       ============================================================ */
    public function pricingInfo(Medicine $medicine)
    {
        $medicine->load(['pricingRule', 'units.unit']);

        // ✅ الوحدة الأساسية (العلبة عادةً)
        $baseUnit = $medicine->units->firstWhere('is_base', true)
                ?? $medicine->units->first();
        $baseUnitId = $baseUnit?->unit_id;

        $batches = MedicineBatch::where('medicine_id', $medicine->id)
            ->where('remaining_quantity', '>', 0)
            ->with('prices')
            ->get()
            ->map(function ($batch) use ($baseUnitId) {
                // ✅ اختر السعر المطابق للوحدة الأساسية، أو الأعلى سعراً
                $price = $batch->prices->firstWhere('unit_id', $baseUnitId)
                    ?? $batch->prices->sortByDesc('buy_price')->first();

                return [
                    'id'           => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expiry_date'  => $batch->expiry_date,
                    'buy_price'    => (float) $batch->buy_price,
                    'sell_price'   => $price ? (float) $price->sell_price : 0,
                    'price_id'     => $price?->id,          // ✅ للقفل
                    'unit_name'    => $baseUnit?->unit?->name ?? 'وحدة',
                    'is_locked'    => $batch->prices->where('price_mode', 'manual')->isNotEmpty(),
                    'lock_reason'  => $batch->prices->firstWhere('price_mode', 'manual')?->lock_reason,
                    'locked_at'    => $batch->prices->firstWhere('price_mode', 'manual')?->locked_at?->toIso8601String(),
                    'has_override' => $batch->pricing_rule_id !== null
                        || $batch->custom_markup_percent !== null,
                ];
            });

        return response()->json([
            'medicine' => [
                'id'   => $medicine->id,
                'name' => $medicine->name,
            ],
            'current_rule' => $medicine->pricingRule ? [
                'id'     => $medicine->pricingRule->id,
                'name'   => $medicine->pricingRule->name,
                'type'   => $medicine->pricingRule->type,
                'value'  => (float) $medicine->pricingRule->value,
                'is_custom' => str_starts_with($medicine->pricingRule->name, 'خاص - '),
            ] : null,
            'batches' => $batches,
        ]);
    }
}