<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\MedicineBatch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // 1. إحصائيات لوحة التحكم (Overview)
    public function getStats()
    {
        return response()->json([
            'total_sales' => Sale::sum('total_amount'),
            'low_stock'   => MedicineBatch::where('quantity', '<', 5)->count(),
            'today_sales' => Sale::whereDate('created_at', today())->count()
        ]);
    }

    // 2. كتالوج الأدوية للمدير (بدون دفعات)
    public function getMedicineCatalogue()
    {
        return response()->json(Medicine::with('category')->get());
    }

    // 3. تخزين المشتريات (Purchase System)
    public function storePurchase(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_number' => 'required|string',
            'items.*.expiry_date' => 'required|date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric',
            'items.*.selling_price' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. حساب الإجمالي
            $totalAmount = collect($request->items)->sum(function ($item) {
                return $item['cost_price'] * $item['quantity'];
            });

            // 2. إنشاء الفاتورة
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                
                'total_amount' => $totalAmount,
            ]);

            // 3. إضافة العناصر وإنشاء الدفعات (Batches)
            foreach ($request->items as $item) {
                // إنشاء الدفعة في جدول الدفعات
                $batch = MedicineBatch::create([
                    'medicine_id' => $item['medicine_id'],
                    'branch_id'   => $request->branch_id,
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'selling_price' => $item['selling_price'],
                ]);

                // تسجيل تفاصيل العنصر في الفاتورة
                $purchase->items()->create([
                    'medicine_batch_id' => $batch->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['cost_price'],
                ]);
                
                // إضافة سجل في الـ InventoryLog
                \App\Models\InventoryLog::create([
                    'medicine_batch_id' => $batch->id,
                    'type' => 'purchase',
                    'quantity_changed' => $item['quantity'], // موجب لأنها إضافة
                    'notes' => 'Purchase Invoice ID: ' . $purchase->id
                ]);
            }

            return response()->json(['message' => 'تم تسجيل المشتريات بنجاح', 'purchase_id' => $purchase->id], 201);
        });
    }

    public function indexPurchases() {
        return Purchase::with(['supplier', 'items.medicine'])->latest()->get();
    }
    public function getAnalytics() 
    {
        
        $topSelling = \App\Models\SaleItem::select('medicine_batch_id', \DB::raw('SUM(quantity) as sold'))
            ->groupBy('medicine_batch_id')
            ->orderBy('sold', 'desc')
            ->take(5)
            ->with('batch.medicine:id,name')
            ->get();

        return response()->json([
            'total_sales'      => \App\Models\Sale::sum('total_amount'),
            'total_purchases'  => \App\Models\Purchase::sum('total_amount'),
            'low_stock_items'  => \App\Models\MedicineBatch::where('quantity', '<', 10)->count(),
            'recent_sales'     => \App\Models\Sale::latest()->take(5)->get(),
            'top_selling'      => $topSelling
        ]);
    }


    public function getBranches() 
    {
        return \App\Models\Branch::all();
    }
}