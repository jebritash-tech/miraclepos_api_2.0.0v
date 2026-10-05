<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{

    protected $fillable=[

        'name',

        'symbol',

        'active'

    ];

    public function medicineUnits()
    {

        return $this->hasMany(MedicineUnit::class);

    }

    public function prices()
    {
        return $this->hasMany(
            MedicinePrice::class
        );
    }

}