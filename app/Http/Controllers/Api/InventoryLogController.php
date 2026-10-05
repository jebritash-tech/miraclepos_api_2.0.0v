<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use Illuminate\Http\Request;

class InventoryLogController extends Controller
{
    // عرض سجلات دواء معين باستخدام الـ ID الخاص به
    public function getMedicineLogs($medicineId)
    {
        return InventoryLog::whereHas('batch', function($query) use ($medicineId) {
            $query->where('medicine_id', $medicineId);
        })
        ->with('batch.medicine:id,name') // لجلب اسم الدواء
        ->orderBy('created_at', 'desc')
        ->get();
    }

    // عرض كل الحركات (للإدارة)
    public function index()
    {
        return InventoryLog::with('batch.medicine:id,name')->latest()->paginate(20);
    }
}
