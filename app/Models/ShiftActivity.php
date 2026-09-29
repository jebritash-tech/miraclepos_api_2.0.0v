<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftActivity extends Model
{
    protected $fillable = [

        'shift_id',

        'user_id',

        'type',

        'amount',

        'reference_type',

        'reference_id',

        'title',

        'description',

        'meta'

    ];

    protected $casts = [

        'meta'=>'array'

    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}