<?php

namespace App\Models;

use App\Models\Medicine;
use App\Models\SaleItem;
use App\Models\MedicinePrice;
use Illuminate\Database\Eloquent\Model;

class MedicineBatch extends Model
{
    protected $fillable = [
        'medicine_id',
        'purchase_item_id',
        'branch_id',
        'batch_number',
        'expiry_date',
        'buy_price',
        'selling_price',
        'quantity',
        'remaining_quantity',
        'purchase_unit_id',
    ];

    // ✅ Bug fix #3: cast خاطئ على عمود غير موجود (quantity_base) — حُذف

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function prices()
    {
        return $this->hasMany(MedicinePrice::class, 'batch_id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | purchaseUnit — الصحيح
    |--------------------------------------------------------------------------
    |
    | purchase_unit_id يشير إلى medicine_units.id
    |--------------------------------------------------------------------------
    */

    public function purchaseUnit()
    {
        return $this->belongsTo(MedicineUnit::class, 'purchase_unit_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ✅ Bug fix #3: حذف unit() — كان يشير لـ Unit بينما القيمة medicine_units.id
    |--------------------------------------------------------------------------
    | أي كود يستخدم $batch->unit يجب أن يستخدم $batch->purchaseUnit->unit بدلاً منه
    |--------------------------------------------------------------------------
    */

    // ❌ محذوف:
    // public function unit() {
    //     return $this->belongsTo(\App\Models\Unit::class, 'purchase_unit_id');
    // }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getConvertedQuantityAttribute()
    {
        $medicineUnit = \Illuminate\Support\Facades\DB::table('medicine_units')
            ->where('id', $this->purchase_unit_id)
            ->first();

        if (!$medicineUnit) {
            return number_format($this->remaining_quantity, 0);
        }

        $unit = \App\Models\Unit::find($medicineUnit->unit_id);
        $unitName = $unit ? $unit->name : '';

        $factor = $medicineUnit->factor ?? 1;

        if ($factor > 1) {
            $packs = floor($this->remaining_quantity / $factor);
            $remainder = $this->remaining_quantity % $factor;

            if ($remainder == 0) {
                return number_format($packs, 0) . ' ' . $unitName;
            }
            return number_format($packs, 0) . ' ' . $unitName . ' (' . number_format($this->remaining_quantity, 0) . ' Pieces)';
        }

        return number_format($this->remaining_quantity, 0) . ' ' . ($unitName ?: 'Piece');
    }

    public function getFormattedStockAttribute()
    {
        return $this->getConvertedQuantityAttribute();
    }
}