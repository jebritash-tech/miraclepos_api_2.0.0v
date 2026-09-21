<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicineBatch;
class MedicineBatchesController extends Controller
{
    //
        /**
     * عرض قائمة الأدوية المتوفرة مرتبة حسب تاريخ الانتهاء (FEFO)
     */
    public function index(Request $request)
    {
        try {
            // الحصول على معرف الفرع الخاص بالمستخدم المسجل حالياً
            $userBranchId = auth()->user()->branch_id;

            $batches = MedicineBatch::with(['medicine:id,name,barcode,category_id', 'medicine.category:id,name'])
                ->where('branch_id', $userBranchId) // الفلترة بناءً على فرع المستخدم
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get()
                ->map(function ($batch) {
                    return [
                        'id'            => $batch->id,
                        'name'          => $batch->medicine->name,
                        'barcode'       => $batch->medicine->barcode,
                        'category'      => $batch->medicine->category->name ?? 'غير مصنف',
                        'selling_price' => (float) $batch->selling_price,
                        'stock'         => (int) $batch->quantity,
                        'expiry_date'   => $batch->expiry_date,
                    ];
                });

            return response()->json($batches, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب قائمة الأدوية الخاصة بفرعك',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
