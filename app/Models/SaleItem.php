<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    //
    protected $fillable = [
        'sale_id',
        'medicine_batch_id',
        'medicine_unit_id',
        'quantity',
        'unit',
        'quantity_pieces',
        'quantity_base',
        'price',
        'profit'
    ];

    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }
    // app/Models/SaleItem.php
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function refundItems()
    {
        return $this->hasMany(RefundItem::class);
    }

    // حساب الكمية المرتجعة لهذا الصنف
    public function getRefundedQuantityAttribute()
    {
        return $this->refundItems()->sum('quantity');
    }

    // الكمية المتبقية القابلة للإرجاع
    public function getRemainingQuantityForRefundAttribute()
    {
        return $this->quantity - $this->refunded_quantity;
    }

    // أضف هذه العلاقة
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'medicine_unit_id');
    }
}
