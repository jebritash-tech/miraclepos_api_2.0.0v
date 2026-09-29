<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineUnit extends Model
{

    protected $fillable = [

        'medicine_id',
    
        'unit_id',
    
        'factor',
    
        'barcode',
    
        'allow_sale',
    
        'is_base',
    
        'sort_order'
    
    ];

    public function medicine()
    {

        return $this->belongsTo(

            Medicine::class

        );

    }

    // public function unit()
    // {

    //     return $this->belongsTo(

    //         Unit::class

    //     );

    // }

    // This links the pivot row to the master unit definition (e.g. getting the unit name)
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    

}