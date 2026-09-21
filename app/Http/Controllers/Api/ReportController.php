<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\Purchase;
use App\Models\SaleItem;
use App\Models\Debt;
use App\Models\InventoryMovement;
use App\Models\ShiftActivity;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Refund;
use App\Models\User;
use App\Models\RefundItem;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * محرك البحث الشامل في الصيدلية
     */
    public function search(Request $request)
    {
        $query = $request->query('q');
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // 1. البحث في الأدوية (الأولوية الأولى)
        $medicines = Medicine::where('name', 'LIKE', "%{$query}%")
            // ->orWhere('barcode', 'LIKE', "%{$query}%")
            ->with(['category', 'units'])
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'دواء',
                    'id' => $item->id,
                    'name' => $item->name,
                    'sub' => $item->barcode ?? 'باركود: غير موجود',
                    'url' => '#', // رابط للانتقال (يمكن ربطه لاحقاً)
                    'icon' => 'fa-pills',
                    'color' => '#3498db'
                ];
            });

        // 2. البحث في فواتير البيع
        $sales = Sale::where('id', 'LIKE', "%{$query}%")
            ->orWhere('total_amount', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'فاتورة بيع',
                    'id' => $item->id,
                    'name' => 'فاتورة #' . $item->id,
                    'sub' => 'المبلغ: ' . number_format($item->total_amount, 2) . ' ج.س',
                    'url' => '#',
                    'icon' => 'fa-receipt',
                    'color' => '#2ecc71'
                ];
            });

        // 3. البحث في فواتير الشراء
        $purchases = Purchase::where('id', 'LIKE', "%{$query}%")
            ->orWhere('invoice_number', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'فاتورة شراء',
                    'id' => $item->id,
                    'name' => 'شراء #' . $item->id,
                    'sub' => 'رقم الفاتورة: ' . ($item->invoice_number ?? 'بدون'),
                    'url' => '#',
                    'icon' => 'fa-shopping-cart',
                    'color' => '#f39c12'
                ];
            });

        // 4. البحث في الموردين
        $suppliers = Supplier::where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'مورد',
                    'id' => $item->id,
                    'name' => $item->name,
                    'sub' => $item->phone ?? 'لا يوجد هاتف',
                    'url' => '#',
                    'icon' => 'fa-truck',
                    'color' => '#9b59b6'
                ];
            });

        // 5. البحث في الدفعات (LOT)
        $batches = MedicineBatch::where('batch_number', 'LIKE', "%{$query}%")
            ->with('medicine')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'دفعة (LOT)',
                    'id' => $item->id,
                    'name' => 'LOT: ' . $item->batch_number,
                    'sub' => $item->medicine->name ?? 'غير معروف' . ' | يتبقى: ' . $item->remaining_quantity,
                    'url' => '#',
                    'icon' => 'fa-boxes',
                    'color' => '#e67e22'
                ];
            });
        // 6. التصنيفات
        $categories = Category::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type'  => 'تصنيف',
                'id'    => $item->id,
                'name'  => $item->name,
                'sub'   => 'تصنيف أدوية',
                'icon'  => 'fa-tags',
                'color' => '#d97706',
            ]);

        // 7. المستخدمون
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'type'  => 'مستخدم',
                'id'    => $item->id,
                'name'  => $item->name,
                'sub'   => $item->email . ' • ' . ($item->role === 'admin' ? 'مدير' : 'صيدلي'),
                'icon'  => 'fa-user',
                'color' => '#3b82f6',
            ]);
        // دمج النتائج وترتيبها (الأدوية أولاً)
        $results = array_merge(
            $medicines->toArray(),
            $sales->toArray(),
            $purchases->toArray(),
            $suppliers->toArray(),
            $batches->toArray(),
            $categories->toArray(),   // ✅
            $users->toArray() 
        );

        return response()->json($results);
    }
    /**
     * الحصول على جميع بيانات لوحة التحكم الرئيسية
     */
    public function getDashboardData(Request $request)
    {
        $branchId = $request->query('branch_id');

        // 1. إجمالي المبيعات اليومية
        $dailySales = Sale::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', today())
            ->sum('total_amount');

        // 2. عدد فواتير اليوم
        $invoiceCount = Sale::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', today())
            ->count();

        // 3. عدد الأدوية منخفضة المخزون (الكمية <= الحد الأدنى)
        $lowStockItems = Inventory::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->whereColumn('quantity', '<=', 'minimum_quantity')
            ->count();

        // 4. عدد الأدوية منتهية الصلاحية (دفعات منتهية الصلاحية)
        $expiredCount = MedicineBatch::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->where('expiry_date', '<', today())
            ->count();

        // 5. إجمالي عدد الأدوية الفريدة
        $totalItems = Medicine::count();

        // 6. أرباح اليوم
        $profitToday = Sale::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', today())
            ->sum('profit_amount');

        // 7. عدد المشتريات اليوم
        $shipmentsCount = Purchase::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', today())
            ->count();

        // 8. المبيعات الأسبوعية (للرسم البياني)
        $weeklySales = collect(range(6, 0))->map(function($i) use ($branchId) {
            $date = Carbon::today()->subDays($i);
            $total = Sale::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            return [
                'day' => $date->locale('ar')->isoFormat('dd'),
                'value' => $total > 0 ? $total / 100 : 0,
                'label_value' => $total
            ];
        });

        // 9. أعلى 5 أدوية مبيعاً
        $topMedicines = SaleItem::select(
                'medicines.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity')
            )
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('sales.branch_id', $branchId))
            ->groupBy('medicines.id', 'medicines.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name' => $item->name,
                'quantity' => $item->total_quantity,
                'status' => 'متوفر' // يمكن تحديثه لاحقاً
            ]);

        // 10. التنبيهات (بأسماء الأدوية)
        $alerts = [];

        // الأدوية منخفضة المخزون
        $lowStockMedicines = Inventory::with('medicine')
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->whereColumn('quantity', '<=', 'minimum_quantity')
            ->get();
        foreach ($lowStockMedicines as $item) {
            $alerts[] = [
                'id' => 'low_' . $item->id,
                'title' => $item->medicine->name . ' مخزون منخفض',
                'desc' => 'الكمية: ' . $item->quantity . '، الحد الأدنى: ' . $item->minimum_quantity,
                'color' => '#f1c40f',
                'severity' => 'انتباه'
            ];
        }

        // الأدوية منتهية الصلاحية
        $expiredBatches = MedicineBatch::with('medicine')
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->where('expiry_date', '<', today())
            ->get();
        foreach ($expiredBatches as $batch) {
            $alerts[] = [
                'id' => 'exp_' . $batch->id,
                'title' => $batch->medicine->name . ' منتهي الصلاحية',
                'desc' => 'تاريخ الصلاحية: ' . $batch->expiry_date,
                'color' => '#e74c3c',
                'severity' => 'عاجل'
            ];
        }

        // الأدوية التي ستنتهي صلاحيتها خلال شهر
        $expiringSoon = MedicineBatch::with('medicine')
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', today()->addMonth())
            ->get();
        foreach ($expiringSoon as $batch) {
            $alerts[] = [
                'id' => 'soon_' . $batch->id,
                'title' => $batch->medicine->name . ' سينتهي صلاحيته بعد ' . now()->diffInDays($batch->expiry_date) . ' يوم',
                'desc' => 'تاريخ الصلاحية: ' . $batch->expiry_date,
                'color' => '#f39c12',
                'severity' => 'تنبيه'
            ];
        }

        // 11. الأنشطة الأخيرة
        $recentActivities = InventoryMovement::with('medicine')

            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($movement) => [
                'id' => $movement->id,
                'title' => $movement->type == 'sale' ? 'بيع ' . $movement->quantity . ' من ' . ($movement->medicine->name ?? 'دواء') : 'حركة مخزون',
                'desc' => $movement->notes ?? '',
                'time' => $movement->created_at->diffForHumans(),
                'color' => $movement->type == 'sale' ? '#3498db' : '#2ecc71',
                'icon' => $movement->type == 'sale' ? 'fas fa-shopping-cart' : 'fas fa-plus-circle'
            ]);
        // تقدير قيمة المخزون (مجموع الكميات × متوسط سعر الشراء)
        // قيمة المخزون الحالي
        $inventoryValue = $this->calculateInventoryValue($branchId);

        // أعلى 5 أدوية ربحية
        $topProfit = SaleItem::select(
                'medicines.name',
                DB::raw('SUM((sale_items.price - medicine_batches.buy_price) * sale_items.quantity) as total_profit')
            )
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('sales.branch_id', $branchId))
            ->groupBy('medicines.id', 'medicines.name')
            ->orderBy('total_profit', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name' => $item->name,
                'profit' => (float) $item->total_profit,
        ]);

        // ============================================================
        // توزيع المبيعات حسب التصنيفات (بناءً على المبيعات الفعلية)
        // ============================================================

        // ============================================================
        // 4. توزيع المبيعات حسب التصنيفات (بيانات حقيقية)
        // ============================================================

        $distribution = Category::select(
                'categories.id',
                'categories.name',
                DB::raw('COALESCE(SUM(sale_items.price * sale_items.quantity), 0) as total_sales')
            )
            ->leftJoin('medicines', 'medicines.category_id', '=', 'categories.id')
            ->leftJoin('medicine_batches', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->leftJoin('sale_items', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->leftJoin('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->when($branchId && $branchId !== 'all', function($q) use ($branchId) {
                return $q->where('sales.branch_id', $branchId);
            })
            ->groupBy('categories.id', 'categories.name')
            ->havingRaw('COALESCE(SUM(sale_items.price * sale_items.quantity), 0) > 0')  // ✅
            ->orderBy('total_sales', 'desc')
            ->get();
            // 12. فواتير اليوم (بالتفصيل)
            $todayInvoices = Sale::when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
                ->whereDate('created_at', today())
                ->latest()
                ->limit(30)
                ->get()
                ->map(function ($sale) {
                    $totalRefunded = Refund::where('sale_id', $sale->id)->sum('amount');
                    return [
                        'id'             => $sale->id,
                        'total_amount'   => (float) $sale->total_amount,
                        'payment_method' => $sale->payment_method ?: 'cash',
                        'bank_name'      => $sale->bank_name,
                        'bank_reference' => $sale->bank_reference,
                        'bank_notes'     => $sale->bank_notes,
                        'created_at'     => $sale->created_at?->format('H:i'),
                        'is_refunded'    => $totalRefunded >= $sale->total_amount,
                        'total_refunded' => (float) $totalRefunded,
                        
                    ];
                });
            return response()->json([
            'daily_sales'       => $dailySales,
            'invoice_count'     => $invoiceCount,
            'low_stock_items'   => $lowStockItems,
            'expired_count'     => $expiredCount,
            'total_items'       => $totalItems,
            'profit_today'      => $profitToday,
            'shipments_count'   => $shipmentsCount,
            'weekly_sales'      => $weeklySales,
            'top_medicines'     => $topMedicines,
            'alerts'            => $alerts,
            'recent_activities' => $recentActivities,
            'inventory_value' => $inventoryValue,
            'top_profit' => $topProfit,
            'distribution' => $distribution,
            'today_invoices' => $todayInvoices,
             
        ]);
    }

    /**
     * حساب خطة الشراء المقترحة بناءً على متوسط الاستهلاك اليومي
     */
    private function calculatePurchasePlan($branchId)
    {
        // جلب جميع الأدوية التي لها مبيعات خلال الـ 30 يوم الماضية
        $medicines = Medicine::whereHas('batches', function($query) use ($branchId) {
            if ($branchId && $branchId !== 'all') {
                $query->where('branch_id', $branchId);
            }
        })->with(['batches' => function($query) use ($branchId) {
            if ($branchId && $branchId !== 'all') {
                $query->where('branch_id', $branchId);
            }
        }])->get();

        $plan = [];

        foreach ($medicines as $medicine) {
            // حساب متوسط المبيعات اليومية لآخر 30 يوم
            $dailyAvg = SaleItem::whereHas('sale', function($q) use ($branchId) {
                    if ($branchId && $branchId !== 'all') {
                        $q->where('branch_id', $branchId);
                    }
                })
                ->whereHas('batch', function($q) use ($medicine) {
                    $q->where('medicine_id', $medicine->id);
                })
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->avg('quantity');

            $dailyAvg = $dailyAvg ?: 0;

            // إجمالي المخزون الحالي
            $currentStock = $medicine->batches->sum('remaining_quantity');

            // عدد الأيام التي يغطيها المخزون الحالي
            $daysCover = $dailyAvg > 0 ? floor($currentStock / $dailyAvg) : 999;

            // الكمية المقترحة للطلب (لتغطية 15 يوم إضافية)
            $suggestedOrder = max(0, ($dailyAvg * 15) - $currentStock);

            $plan[] = [
                'name' => $medicine->name,
                'avg_daily_sales' => round($dailyAvg, 2),
                'current_stock' => $currentStock,
                'days_cover' => $daysCover,
                'suggested_order' => ceil($suggestedOrder),
                'status' => $daysCover < 5 ? 'critical' : ($daysCover < 15 ? 'warning' : 'normal'),
                'status_label' => $daysCover < 5 ? 'عاجل' : ($daysCover < 15 ? 'انتباه' : 'متوفر'),
            ];
        }

        // ترتيب حسب الحاجة (الأكثر إلحاحاً أولاً)
        usort($plan, function($a, $b) {
            return $a['days_cover'] <=> $b['days_cover'];
        });

        return array_slice($plan, 0, 10); // عرض أول 10 عناصر فقط
    }

    /**
     * حساب إجمالي قيمة المخزون الحالي
     */
    private function calculateInventoryValue($branchId = null)
    {
        // 1. نأخذ أفضل سعر شراء لكل دفعة (على أساس أقل وحدة)
        $bestPriceSub = DB::table('medicine_prices')
            ->join('medicine_units', function ($join) {
                $join->on('medicine_prices.medicine_id', '=', 'medicine_units.medicine_id')
                    ->on('medicine_prices.unit_id', '=', 'medicine_units.unit_id');
            })
            ->where('medicine_prices.is_active', true)
            ->select(
                'medicine_prices.batch_id',
                'medicine_prices.buy_price',
                'medicine_units.factor',
                DB::raw('ROW_NUMBER() OVER(PARTITION BY medicine_prices.batch_id ORDER BY medicine_units.factor DESC) as rn')
            );

        // 2. نربطها مع الدفعات ونحسب القيمة
        $query = DB::table('medicine_batches')
            ->leftJoinSub($bestPriceSub, 'best_prices', function ($join) {
                $join->on('medicine_batches.id', '=', 'best_prices.batch_id')
                    ->where('best_prices.rn', '=', 1);
            })
            ->where('medicine_batches.remaining_quantity', '>', 0);

        // تطبيق فلتر الفرع إن وجد
        if ($branchId && $branchId !== 'all') {
            $query->where('medicine_batches.branch_id', $branchId);
        }

        // حساب القيمة الإجمالية
        return $query->selectRaw('
            SUM(
                (medicine_batches.remaining_quantity / COALESCE(NULLIF(best_prices.factor, 0), 1))
                * COALESCE(best_prices.buy_price, medicine_batches.buy_price)
            ) as total
        ')->value('total') ?? 0;
    }

    // 1. إجمالي المبيعات والأرباح خلال فترة
    public function salesReport(Request $request)
    {
        // Find the currently authenticated user's active open shift
        $activeShift = Shift::where('user_id', auth()->id())
                                ->where('status', 'open')
                                ->first();

            if (!$activeShift) {
                return response()->json([
                    'message' => 'No active shift found for the current user.',
                    'data' => []
                ], 403);
            }

            // Query sales restricted exclusively to the active shift ID
            $sales = Sale::where('shift_id', $activeShift->id)
                        ->with('items.batch', 'items.unit')
                        ->latest()
                        ->paginate(20);

            return response()->json($sales);
    }

    public function getRecentSales(Request $request)
    {
        $branchId = $request->query('branch_id');

        $sales = Sale::query()
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->take(8)
            ->get()
            ->map(function($sale) {
                $totalRefunded = Refund::where('sale_id', $sale->id)->sum('amount');
                return [
                    'id' => $sale->id,
                    'total_amount' => $sale->total_amount,
                    'payment_method' => $sale->payment_method,
                    'created_at' => $sale->created_at,
                    'is_refunded' => $totalRefunded >= $sale->total_amount,
                    'total_refunded' => $totalRefunded,
                ];
            });

        return response()->json(['recent' => $sales]);
    }

    public function getAnalyticsData(Request $request) 
    {
        $branchId = $request->query('branch_id');

        // 1. جلب إجمالي المبيعات لآخر 7 أيام
        $dates = collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('Y-m-d'));
        $totals = $dates->map(function($date) use ($branchId) {
            return Sale::query()
                ->when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
                ->whereDate('created_at', $date)
                ->sum('total_amount');
        });

        // 2. جلب أكثر الأدوية ربحية
        $topMedicines = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('sales.branch_id', $branchId))
            ->select(
                'medicines.name as name',
                DB::raw('SUM((sale_items.price - medicine_batches.cost_price) * sale_items.quantity) as profit')
            )
            ->groupBy('medicines.id', 'medicines.name')
            ->orderBy('profit', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'dates' => $dates, 
            'totals' => $totals, 
            'top_medicines' => $topMedicines
        ]);
    }

    public function getSaleDetails($id)
    {
        $sale = Sale::with([
            'items.batch.medicine',
            
            'user',
            'shift',
        ])->find($id);

        if (!$sale) {
            return response()->json(['message' => 'الفاتورة غير موجودة'], 404);
        }

        // إجمالي المرتجعات
        $totalRefunded = (float) Refund::where('sale_id', $id)->sum('amount');

        // المرتجعات لكل صنف
        $sale->items->each(function ($item) {
            $refundedQty = (int) RefundItem::where('sale_item_id', $item->id)->sum('quantity');
            $item->refunded_quantity = $refundedQty;
            $item->remaining_quantity_for_refund = max(0, (int) $item->quantity - $refundedQty);
        });

        return response()->json([
            'id'              => $sale->id,
            'created_at'      => $sale->created_at?->toIso8601String(),
            'total_amount'    => (float) $sale->total_amount,
            'profit_amount'   => (float) ($sale->profit_amount ?? 0),
            'payment_method'  => $sale->payment_method ?? 'cash',
            'bank_name'       => $sale->bank_name,
            'bank_reference'  => $sale->bank_reference,
            'bank_transfer_date' => $sale->bank_transfer_date,
            'bank_notes'      => $sale->bank_notes,
            'branch_id'       => $sale->branch_id,
            'shift_id'        => $sale->shift_id,
            'user'            => $sale->user ? [
                'id'   => $sale->user->id,
                'name' => $sale->user->name,
            ] : null,
            'shift'           => $sale->shift ? [
                'id'     => $sale->shift->id,
                'status' => $sale->shift->status,
            ] : null,
            'total_refunded'  => $totalRefunded,
            'items'           => $sale->items->map(function ($item) {
                return [
                    'id'                   => $item->id,
                    'medicine_batch_id'    => $item->medicine_batch_id,
                    'quantity'             => (int) $item->quantity,
                    'quantity_base'        => (int) ($item->quantity_base ?? 0),
                    'unit'                 => $item->unit,
                    'price'                => (float) $item->price,
                    'profit'               => (float) ($item->profit ?? 0),
                    'refunded_quantity'    => $item->refunded_quantity,
                    'remaining_quantity_for_refund' => $item->remaining_quantity_for_refund,
                    'batch'                => $item->batch ? [
                        'id'            => $item->batch->id,
                        'batch_number'  => $item->batch->batch_number,
                        'expiry_date'   => $item->batch->expiry_date,
                        'medicine'      => $item->batch->medicine ? [
                            'id'              => $item->batch->medicine->id,
                            'name'            => $item->batch->medicine->name,
                            'scientific_name' => $item->batch->medicine->scientific_name,
                        ] : null,
                    ] : null,
                    
                ];
            }),
        ]);
    }


}
