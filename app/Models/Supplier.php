<?php

namespace App\Models;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Supplier extends Model
{
    //
    protected $fillable = ['name', 'contact_person', 'phone', 'email', 'address'];
    use SoftDeletes;
    
    // purchases
    public function purchases() {
        return $this->hasMany(Purchase::class);
    }
}
