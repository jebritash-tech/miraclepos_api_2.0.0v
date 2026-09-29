<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{

    protected $fillable=[

        'user_id',

        'branch_id',

        'sale_id',

        'total_amount',

        'paid_amount',

        'remaining_amount',

        'status',

        'due_date',

        'notes',
        'created_at',  // ✅ جديد

    ];

    protected $casts=[

        'due_date'=>'datetime'

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function payments(){

        return $this->hasMany(DebtPayment::class);

    }

}