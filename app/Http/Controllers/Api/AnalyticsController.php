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

        // ✅ أضف حالة الإرجاع لكل فاتورة
        $saleIds = $data->pluck('id')->toArray();
        $refundsBySale = DB::table('refunds')
            ->whereIn('sale_id', $saleIds)
            ->select('sale_id', DB::raw('SUM(amount) as total_refunded'))
            ->groupBy('sale_id')
            ->get()
            ->keyBy('sale_id');

        $data = $data->map(function ($sale) use ($refundsBySale) {
            $refundedAmount = (float) ($refundsBySale->get($sale->id)->total_refunded ?? 0);
            $sale->total_refunded = $refundedAmount;
            $sale->has_refunds = $refundedAmount > 0;
            $sale->is_fully_refunded = $refundedAmount >= (float) $sale->total_amount;
            return $sale;
        });

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
        /* ═══════════════════════════════════════════════════════════
        المبيعات (Gross) — بدون خصم
        ═══════════════════════════════════════════════════════════ */
        $todaySalesQuery = DB::table('sales')->whereDate('created_at', today());
        $this->applyBranchFilter($todaySalesQuery);

        $todayProfitQuery = DB::table('sales')->whereDate('created_at', today());
        $this->applyBranchFilter($todayProfitQuery);

        $todayInvoicesQuery = DB::table('sales')->whereDate('created_at', today());
        $this->applyBranchFilter($todayInvoicesQuery);

        $avgInvoiceQuery = DB::table('sales')->whereDate('created_at', today());
        $this->applyBranchFilter($avgInvoiceQuery);

        $monthlySalesQuery = DB::table('sales')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
        $this->applyBranchFilter($monthlySalesQuery);

        $monthlyRevenueQuery = DB::table('sales')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);
        $this->applyBranchFilter($monthlyRevenueQuery);

        $weeklySalesQuery = DB::table('sales')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        $this->applyBranchFilter($weeklySalesQuery);

        $weeklyRevenueQuery = DB::table('sales')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        $this->applyBranchFilter($weeklyRevenueQuery);

        /* ═══════════════════════════════════════════════════════════
        ✅ ربح المرتجعات (للخصم من الأرباح)
        ═══════════════════════════════════════════════════════════ */

        // ربح مرتجعات اليوم
        $todayRefundProfitQuery = DB::table('refund_items')
            ->join('refunds', 'refund_items.refund_id', '=', 'refunds.id')
            ->join('sale_items', 'refund_items.sale_item_id', '=', 'sale_items.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('refunds.created_at', today())
            ->selectRaw('
                SUM(
                    sale_items.profit * refund_items.quantity 
                    / GREATEST(sale_items.quantity, 1)
                ) as refund_profit
            ');
        $this->applyBranchFilter($todayRefundProfitQuery, 'sales.branch_id');
        $todayRefundProfit = (float) ($todayRefundProfitQuery->value('refund_profit') ?? 0);

        // ربح مرتجعات الأسبوع
        $weeklyRefundProfitQuery = DB::table('refund_items')
            ->join('refunds', 'refund_items.refund_id', '=', 'refunds.id')
            ->join('sale_items', 'refund_items.sale_item_id', '=', 'sale_items.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('refunds.created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw('
                SUM(
                    sale_items.profit * refund_items.quantity 
                    / GREATEST(sale_items.quantity, 1)
                ) as refund_profit
            ');
        $this->applyBranchFilter($weeklyRefundProfitQuery, 'sales.branch_id');
        $weeklyRefundProfit = (float) ($weeklyRefundProfitQuery->value('refund_profit') ?? 0);

        // ربح مرتجعات الشهر
        $monthlyRefundProfitQuery = DB::table('refund_items')
            ->join('refunds', 'refund_items.refund_id', '=', 'refunds.id')
            ->join('sale_items', 'refund_items.sale_item_id', '=', 'sale_items.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereYear('refunds.created_at', now()->year)
            ->whereMonth('refunds.created_at', now()->month)
            ->selectRaw('
                SUM(
                    sale_items.profit * refund_items.quantity 
                    / GREATEST(sale_items.quantity, 1)
                ) as refund_profit
            ');
        $this->applyBranchFilter($monthlyRefundProfitQuery, 'sales.branch_id');
        $monthlyRefundProfit = (float) ($monthlyRefundProfitQuery->value('refund_profit') ?? 0);

        /* ═══════════════════════════════════════════════════════════
        ✅ مبلغ المرتجعات (للخصم من المبيعات الصافية)
        ═══════════════════════════════════════════════════════════ */

        $todayRefundAmountQuery = DB::table('refunds')
            ->join('sales', 'refunds.sale_id', '=', 'sales.id')
            ->whereDate('refunds.created_at', today());
        $this->applyBranchFilter($todayRefundAmountQuery, 'sales.branch_id');
        $todayRefundAmount = (float) ($todayRefundAmountQuery->sum('refunds.amount') ?? 0);

        $weeklyRefundAmountQuery = DB::table('refunds')
            ->join('sales', 'refunds.sale_id', '=', 'sales.id')
            ->whereBetween('refunds.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        $this->applyBranchFilter($weeklyRefundAmountQuery, 'sales.branch_id');
        $weeklyRefundAmount = (float) ($weeklyRefundAmountQuery->sum('refunds.amount') ?? 0);

        $monthlyRefundAmountQuery = DB::table('refunds')
            ->join('sales', 'refunds.sale_id', '=', 'sales.id')
            ->whereYear('refunds.created_at', now()->year)
            ->whereMonth('refunds.created_at', now()->month);
        $this->applyBranchFilter($monthlyRefundAmountQuery, 'sales.branch_id');
        $monthlyRefundAmount = (float) ($monthlyRefundAmountQuery->sum('refunds.amount') ?? 0);

        /* ═══════════════════════════════════════════════════════════
        الإرجاع
        ═══════════════════════════════════════════════════════════ */
        $grossTodaySales = (float) $todaySalesQuery->sum('total_amount');
        $grossWeeklySales = (float) $weeklySalesQuery->sum('total_amount');
        $grossMonthlySales = (float) $monthlySalesQuery->sum('total_amount');

        $grossTodayProfit = (float) $todayProfitQuery->sum('profit_amount');
        $grossWeeklyProfit = (float) $weeklyRevenueQuery->sum('profit_amount');
        $grossMonthlyProfit = (float) $monthlyRevenueQuery->sum('profit_amount');

        return [
            // ═══ المبيعات (Gross) ═══
            'today_sales'        => $grossTodaySales,
            'weekly_sales'       => $grossWeeklySales,
            'monthly_sales'      => $grossMonthlySales,

            // ═══ ✅ المبيعات الصافية (Net) ═══
            'today_net_sales'    => $grossTodaySales - $todayRefundAmount,
            'weekly_net_sales'   => $grossWeeklySales - $weeklyRefundAmount,
            'monthly_net_sales'  => $grossMonthlySales - $monthlyRefundAmount,

            // ═══ ✅ مبالغ المرتجعات ═══
            'today_refunds'      => $todayRefundAmount,
            'weekly_refunds'     => $weeklyRefundAmount,
            'monthly_refunds'    => $monthlyRefundAmount,

            // ═══ ✅ الأرباح (بعد خصم ربح المرتجعات) ═══
            'today_profit'       => $grossTodayProfit - $todayRefundProfit,
            'weekly_profit'      => $grossWeeklyProfit - $weeklyRefundProfit,
            'monthly_profit'     => $grossMonthlyProfit - $monthlyRefundProfit,
            'weekly_revenue'     => $grossWeeklyProfit - $weeklyRefundProfit,
            'monthly_revenue'    => $grossMonthlyProfit - $monthlyRefundProfit,

            // ═══ ✅ أرباح المرتجعات (للعرض) ═══
            'today_refunds_profit'   => $todayRefundProfit,
            'weekly_refunds_profit'  => $weeklyRefundProfit,
            'monthly_refunds_profit' => $monthlyRefundProfit,

            // ═══ الفواتير ═══
            'today_invoices'   => $todayInvoicesQuery->count(),
            'avg_invoice'      => $avgInvoiceQuery->avg('total_amount') ?? 0,

            // ═══ المخزون ═══
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
        /* ═══════════════════════════════════════════════════════════
        المبيعات والأرباح (Gross)
        ═══════════════════════════════════════════════════════════ */
        $salesQuery = DB::table('sales')
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month')
            ->selectRaw('SUM(total_amount) as sales')
            ->selectRaw('SUM(profit_amount) as gross_profit')
            ->whereYear('created_at', now()->year);

        $this->applyBranchFilter($salesQuery);

        $salesData = $salesQuery->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->get()
            ->keyBy('month');

        /* ═══════════════════════════════════════════════════════════
        ✅ ربح المرتجعات شهرياً
        ═══════════════════════════════════════════════════════════ */
        $refundQuery = DB::table('refund_items')
            ->join('refunds', 'refund_items.refund_id', '=', 'refunds.id')
            ->join('sale_items', 'refund_items.sale_item_id', '=', 'sale_items.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->selectRaw('EXTRACT(MONTH FROM refunds.created_at) as month')
            ->selectRaw('
                SUM(
                    sale_items.profit * refund_items.quantity 
                    / GREATEST(sale_items.quantity, 1)
                ) as refund_profit
            ')
            ->whereYear('refunds.created_at', now()->year);

        $this->applyBranchFilter($refundQuery, 'sales.branch_id');

        $refundData = $refundQuery->groupByRaw('EXTRACT(MONTH FROM refunds.created_at)')
            ->get()
            ->keyBy('month');

        /* ═══════════════════════════════════════════════════════════
        ✅ مبلغ المرتجعات شهرياً
        ═══════════════════════════════════════════════════════════ */
        $refundAmountQuery = DB::table('refunds')
            ->join('sales', 'refunds.sale_id', '=', 'sales.id')
            ->selectRaw('EXTRACT(MONTH FROM refunds.created_at) as month')
            ->selectRaw('SUM(refunds.amount) as refund_amount')
            ->whereYear('refunds.created_at', now()->year);

        $this->applyBranchFilter($refundAmountQuery, 'sales.branch_id');

        $refundAmountData = $refundAmountQuery->groupByRaw('EXTRACT(MONTH FROM refunds.created_at)')
            ->get()
            ->keyBy('month');

        /* ═══════════════════════════════════════════════════════════
        الدمج
        ═══════════════════════════════════════════════════════════ */
        $result = [];

        for ($m = 1; $m <= 12; $m++) {
            $saleRow = $salesData->get($m);
            if (!$saleRow) continue;

            $refundProfitRow = $refundData->get($m);
            $refundProfit = $refundProfitRow ? (float) $refundProfitRow->refund_profit : 0;

            $refundAmountRow = $refundAmountData->get($m);
            $refundAmount = $refundAmountRow ? (float) $refundAmountRow->refund_amount : 0;

            $grossSales = (float) $saleRow->sales;
            $grossProfit = (float) $saleRow->gross_profit;

            $result[] = (object) [
                'month'        => Carbon::create()->month($m)->locale('ar')->translatedFormat('F'),
                'sales'        => $grossSales,                             // Gross
                'net_sales'    => $grossSales - $refundAmount,             // ✅ Net
                'refunds'      => $refundAmount,                           // ✅ المرتجعات
                'profit'       => $grossProfit - $refundProfit,            // ✅ صافي الربح
                'gross_profit' => $grossProfit,                            // Gross Profit
            ];
        }

        return collect($result);
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
        /* ═══════════════════════════════════════════════════════════
        ✅ احسب الكميات المُرتجعة لكل بند
        ═══════════════════════════════════════════════════════════ */
        $refundedSub = DB::table('refund_items')
            ->select(
                'sale_item_id',
                DB::raw('SUM(quantity) as refunded_quantity')
            )
            ->groupBy('sale_item_id');

        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->leftJoinSub($refundedSub, 'refunded', function ($join) {
                $join->on('sale_items.id', '=', 'refunded.sale_item_id');
            })
            ->select(
                'medicines.name',
                DB::raw('
                    SUM(sale_items.quantity - COALESCE(refunded.refunded_quantity, 0)) qty
                ')
            );

        if ($this->branchId) {
            $query->where('sales.branch_id', $this->branchId);
        }

        return $query->groupBy('medicines.id', 'medicines.name')
            ->havingRaw('SUM(sale_items.quantity - COALESCE(refunded.refunded_quantity, 0)) > 0')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();
    }

    private function topProfitProducts()
    {
        /* ═══════════════════════════════════════════════════════════
        ✅ احسب الكميات المُرتجعة لكل بند
        ═══════════════════════════════════════════════════════════ */
        $refundedSub = DB::table('refund_items')
            ->select(
                'sale_item_id',
                DB::raw('SUM(quantity) as refunded_quantity')
            )
            ->groupBy('sale_item_id');

        $query = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->leftJoinSub($refundedSub, 'refunded', function ($join) {
                $join->on('sale_items.id', '=', 'refunded.sale_item_id');
            })
            ->select(
                'medicines.name',
                DB::raw('
                    SUM(
                        sale_items.profit 
                        * (sale_items.quantity - COALESCE(refunded.refunded_quantity, 0)) 
                        / GREATEST(sale_items.quantity, 1)
                    ) profit
                ')
            );

        if ($this->branchId) {
            $query->where('sales.branch_id', $this->branchId);
        }

        return $query->groupBy('medicines.id', 'medicines.name')
            ->havingRaw('
                SUM(
                    sale_items.profit 
                    * (sale_items.quantity - COALESCE(refunded.refunded_quantity, 0)) 
                    / GREATEST(sale_items.quantity, 1)
                ) > 0
            ')
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
        $analysisWindowDays = 30;

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

        // ✅ المخزون الحالي لكل دواء
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

        // ✅ جلب كل وحدات البيع لكل دواء (مع factor و name)
        $medicineIds = $medicines->pluck('id')->toArray();

        $unitsByMedicine = DB::table('medicine_units')
            ->join('units', 'medicine_units.unit_id', '=', 'units.id')
            ->whereIn('medicine_units.medicine_id', $medicineIds)
            ->where('medicine_units.allow_sale', true)
            ->select(
                'medicine_units.medicine_id',
                'medicine_units.factor',
                'medicine_units.is_base',
                'medicine_units.sort_order',
                'units.name as unit_name',
                'units.symbol as unit_symbol'
            )
            ->orderBy('medicine_units.medicine_id')
            ->orderByDesc('medicine_units.factor')
            ->get()
            ->groupBy('medicine_id');

        return $medicines->map(function ($item) use (
            $leadTimeDays,
            $targetCoverageDays,
            $safetyDays,
            $analysisWindowDays,
            $unitsByMedicine
        ) {
            $totalSold = (float) $item->total_sold;
            $currentStock = (float) $item->current_stock;

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
            } elseif ($avgDaily == 0 && $currentStock > 100) {
                // ✅ جديد: مخزون راكد (لا مبيعات + كمية كبيرة)
                $status = 'overstocked';
                $statusLabel = 'راكد — لا مبيعات';
            } elseif ($daysCover !== null && $daysCover > 365) {
                // ✅ جديد: مخزون مفرط (أكثر من سنة)
                $status = 'overstocked';
                $statusLabel = 'مخزون مفرط';
            } elseif ($daysCover !== null && $daysCover > 180) {
                // ✅ جديد: مخزون زائد (6 أشهر - سنة)
                $status = 'excess';
                $statusLabel = 'مخزون زائد';
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

            // ═══════════════════════════════════════════════════
            // ✅ أكبر وحدة بيع
            // ═══════════════════════════════════════════════════
            $units = $unitsByMedicine->get($item->id) ?? collect();

            // أكبر factor = أكبر وحدة بيع
            $largestUnit = $units->sortByDesc('factor')->first();

            $largestUnitName = $largestUnit?->unit_name ?? 'وحدة';
            $largestUnitFactor = (float) ($largestUnit?->factor ?? 1);

            // ✅ المخزون بأكبر وحدة
            $stockInLargestUnit = $largestUnitFactor > 0
                ? round($currentStock / $largestUnitFactor, 2)
                : $currentStock;

            // ✅ متوسط البيع اليومي بأكبر وحدة
            $avgDailyInLargestUnit = $largestUnitFactor > 0
                ? round($avgDaily / $largestUnitFactor, 2)
                : $avgDaily;

            // ✅ الطلب المقترح بأكبر وحدة (تقريب للأعلى)
            $suggestedOrderInLargestUnit = $largestUnitFactor > 0
                ? (int) ceil($suggestedOrder / $largestUnitFactor)
                : $suggestedOrder;
            // ✅ اقتراح إجراء
            $action = 'none';
            $actionLabel = '';

            if ($status === 'overstocked') {
                $action = 'do_not_order';
                $actionLabel = 'لا تطلب — راكد';
            } elseif ($status === 'excess') {
                $action = 'reduce_price';
                $actionLabel = 'فكّر في خصم';
            } elseif ($status === 'critical') {
                $action = 'urgent_order';
                $actionLabel = 'اطلب فوراً';
            } elseif ($status === 'warning') {
                $action = 'order_soon';
                $actionLabel = 'اطلب قريباً';
            }

            return [
                'name'                              => $item->name,
                'avg_daily_sales'                   => round($avgDaily, 2),
                'current_stock'                     => (int) round($currentStock),
                'days_cover'                        => $daysCover === null ? '∞' : $daysCover,
                'suggested_order'                   => $suggestedOrder,
                'status'                            => $status,
                'status_label'                      => $statusLabel,

                // ✅ جديد — العرض بأكبر وحدة
                'largest_unit_name'                 => $largestUnitName,
                'largest_unit_factor'               => $largestUnitFactor,
                'current_stock_in_largest_unit'     => $stockInLargestUnit,
                'avg_daily_in_largest_unit'         => $avgDailyInLargestUnit,
                'suggested_order_in_largest_unit'   => $suggestedOrderInLargestUnit,
                'action'        => $action,
                'action_label'  => $actionLabel,
            ];
        });
    }

    /* ============================================================
    ✅ تفاصيل رأس المال المجمد
    ============================================================ */
    public function frozenCapitalDetails(Request $request)
    {
        $branch = $request->get('branch_id');
        $this->branchId = ($branch && $branch !== 'all') ? (int) $branch : null;

        $perPage = min((int) $request->get('per_page', 20), 100);
        $page    = max(1, (int) $request->get('page', 1));

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

        $baseQuery = DB::table('medicine_batches')
            ->leftJoinSub($bestPriceSub, 'best_prices', function ($join) {
                $join->on('medicine_batches.id', '=', 'best_prices.batch_id')
                    ->where('best_prices.rn', '=', 1);
            })
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->leftJoin('branches', 'medicine_batches.branch_id', '=', 'branches.id')
            ->where('medicine_batches.remaining_quantity', '>', 0)
            ->whereNotExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('sale_items')
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereColumn('sale_items.medicine_batch_id', 'medicine_batches.id')
                    ->where('sales.created_at', '>=', now()->subDays(90));
            });

        $this->applyBranchFilter($baseQuery, 'medicine_batches.branch_id');

        // ✅ إجمالي العدد
        $total = (clone $baseQuery)->count();

        // ✅ إجمالي القيمة المجمدة (بحساب مباشر)
        $totalValue = (clone $baseQuery)
            ->selectRaw('
                SUM(
                    (medicine_batches.remaining_quantity / COALESCE(NULLIF(best_prices.factor, 0), 1))
                    * COALESCE(best_prices.buy_price, medicine_batches.buy_price)
                ) as total_frozen
            ')
            ->value('total_frozen') ?? 0;

        // ✅ جلب البيانات مع القيمة المحسوبة
        $data = (clone $baseQuery)
            ->select(
                'medicine_batches.id as batch_id',
                'medicine_batches.batch_number',
                'medicine_batches.expiry_date',
                'medicine_batches.remaining_quantity',
                'medicine_batches.buy_price as batch_buy_price',
                'medicines.id as medicine_id',
                'medicines.name as medicine_name',
                'branches.name as branch_name',
                DB::raw('COALESCE(best_prices.buy_price, medicine_batches.buy_price) as effective_buy_price'),
                DB::raw('COALESCE(NULLIF(best_prices.factor, 0), 1) as factor'),
                DB::raw('
                    (medicine_batches.remaining_quantity / COALESCE(NULLIF(best_prices.factor, 0), 1))
                    * COALESCE(best_prices.buy_price, medicine_batches.buy_price)
                    as frozen_value
                ')
            )
            ->orderByDesc('frozen_value')
            ->forPage($page, $perPage)
            ->get()
            ->map(function ($row) {
                $daysToExpiry = $row->expiry_date
                    ? now()->diffInDays(Carbon::parse($row->expiry_date), false)
                    : null;

                return [
                    'batch_id'         => $row->batch_id,
                    'batch_number'     => $row->batch_number,
                    'medicine_id'      => $row->medicine_id,
                    'medicine_name'    => $row->medicine_name,
                    'branch_name'      => $row->branch_name,
                    'expiry_date'      => $row->expiry_date,
                    'days_to_expiry'   => $daysToExpiry,
                    'remaining_qty'    => (float) $row->remaining_quantity,
                    'factor'           => (float) $row->factor,
                    'buy_price'        => (float) $row->effective_buy_price,
                    'frozen_value'     => round((float) $row->frozen_value, 2),
                ];
            });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
                'per_page'     => $perPage,
                'total'        => $total,
                'total_value'  => round((float) $totalValue, 2),
            ],
        ]);
    }

    /* ============================================================
    ✅ تفاصيل المخزون المنخفض
    ============================================================ */
    public function lowStockDetails(Request $request)
    {
        $branch = $request->get('branch_id');
        $this->branchId = ($branch && $branch !== 'all') ? (int) $branch : null;

        $query = DB::table('inventories')
            ->join('medicines', 'inventories.medicine_id', '=', 'medicines.id')
            ->leftJoin('branches', 'inventories.branch_id', '=', 'branches.id')
            ->whereColumn('inventories.quantity', '<=', 'inventories.minimum_quantity')
            ->where('inventories.quantity', '>', 0)
            ->select(
                'inventories.id',
                'inventories.quantity',
                'inventories.minimum_quantity',
                'inventories.maximum_quantity',
                'medicines.id as medicine_id',
                'medicines.name as medicine_name',
                'branches.name as branch_name',
                'inventories.branch_id'
            );

        $this->applyBranchFilter($query, 'inventories.branch_id');

        return response()->json([
            'data' => $query->orderBy('inventories.quantity')
                ->limit(200)
                ->get()
                ->map(fn($row) => [
                    'id'                => $row->id,
                    'medicine_id'       => $row->medicine_id,
                    'medicine_name'     => $row->medicine_name,
                    'branch_name'       => $row->branch_name,
                    'quantity'          => (int) $row->quantity,
                    'minimum_quantity'  => (int) $row->minimum_quantity,
                    'maximum_quantity'  => $row->maximum_quantity,
                    'deficit'           => max(0, (int) $row->minimum_quantity - (int) $row->quantity),
                    'reorder_qty'       => $row->maximum_quantity
                        ? max(0, (int) $row->maximum_quantity - (int) $row->quantity)
                        : max(0, (int) $row->minimum_quantity * 2 - (int) $row->quantity),
                ]),
        ]);
    }

    /* ============================================================
    ✅ تفاصيل قرب انتهاء الصلاحية
    ============================================================ */
    public function expiringDetails(Request $request)
    {
        $branch = $request->get('branch_id');
        $this->branchId = ($branch && $branch !== 'all') ? (int) $branch : null;

        $days = (int) $request->get('days', 60);

        $query = DB::table('medicine_batches')
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->leftJoin('branches', 'medicine_batches.branch_id', '=', 'branches.id')
            ->whereDate('medicine_batches.expiry_date', '<=', now()->addDays($days))
            ->where('medicine_batches.remaining_quantity', '>', 0)
            ->select(
                'medicine_batches.id as batch_id',
                'medicine_batches.batch_number',
                'medicine_batches.expiry_date',
                'medicine_batches.remaining_quantity',
                'medicine_batches.buy_price',
                'medicines.id as medicine_id',
                'medicines.name as medicine_name',
                'branches.name as branch_name'
            );

        $this->applyBranchFilter($query, 'medicine_batches.branch_id');

        return response()->json([
            'data' => $query->orderBy('medicine_batches.expiry_date')
                ->limit(200)
                ->get()
                ->map(function ($row) {
                    $days = now()->diffInDays(Carbon::parse($row->expiry_date), false);

                    return [
                        'batch_id'         => $row->batch_id,
                        'batch_number'     => $row->batch_number,
                        'medicine_id'      => $row->medicine_id,
                        'medicine_name'    => $row->medicine_name,
                        'branch_name'      => $row->branch_name,
                        'expiry_date'      => $row->expiry_date,
                        'days_to_expiry'   => $days,
                        'is_expired'       => $days < 0,
                        'remaining_qty'    => (float) $row->remaining_quantity,
                        'buy_price'        => (float) $row->buy_price,
                        'stock_value'      => round((float) $row->remaining_quantity * (float) $row->buy_price, 2),
                    ];
                }),
        ]);
    }

    /* ============================================================
    ✅ تفاصيل صحة المخزون
    ============================================================ */
    public function healthDetails(Request $request)
    {
        return response()->json([
            'low_stock'     => $this->inventoryMetrics()['low_stock'],
            'expiring_soon' => $this->inventoryMetrics()['expiring_soon'],
            'health_score'  => $this->inventoryMetrics()['health_score'],
            'breakdown' => [
                'low_stock_penalty'    => $this->inventoryMetrics()['low_stock'] * 2,
                'expiring_penalty'     => $this->inventoryMetrics()['expiring_soon'] * 2,
                'base_score'           => 100,
            ],
        ]);
    }
}
