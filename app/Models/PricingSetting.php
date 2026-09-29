<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingSetting extends Model
{
    //
    protected $fillable=[

        'exchange_rate',

        'profit_percent',

        'extra_cost',

        'round_to',

        'active',

    ];
}
