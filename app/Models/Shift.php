<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{

    protected $fillable = [

        'user_id',

        'branch_id',

        'opening_cash',

        'expected_cash',

        'closing_cash',

        'cash_sales',

        'card_sales',

        'refund_amount',

        'expenses_amount',

        'debts_amount',

        'withdraw_amount',

        'sales_count',

        'status',

        'opened_at',

        'closed_at'

    ];

    protected $casts = [

        'opened_at' => 'datetime',

        'closed_at' => 'datetime'

    ];
    protected $appends = [
        'difference',
        'duration',
    ];
    public function getOpenedAtFormattedAttribute()
    {
        return $this->opened_at?->format('Y-m-d H:i');
    }

    public function getClosedAtFormattedAttribute()
    {
        return $this->closed_at?->format('Y-m-d H:i');
    }
    public function getOpenedAttribute()
    {

        return optional($this->opened_at)

            ->format('Y-m-d H:i');

    }

    public function getClosedAttribute()
    {

        return optional($this->closed_at)

            ->format('Y-m-d H:i');

    }
    public function getDifferenceAttribute()
    {

        if ($this->closing_cash === null) {

            return null;

        }

        return

            (float)$this->closing_cash

            -

            (float)$this->expected_cash;

    }

    public function getDurationAttribute()
    {

        if (!$this->closed_at) {

            return null;

        }

        return

            $this->opened_at

            ->diffForHumans(

                $this->closed_at,

                true

            );

    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function withdrawals()
    {

        return $this->hasMany(
            Withdrawal::class
        );

    }

    public function expenses()
    {
        return $this->hasMany(
            Expense::class
        );
    }


    public function activities()
    {
        return $this->hasMany(ShiftActivity::class);
    }
}