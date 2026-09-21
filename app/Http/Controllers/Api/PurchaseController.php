<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\MedicineBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PurchaseService;
use App\Http\Requests\PurchaseRequest;

class PurchaseController extends Controller
{
    public function store(PurchaseRequest $request)
    {
        DB::beginTransaction();

        try {
            $purchase = app(PurchaseService::class)->store($request->validated());

            DB::commit();

            $purchases = Purchase::with(['supplier', 'items.medicine'])
                ->latest()
                ->paginate(10);

            return response()->json([
                'message' => 'تم تسجيل المشتريات بنجاح',
                'purchase' => $purchase->load('items', 'supplier'),
                'purchases' => $purchases->items(),
                'pagination' => [
                    'currentPage' => $purchases->currentPage(),
                    'lastPage' => $purchases->lastPage(),
                    'total' => $purchases->total(),
                    'perPage' => $purchases->perPage()
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // إضافة دالة لجلب جميع المشتريات (للجدول)
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'items.medicine'])
            ->latest()
            ->paginate(10);

        return response()->json([
                
                'purchases' => $purchases->items(), // البيانات
                'pagination' => [
                    'currentPage' => $purchases->currentPage(),
                    'lastPage' => $purchases->lastPage(),
                    'total' => $purchases->total(),
                    'perPage' => $purchases->perPage()
                ]
            ]);
    }

    // إضافة دالة لجلب فاتورة شراء محددة
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'items.medicine'])
            ->findOrFail($id);

        return response()->json($purchase);
    }
}