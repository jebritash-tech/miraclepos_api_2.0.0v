<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    //
    protected $fillable = ['medicine_batch_id', 'type', 'quantity_changed', 'notes'];   

    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }

    public function medicineBatch()
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }
}
