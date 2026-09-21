<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{

    protected $fillable=[

        'debt_id',

        'user_id',

        'amount',

        'notes'

    ];

    public function debt(){

        return $this->belongsTo(Debt::class);

    }

    public function user(){

        return $this->belongsTo(User::class);

    }

}
