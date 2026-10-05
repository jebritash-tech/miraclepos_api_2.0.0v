<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'total_amount',
        'shift_id',
        'profit_amount',
        'payment_method',
        'bank_name',
        'bank_reference',
        'bank_transfer_date',
        'bank_notes',
        'created_at',

        // ✅ الخصم (موجود + جديد)
        'discount_type',
        'discount_value',
        'discount_amount',
        'discount_reason',
        'discount_by',

        // ✅ جديد
        'line_discount_total',
        'discount_scope',
    ];

    protected $casts = [
        'discount_value'       => 'decimal:2',
        'discount_amount'      => 'decimal:2',
        'line_discount_total'  => 'decimal:2',
        'total_amount'         => 'decimal:2',
        'profit_amount'        => 'decimal:2',
    ];

    protected $appends = [
        'subtotal',
        'has_discount',
        'total_discount',
    ];

    /* ============================================================
       Relationships
       ============================================================ */

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function debt()
    {
        return $this->hasOne(Debt::class);
    }

    public function discountBy()
    {
        return $this->belongsTo(User::class, 'discount_by');
    }

    /* ============================================================
       Accessors
       ============================================================ */

    /**
     * ✅ إجمالي الفاتورة قبل أي خصم
     *
     * subtotal = المبلغ الصافي + خصم الفاتورة + خصومات البنود
     */
    public function getSubtotalAttribute(): float
    {
        return round(
            (float) $this->total_amount
            + (float) $this->discount_amount
            + (float) $this->line_discount_total,
            2
        );
    }

    /**
     * هل الفاتورة عليها أي خصم (فاتورة أو بنود)؟
     */
    public function getHasDiscountAttribute(): bool
    {
        return ((float) $this->discount_amount > 0)
            || ((float) $this->line_discount_total > 0);
    }

    /**
     * إجمالي كل الخصومات (فاتورة + بنود)
     */
    public function getTotalDiscountAttribute(): float
    {
        return round(
            (float) $this->discount_amount + (float) $this->line_discount_total,
            2
        );
    }
}