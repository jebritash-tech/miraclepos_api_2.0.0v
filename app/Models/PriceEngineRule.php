<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceEngineRule extends Model
{
    protected $fillable = [
        'name',
        'type',
        'apply_on',
        'value',
        'sort_order',
        'is_active',
        'is_default',
        'settings',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'settings' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function medicines()
    {
        return $this->hasMany(
            Medicine::class,
            'pricing_rule_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
        /**
     * ✅ إعدادات التقريب — الوضع الافتراضي: متدرج
     *
     * المنطق:
     * - إذا لم يكن هناك settings.rounding → السلوك الافتراضي = tiered (متدرج)
     * - إذا كان هناك settings.rounding.tiered = true → متدرج
     * - إذا كان settings.rounding.tiered = false → تقريب ثابت (يحتاج unit)
     */
    public function roundingSettings(): array
    {
        $rounding = data_get($this->settings, 'rounding', []);

        // ✅ كشف الوضع المتدرج — الافتراضي true
        // إذا لم يكن محدداً صراحة، يُعتبر true
        $tiered = array_key_exists('tiered', $rounding)
            ? (bool) $rounding['tiered']
            : true;  // ← الافتراضي

        return [
            'mode'   => $rounding['mode'] ?? 'up',
            'unit'   => max(0, (float) ($rounding['unit'] ?? 0)),
            'tiered' => $tiered,
        ];
    }
}