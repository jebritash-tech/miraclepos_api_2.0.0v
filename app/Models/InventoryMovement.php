<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [

        'medicine_id',

        'batch_id',

        'branch_id',

        'type',

        'quantity',

        'balance_after',

        'reference_type',

        'reference_id',

        'notes',

    ];

    protected $casts = [

        'quantity' => 'integer',

        'balance_after' => 'integer',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}