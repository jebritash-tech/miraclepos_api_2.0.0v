<?php

namespace App\Models;

use App\Models\Purchase;
use App\Models\MedicineBatch;
use App\Models\Medicine;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'medicine_id',
        'unit_id',
        'quantity',
        'factor',
        'base_quantity',
        'buy_price',
        'subtotal',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ Bug fix #4: unit() الآن يشير لـ Unit (كان MedicineUnit بشكل خاطئ)
    |--------------------------------------------------------------------------
    |
    | purchase_items.unit_id يشير إلى units.id (بحسب migration + PurchaseService)
    |--------------------------------------------------------------------------
    */

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function batch()
    {
        return $this->hasOne(MedicineBatch::class, 'purchase_item_id');
    }
}