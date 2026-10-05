<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDebt extends Model
{

    protected $fillable=[

        'user_id',

        'shift_id',

        'amount',

        'paid_amount',

        'reason',

        'status'

    ];

    protected $appends=[

        'remaining'

    ];

    public function getRemainingAttribute()
    {

        return $this->amount
            -
            $this->paid_amount;

    }

    public function user()
    {

        return $this->belongsTo(User::class);

    }

    public function shift()
    {

        return $this->belongsTo(Shift::class);

    }

}