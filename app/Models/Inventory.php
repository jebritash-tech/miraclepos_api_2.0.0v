<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //
    protected $fillable = [

        'medicine_id',

        'branch_id',

        'quantity',
        'purchased_quantity',
        'factor',
        'reserved_quantity',

        'available_quantity',

        'minimum_quantity',

        'maximum_quantity',

    ];

    protected $casts = [

        'quantity' => 'integer',

        'reserved_quantity' => 'integer',

        'available_quantity' => 'integer',

        'minimum_quantity' => 'integer',

        'maximum_quantity' => 'integer',

    ];

    public function medicine()
    {
        return $this->belongsTo(
            Medicine::class
        );
    }

    public function branch()
    {
        return $this->belongsTo(
            Branch::class
        );
    }
}
