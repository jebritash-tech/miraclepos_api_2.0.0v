<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = [

        'shift_id',

        'user_id',

        'type',

        'amount',

        'notes',
        'created_at',  // ✅ جديد

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
