<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\MedicineBatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Medicine extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'barcode', 'category_id','pricing_rule_id','pricing_method','notes'];
    
    //
    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function batches() {
        return $this->hasMany(MedicineBatch::class);
    }

    // public function units()
    // {

    //     return $this->hasMany(

    //         MedicineUnit::class

    //     )->orderBy(

    //         'sort_order'

    //     );

    // }

    public function inventory()
    {
        return $this->hasMany(
            Inventory::class
        );
    }

    public function prices()
    {
        return $this->hasMany(
            MedicinePrice::class
        );
    }

    // public function units()
    // {
    //     return $this->hasManyThrough(
    //         Unit::class,          // Final model you want to access
    //         MedicineUnit::class,  // Intermediate model / table
    //         'medicine_id',        // Foreign key on medicine_units table...
    //         'id',                 // Foreign key on units table...
    //         'id',                 // Local key on medicines table...
    //         'unit_id'             // Local key on medicine_units table
    //     )->withPivot([
    //         'id',
    //         'factor',
    //         'barcode',
    //         'allow_sale',
    //         'is_base',
    //         'sort_order'
    //     ]);
    // }
    public function units()
    {
        return $this->hasMany(MedicineUnit::class, 'medicine_id')->orderBy('sort_order');
    }
    public function pricingRule()
    {
        return $this->belongsTo(

            \App\Models\PriceEngineRule::class,

            'pricing_rule_id'

        );
    }
}
