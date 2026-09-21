<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\PriceEngineRule;

class PricingService
{
    /**
     * ✅ الحل الهرمي: دفعة → دواء → عام
     */
    public function resolveRules(
        Medicine $medicine,
        ?MedicineBatch $batch = null
    ): array {
        // 1. قاعدة خاصة بالدفعة
        if ($batch?->pricing_rule_id) {
            $rule = PriceEngineRule::find($batch->pricing_rule_id);
            if ($rule?->is_active) {
                return [
                    'rules'  => [$rule],
                    'source' => 'batch_rule',
                ];
            }
        }

        // 2. markup مخصص للدفعة
        if ($batch?->custom_markup_percent !== null) {
            $dynamicRule = new PriceEngineRule([
                'name'     => "دفعة #{$batch->id} — {$batch->custom_markup_percent}%",
                'type'     => 'percentage',
                'value'    => $batch->custom_markup_percent,
                'apply_on' => 'buy_price',
                'is_active' => true,
                'settings' => [
                    'rounding' => ['mode' => 'up', 'unit' => 100],
                ],
            ]);
            return [
                'rules'  => [$dynamicRule],
                'source' => 'batch_custom',
            ];
        }

        // 3. قاعدة خاصة بالدواء
        if ($medicine->pricing_rule_id) {
            $rule = $medicine->pricingRule;
            if ($rule?->is_active) {
                return [
                    'rules'  => [$rule],
                    'source' => 'medicine_rule',
                ];
            }
        }

        // 4. القاعدة الافتراضية العامة
        $default = PriceEngineRule::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->orderBy('sort_order')
            ->first();

        if (!$default) {
            throw new \RuntimeException(
                'لا توجد قاعدة تسعير مفعلة أو افتراضية.'
            );
        }

        return [
            'rules'  => [$default],
            'source' => 'global_default',
        ];
    }

    /**
     * ✅ حساب سعر البيع (مع معلومات المصدر)
     */
    public function calculateSellPrice(
        float $buyPrice,
        Medicine $medicine,
        ?MedicineBatch $batch = null
    ): array {
        $resolved = $this->resolveRules($medicine, $batch);
        $rule = $resolved['rules'][0];

        $rawSellPrice = $this->applyRule($buyPrice, $rule);
        $sellPrice = $this->applyRounding($rawSellPrice, $rule);

        $profitAmount = $sellPrice - $buyPrice;
        $profitPercent = $buyPrice > 0 ? ($profitAmount / $buyPrice) * 100 : 0;

        return [
            'rule_id'         => $rule->id ?? null,
            'rule_name'       => $rule->name,
            'rule_source'     => $resolved['source'],
            'buy_price'       => round($buyPrice, 2),
            'raw_sell_price'  => round($rawSellPrice, 2),
            'sell_price'      => round($sellPrice, 2),
            'profit_amount'   => round($profitAmount, 2),
            'profit_percent'  => round($profitPercent, 2),
        ];
    }

    protected function applyRule(float $buyPrice, PriceEngineRule $rule): float
    {
        $value = (float) $rule->value;

        return match ($rule->type) {
            'percentage' => $buyPrice + ($buyPrice * ($value / 100)),
            'fixed'      => $buyPrice + $value,
            'multiply'   => $buyPrice * $value,
            default      => throw new \RuntimeException(
                "نوع قاعدة التسعير غير معروف: {$rule->type}"
            ),
        };
    }

    /**
     * ✅ التقريب الذكي — افتراضي متدرج
     *
     * القواعد:
     * 1. إذا كانت القاعدة تحدد "tiered: false" صراحة → استخدم تقريب ثابت
     * 2. إذا كانت القاعدة تحدد "tiered: true" صراحة → استخدم تقريب متدرج
     * 3. إذا لم يوجد تحديد → السلوك الافتراضي = تقريب متدرج (ذكي)
     *
     * هذا يضمن أن أي قاعدة جديدة من الواجهة تستخدم التقريب المتدرج تلقائياً.
     */
    protected function applyRounding(float $price, PriceEngineRule $rule): float
    {
        $settings = $rule->roundingSettings();
        $mode = $settings['mode'] ?? 'none';

        // إذا لم يوجد تقريب إطلاقاً → أعد السعر كما هو
        if ($mode === 'none') {
            return $price;
        }

        // ✅ كشف الوضع
        $tiered = $settings['tiered'] ?? true;  // ← الافتراضي true
        $unit = (float) ($settings['unit'] ?? 0);

        // ✅ التقريب المتدرج (افتراضي)
        // يعمل إذا: tiered = true
        if ($tiered === true) {
            return $this->applyTieredRounding($price, $mode);
        }

        // ⚠️ التقريب الثابت (فقط إذا عطّل المستخدم المتدرج صراحة)
        if ($unit <= 0) {
            return $price; // لا يوجد تقريب ثابت محدد
        }

        return match ($mode) {
            'nearest' => round($price / $unit) * $unit,
            'up'      => ceil($price / $unit) * $unit,
            'down'    => floor($price / $unit) * $unit,
            default   => $price,
        };
    }

    /**
     * ✅ التقريب المتدرج — النواة
     *
     * @param float  $price السعر النظري
     * @param string $mode  up|nearest|down
     * @return float
     */
    protected function applyTieredRounding(float $price, string $mode = 'up'): float
    {
        // 1. تحديد التقريب المناسب حسب نطاق السعر
        $unit = $this->resolveTierUnit($price);

        if ($unit <= 0) {
            return $price;
        }

        // 2. تطبيق التقريب
        return match ($mode) {
            'nearest' => round($price / $unit) * $unit,
            'up'      => ceil($price / $unit) * $unit,
            'down'    => floor($price / $unit) * $unit,
            default   => $price,
        };
    }

    /**
     * ✅ تحديد الوحدة المناسبة للنطاق السعري
     *
     * يمكن تخصيص النطاقات عبر إعدادات عامة لاحقاً.
     */
    protected function resolveTierUnit(float $price): float
    {
        // النطاقات القابلة للتخصيص
        $tiers = [
            ['max' => 50,      'unit' => 5],
            ['max' => 500,     'unit' => 25],
            ['max' => 5000,    'unit' => 100],
            ['max' => PHP_FLOAT_MAX, 'unit' => 500],
        ];

        foreach ($tiers as $tier) {
            if ($price < $tier['max']) {
                return (float) $tier['unit'];
            }
        }

        return 5.0; // fallback آمن
    }
}