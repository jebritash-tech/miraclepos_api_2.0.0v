<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineBatch;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'medicine_batch_id' => 'required|exists:medicine_batches,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. إعادة الكمية إلى الدفعة المحددة
            $batch = MedicineBatch::find($request->medicine_batch_id);
            $batch->increment('quantity', $request->quantity);

            // 2. تسجيل العملية في الـ InventoryLog
            InventoryLog::create([
                'medicine_batch_id' => $batch->id,
                'type' => 'return',
                'quantity_changed' => $request->quantity, // موجب لأنها عودة للمخزن
                'notes' => 'Customer Return: ' . $request->reason
            ]);

            return response()->json(['message' => 'تم استلام المرتجع وتحديث المخزون بنجاح'], 200);
        });
    }
}
