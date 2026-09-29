<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = [

        'user_id',

        'month',

        'year',

        'basic_salary',

        'allowances',

        'deductions',

        'net_salary',

        'status',

        'paid_at',

        'payment_method',

        'bank_name',

        'bank_reference',

        'notes'

    ];

    protected $casts = [

        'basic_salary' => 'decimal:2',

        'allowances' => 'decimal:2',

        'deductions' => 'decimal:2',

        'net_salary' => 'decimal:2',

        'paid_at' => 'date'

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(

            User::class

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPaid()
    {
        return

            $this->status === 'paid';

    }

    public function isPending()
    {
        return

            $this->status === 'pending';

    }
}