<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'medicine_batch_id',
        'medicine_unit_id',
        'quantity',
        'unit',
        'quantity_pieces',
        'quantity_base',
        'price',
        'profit',

        // ✅ الحقول الجديدة
        'line_discount',
        'invoice_share',
        'effective_total',
    ];

    protected $casts = [
        'price'           => 'decimal:2',
        'profit'          => 'decimal:2',
        'line_discount'   => 'decimal:2',
        'invoice_share'   => 'decimal:2',
        'effective_total' => 'decimal:2',
    ];

    /* ============================================================
       Relationships
       ============================================================ */

    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function refundItems()
    {
        return $this->hasMany(RefundItem::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'medicine_unit_id');
    }

    /* ============================================================
       Accessors
       ============================================================ */

    public function getRefundedQuantityAttribute()
    {
        return (int) $this->refundItems()->sum('quantity');
    }

    public function getRemainingQuantityForRefundAttribute()
    {
        return max(0, (int) $this->quantity - (int) $this->refunded_quantity);
    }

    /**
     * ✅ السعر الفعلي للوحدة الواحدة (بعد كل الخصومات)
     *
     * يُستخدم عند الإرجاع لضمان استرداد ما دفعه العميل فعلاً.
     */
    public function getEffectiveUnitPriceAttribute(): float
    {
        $qty = (float) $this->quantity;
        if ($qty <= 0) return 0.0;

        return round((float) $this->effective_total / $qty, 2);
    }

    /**
     * مجموع الخصومات على هذا البند (مباشر + نصيبه من الفاتورة)
     */
    public function getTotalDiscountAttribute(): float
    {
        return round(
            (float) $this->line_discount + (float) $this->invoice_share,
            2
        );
    }
}