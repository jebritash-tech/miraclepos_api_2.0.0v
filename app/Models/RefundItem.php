<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundItem extends Model
{
    protected $fillable = ['refund_id', 'sale_item_id', 'quantity', 'price'];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }
}