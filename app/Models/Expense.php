<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{

    protected $fillable=[

        'shift_id',

        'user_id',

        'amount',

        'title',

        'notes',
        'category',           // ✅ جديد
        'payment_method',     // ✅ جديد
        'bank_name',          // ✅ جديد
        'bank_reference',     // ✅ جديد
        'created_at',
    ];

    public function shift()
    {

        return $this->belongsTo(
            Shift::class
        );

    }

    public function user()
    {

        return $this->belongsTo(
            User::class
        );

    }

}
