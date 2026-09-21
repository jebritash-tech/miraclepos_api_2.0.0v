<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicinePrice extends Model
{
    protected $fillable = [
        'batch_id',
        'medicine_id',
        'unit_id',
        'buy_price',
        'sell_price',
        'profit_amount',
        'profit_percent',
        'is_active',
        'price_mode',
        'locked_by',
        'locked_at',
        'lock_reason',
    ];

    protected $casts = [
        'buy_price'      => 'decimal:2',
        'sell_price'     => 'decimal:2',
        'profit_amount'  => 'decimal:2',
        'profit_percent' => 'decimal:2',
        'locked_at'      => 'datetime',
        'is_active'      => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ Bug fix #5: unit() الآن يشير لـ Unit (كان MedicineUnit بشكل خاطئ)
    |--------------------------------------------------------------------------
    |
    | medicine_prices.unit_id يشير إلى units.id (بحسب migration + PriceEngineService)
    |--------------------------------------------------------------------------
    */

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isLocked(): bool
    {
        return $this->price_mode === 'manual';
    }

    public function lock(int $userId, ?string $reason = null): void
    {
        $this->update([
            'price_mode'  => 'manual',
            'locked_by'   => $userId,
            'locked_at'   => now(),
            'lock_reason' => $reason,
        ]);
    }

    public function unlock(): void
    {
        $this->update([
            'price_mode'  => 'auto',
            'locked_by'   => null,
            'locked_at'   => null,
            'lock_reason' => null,
        ]);
    }
}