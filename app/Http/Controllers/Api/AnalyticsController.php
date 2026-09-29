<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected ?int $branchId = null;

    public function dashboard(Request $request)
    {
        $branch = $request->get('branch_id');
        $this->branchId = ($branch && $branch !== 'all') ? (int) $branch : null;

        $recentSalesPage = (int) $request->get('recent_sales_page', 1);
        $recentSalesPerPage = (int) $request->get('recent_sales_per_page', 10);

        return response()->json([
            'kpis' => $this->kpis(),
            'sales_profit_chart' => $this->salesProfitChart(),
            'growth_chart'       => $this->growthChart(),
            'inventory'          => $this->inventoryMetrics(),
            'top_profit_products'=> $this->topProfitProducts(),
            'top_selling'        => $this->topSellingProducts(),
            'suppliers'          => $this->supplierAnalytics(),
            'forecast'           => $this->forecast(),
            'peak_hours'         => $this->peakHours(),
            'recent_sales'       => $this->recentSales(),
        ]);
    }

    private function recentSales(int $page = 1, int $perPage = 10)
    {
        $query = DB::table('sales');
        $this->applyBranchFilter($query);

        $total = (clone $query)->count();

        $data = $query->orderByDesc('created_at')
            ->forPage($page, $perPage)
            ->get([
                'id', 'total_amount', 'profit_amount',
                'payment_method', 'created_at',
            ]);

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
                'per_page'     => $perPage,
                'total'        => $total,
            ],
        ];
    }

    private function applyBranchFilter($query, $column = 'branch_id')
    {
        if ($this->branchId) {
            $query->where($column, $this->branchId);
        }
        return $query;
    }

    private function kpis()
    {
        // 1. Today's Sales
        $todaySalesQuery = DB::table('sales')->whereDate('created_at', today());
        $this->applyBranchFilter($todaySalesQuery);

        // 2. Today's Profit
        $todayProfitQuery = DB::table('sales')->whereDate('created_at', today());
        $this->applyBranchFilter($todayProfitQuery);

        // 3. Today's Invoices
        $todayInvoicesQuery = DB::table('sales')->whereDate('created_at', today());
        $this->applyBranchFilter($todayInvoicesQuery);

        // 4. Average Invoice
        $avgInvoiceQuery = DB::table('sales')->whereDate('created_at', today());
        $this->applyBranchFilter($avgInvoiceQuery);

        // 5. Monthly Sales (Current Month)
        $monthlySalesQuery = DB::table('sales')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
        $this->applyBranchFilter($monthlySalesQuery);

        // 6. Monthly Revenue / Profit (Current Month)
        $monthlyRevenueQuery = DB::table('sales')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
        $this->applyBranchFilter($monthlyRevenueQuery);

        // 7. Weekly Sales (Current Week, e.g., starting from Monday or past 7 days)
        $weeklySalesQuery = DB::table('sales')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        $this->applyBranchFilter($weeklySalesQuery);

        // Weekly Revenue / Profit (Current Week)
        $weeklyRevenueQuery = DB::table('sales')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        $this->applyBranchFilter($weeklyRevenueQuery);
        return [
            'today_sales'      => $todaySalesQuery->sum('total_amount'),
            'today_profit'     => $todayProfitQuery->sum('profit_amount'),
            'weekly_sales'     => $weeklySalesQuery->sum('total_amount'),     
            'weekly_revenue'   => $weeklyRevenueQuery->sum('profit_amount'),   
            'weekly_profit'    => $weeklyRevenueQuery->sum('profit_amount'),   
            'today_invoices'   => $todayInvoicesQuery->count(),
            'avg_invoice'      => $avgInvoiceQuery->avg('total_amount') ?? 0,
            'monthly_sales'    => $monthlySalesQuery->sum('total_amount'),
            'monthly_revenue'  => $monthlyRevenueQuery->sum('profit_amount'),
            'monthly_profit'   => $monthlyRevenueQuery->sum('profit_amount'),
            'inventory_value'  => $this->calculateInventoryValue(),
            'frozen_capital'   => $this->frozenCapital(),
        ];
    }

    private function calculateInventoryValue(): float
    {
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

        // ✅ لا leftJoin إضافي — هذا كان يُكرر الصفوف ويضاعف القيمة
        $query = DB::table('medicine_batches')
            ->leftJoinSub($bestPriceSub, 'best_prices', function ($join) {
                $join->on('medicine_batches.id', '=', 'best_prices.batch_id')
                    ->where('best_prices.rn', '=', 1);
            })
            ->where('medicine_batches.remaining_quantity', '>', 0);

        $this->applyBranchFilter($query, 'medicine_batches.branch_id');

        return (float) ($query->selectRaw('
            SUM(
                (medicine_batches.remaining_quantity / COALESCE(NULLIF(best_prices.factor, 0), 1))
                * COALESCE(best_prices.buy_price, medicine_batches.buy_price)
            ) as total
        ')->value('total') ?? 0);
    }

    private function salesProfitChart()
    {
        $query = DB::table('sales')
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month')
            ->selectRaw('SUM(total_amount) as sales')
            ->selectRaw('SUM(profit_amount) as profit')
            ->whereYear('created_at', now()->year);

        $this->applyBranchFilter($query);

        return $query->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->orderByRaw('EXTRACT(MONTH FROM created_at)')
            ->get()
            ->map(function ($row) {
                $row->month = Carbon::create()
                    ->month((int) $row->month)
                    ->locale('ar')
                    ->translatedFormat('F');

                return $row;
            });
    }

    private function growthChart()
    {
        $query = DB::table('sales')
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month')
            ->selectRaw('SUM(total_amount) as sales')
            ->whereYear('created_at', now()->year);

        $this->applyBranchFilter($query);

        $months = $query->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->orderByRaw('EXTRACT(MONTH FROM created_at)')
            ->get();
    
        $result = [];
    
        foreach ($months as $index => $month) {
            if ($index == 0) {
                $growth = 0;
            } else {
                $previous = $months[$index - 1]->sales;
    
                $growth = $previous > 0
                    ? (($month->sales - $previous) / $previous) * 100
                    : 0;
            }
    
            $result[] = [
                'month' => Carbon::create()
                    ->month((int) $month->month)
                    ->locale('ar')
                    ->translatedFormat('F'),
                'growth' => round($growth, 2)
            ];
        }
    
        return $result;
    }

    private function inventoryMetrics()
    {
        $lowStockQuery = DB::table('inventories')
            ->whereColumn('quantity', '<=', 'minimum_quantity')
            ->where('quantity', '>', 0);
        $this->applyBranchFilter($lowStockQuery, 'branch_id');

        $expiringQuery = DB::table('medicine_batches')
            ->whereDate('expiry_date', '<=', now()->addDays(60))
            ->where('remaining_quantity', '>', 0);
        $this->applyBranchFilter($expiringQuery, 'branch_id');

        return [
            'inventory_value' => $this->calculateInventoryValue(),
            'low_stock'       => $lowStockQuery->count(),
            'expiring_soon'   => $expiringQuery->count(),
            'health_score'    => $this->inventoryHealthScore()
        ];
    }

    private function inventoryHealthScore()
    {
        $lowStockQuery = DB::table('inventories')
            ->whereColumn('quantity', '<=', 'minimum_quantity')
            ->where('quantity', '>', 0);
        $this->applyBranchFilter($lowStockQuery, 'branch_id');
        $lowStock = $lowStockQuery->count();

        $expiringQuery = DB::table('medicine_batches')
            ->whereDate('expiry_date', '<=', now()->addDays(60))
            ->where('remaining_quantity', '>', 0);
        $this->applyBranchFilter($expiringQuery, 'branch_id');
        $expiring = $expiringQuery->count();

        $score = 100 - ($lowStock * 2) - ($expiring * 2);

        return max(0, $score);
    }

   private function topSellingProducts()
    {
        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->select(
                'medicines.name',
                DB::raw('SUM(sale_items.quantity) qty')
            );

        if ($this->branchId) {
            $query->where('sales.branch_id', $this->branchId);
        }

        return $query->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();
    }

    private function topProfitProducts()
    {
        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->select(
                'medicines.name',
                DB::raw('SUM(sale_items.profit) profit')
            );

        if ($this->branchId) {
            $query->where('sales.branch_id', $this->branchId);
        }

        return $query->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('profit')
            ->limit(10)
            ->get();
    }

    private function supplierAnalytics()
    {
        $query = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.name',
                DB::raw('SUM(purchase_items.subtotal) total')
            );

        $this->applyBranchFilter($query, 'purchases.branch_id');

        return $query->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total')
            ->get();
    }

    private function peakHours()
    {
        $query = DB::table('sales')
            ->selectRaw('EXTRACT(HOUR FROM created_at) as hour')
            ->selectRaw('COUNT(*) as invoices');

        $this->applyBranchFilter($query);

        return $query->groupByRaw('EXTRACT(HOUR FROM created_at)')
            ->orderByRaw('EXTRACT(HOUR FROM created_at)')
            ->get()
            ->map(function ($row) {
                $row->hour = Carbon::createFromTime(
                    (int) $row->hour,
                    0
                )->format('H:i');

                return $row;
            });
    }

 

    private function frozenCapital()
    {
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

        $query = DB::table('medicine_batches')
            ->leftJoinSub($bestPriceSub, 'best_prices', function ($join) {
                $join->on('medicine_batches.id', '=', 'best_prices.batch_id')
                    ->where('best_prices.rn', '=', 1);
            })
            
            ->where('medicine_batches.remaining_quantity', '>', 0)
            ->whereNotExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereColumn('sale_items.medicine_batch_id', 'medicine_batches.id')
                    ->where('sales.created_at', '>=', now()->subDays(90));
            });

        $this->applyBranchFilter($query, 'medicine_batches.branch_id');

        return $query->selectRaw('SUM((medicine_batches.remaining_quantity / COALESCE(NULLIF(best_prices.factor, 0), 1)) * COALESCE(best_prices.buy_price, medicine_batches.buy_price)) as total')
            ->value('total') ?? 0;
    }

    private function forecast()
    {
        $leadTimeDays = 5;
        $safetyDays = 3;
        $targetCoverageDays = 30;
        $analysisWindowDays = 30; // ✅ نافذة ثابتة

        // ✅ إجمالي المبيعات في آخر 30 يوماً لكل دواء
        $salesSub = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->where('sales.created_at', '>=', now()->subDays($analysisWindowDays))
            ->when($this->branchId, fn($q) => $q->where('sales.branch_id', $this->branchId))
            ->select(
                'medicine_batches.medicine_id',
                DB::raw("SUM(sale_items.quantity_base) as total_sold")
            )
            ->groupBy('medicine_batches.medicine_id');

        // ✅ المخزون الحالي لكل دواء (بالمقارنة على الوحدة الأساسية = piece)
        $stockSub = DB::table('medicine_batches')
            ->where('remaining_quantity', '>', 0)
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->select(
                'medicine_id',
                DB::raw("SUM(remaining_quantity) as current_stock")
            )
            ->groupBy('medicine_id');

        $medicines = DB::table('medicines')
            ->leftJoinSub($salesSub, 'sales_summary', fn($j) => $j->on('medicines.id', '=', 'sales_summary.medicine_id'))
            ->leftJoinSub($stockSub, 'stock_summary', fn($j) => $j->on('medicines.id', '=', 'stock_summary.medicine_id'))
            ->select(
                'medicines.id',
                'medicines.name',
                DB::raw('COALESCE(sales_summary.total_sold, 0) as total_sold'),
                DB::raw('COALESCE(stock_summary.current_stock, 0) as current_stock')
            )
            ->get();

        return $medicines->map(function ($item) use ($leadTimeDays, $targetCoverageDays, $safetyDays, $analysisWindowDays) {
            $totalSold = (float) $item->total_sold;
            $currentStock = (float) $item->current_stock;

            // ✅ متوسط البيع اليومي = إجمالي آخر 30 يوم ÷ 30
            $avgDaily = $totalSold / $analysisWindowDays;

            $reorderPoint = ($leadTimeDays + $safetyDays) * $avgDaily;
            $daysCover = $avgDaily > 0 ? round($currentStock / $avgDaily, 1) : null;

            $suggestedOrder = 0;
            if ($avgDaily > 0 && $currentStock <= ($avgDaily * $targetCoverageDays)) {
                $targetStock = $avgDaily * $targetCoverageDays;
                $suggestedOrder = max(0, (int) ceil($targetStock - $currentStock));
            }

            if ($currentStock <= 0) {
                $status = 'critical';
                $statusLabel = 'نفذت الكمية';
            } elseif ($avgDaily > 0 && $currentStock <= $reorderPoint) {
                $status = 'warning';
                $statusLabel = 'طلب شراء';
            } elseif ($avgDaily == 0 && $currentStock < 10) {
                $status = 'warning';
                $statusLabel = 'مخزون منخفض (لا مبيعات)';
            } else {
                $status = 'normal';
                $statusLabel = 'كافٍ';
            }

            return [
                'name'            => $item->name,
                'avg_daily_sales' => round($avgDaily, 2),
                'current_stock'   => (int) round($currentStock),
                'days_cover'      => $daysCover === null ? '∞' : $daysCover,
                'suggested_order' => $suggestedOrder,
                'status'          => $status,
                'status_label'    => $statusLabel,
            ];
        });
    }
}