<?php

namespace App\Models;
use App\Models\Supplier;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    

    protected $fillable = [

        'supplier_id',

        'branch_id',

        'user_id',

        'invoice_number',

        'purchase_date',

        'exchange_rate',

        'discount',

        'notes',

        'subtotal',

        'total_amount'

    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

}
