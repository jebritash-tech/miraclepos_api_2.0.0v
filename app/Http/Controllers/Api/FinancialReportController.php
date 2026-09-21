<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Sale, Refund, Expense, Withdrawal, Purchase, Salary, Branch};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    /* ============================================================
       GET /api/financial-reports
       params:
         - period = daily|weekly|monthly|yearly
         - from, to (dates) — للتقرير المخصص
         - branch_id = all | {id}
       ============================================================ */
    public function index(Request $request)
    {
        $period   = $request->input('period', 'daily');
        $branchId = $request->input('branch_id', 'all');

        $range = $this->resolveRange($period, $request);

        $sales     = $this->salesSummary($range, $branchId);
        $refunds   = $this->refundsSummary($range, $branchId);
        $expenses  = $this->expensesSummary($range, $branchId);
        $withdraws = $this->withdrawalsSummary($range, $branchId);
        $purchases = $this->purchasesSummary($range, $branchId);
        $debtsPaid = $this->debtsPaidSummary($range, $branchId);
        $salaries  = $this->salariesSummary($range, $branchId);

        // ✅ معلومات الفرع للعرض
        $branchInfo = ['id' => null, 'name' => 'كل الفروع'];
        if ($branchId && $branchId !== 'all') {
            $b = Branch::find($branchId);
            if ($b) {
                $branchInfo = ['id' => $b->id, 'name' => $b->name];
            }
        }

        return response()->json([
            'period' => [
                'type'  => $period,
                'label' => $this->periodLabel($period),
                'from'  => $range['from']->toDateString(),
                'to'    => $range['to']->toDateString(),
            ],
            'branch' => $branchInfo,
            'summary' => [
                'total_sales'       => $sales['total'],
                'total_profit'      => $sales['profit'],
                'total_refunds'     => $refunds['total'],
                'total_expenses'    => $expenses['total'],
                'total_withdrawals' => $withdraws['total'],
                'total_purchases'   => $purchases['total'],
                'total_debts_paid'  => $debtsPaid['total'],
                'total_salaries'    => $salaries['total'],
                'net_revenue'       => $sales['total'] - $refunds['total']
                                       - $expenses['total'] - $withdraws['total']
                                       - $salaries['total'],
                'net_profit'        => $sales['profit'] - $refunds['total']
                                       - $expenses['total'] - $withdraws['total']
                                       - $salaries['total'],
            ],
            'breakdown' => [
                'sales_by_payment' => $sales['by_payment'],
                'sales_by_day'     => $sales['by_day'],
                'expenses_by_day'  => $expenses['by_day'],
                'top_medicines'    => $sales['top_medicines'],
            ],
            'details' => [
                'expenses_list'    => $expenses['list'],
                'withdrawals_list' => $withdraws['list'],
                'purchases_list'   => $purchases['list'],
            ],
        ]);
    }

    /* ============================================================
       Range & Label
       ============================================================ */
    protected function resolveRange(string $period, Request $request): array
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        if ($from && $to) {
            return [
                'from' => Carbon::parse($from)->startOfDay(),
                'to'   => Carbon::parse($to)->endOfDay(),
            ];
        }

        return match ($period) {
            'daily'   => ['from' => now()->startOfDay(),  'to' => now()->endOfDay()],
            'weekly'  => ['from' => now()->startOfWeek(), 'to' => now()->endOfWeek()],
            'monthly' => ['from' => now()->startOfMonth(),'to' => now()->endOfMonth()],
            'yearly'  => ['from' => now()->startOfYear(), 'to' => now()->endOfYear()],
            default   => ['from' => now()->startOfDay(),  'to' => now()->endOfDay()],
        };
    }

    protected function periodLabel(string $period): string
    {
        return match ($period) {
            'daily'   => 'التقرير اليومي',
            'weekly'  => 'التقرير الأسبوعي',
            'monthly' => 'التقرير الشهري',
            'yearly'  => 'التقرير السنوي',
            default   => 'تقرير مخصص',
        };
    }

    /* ============================================================
       Helper — تطبيق فلتر الفرع
       ============================================================ */
    protected function applyBranch($query, $branchId, string $column = 'branch_id')
    {
        if ($branchId && $branchId !== 'all') {
            $query->where($column, $branchId);
        }
        return $query;
    }

    /* ============================================================
       1) المبيعات
       ============================================================ */
    protected function salesSummary(array $range, $branchId): array
    {
        $q = Sale::whereBetween('created_at', [$range['from'], $range['to']]);
        $this->applyBranch($q, $branchId);

        $total  = (float) (clone $q)->sum('total_amount');
        $profit = (float) (clone $q)->sum('profit_amount');
        $count  = (clone $q)->count();

        $byPayment = (clone $q)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        $byDay = (clone $q)
            ->selectRaw('DATE(created_at) as day, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $topMedicines = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('medicine_batches', 'sale_items.medicine_batch_id', '=', 'medicine_batches.id')
            ->join('medicines', 'medicine_batches.medicine_id', '=', 'medicines.id')
            ->whereBetween('sales.created_at', [$range['from'], $range['to']])
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('sales.branch_id', $branchId))
            ->selectRaw('medicines.name, SUM(sale_items.quantity) as quantity, SUM(sale_items.price * sale_items.quantity) as revenue')
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return [
            'total'  => $total,
            'profit' => $profit,
            'count'  => $count,
            'by_payment' => $byPayment,
            'by_day' => $byDay,
            'top_medicines' => $topMedicines,
        ];
    }

    /* ============================================================
       2) المرتجعات
       ============================================================ */
    protected function refundsSummary(array $range, $branchId): array
    {
        $q = Refund::whereBetween('created_at', [$range['from'], $range['to']]);

        if ($branchId && $branchId !== 'all') {
            $q->whereHas('sale', fn($s) => $s->where('branch_id', $branchId));
        }

        return [
            'total' => (float) $q->sum('amount'),
            'count' => $q->count(),
        ];
    }
    /* ============================================================
       3) المصروفات — مع حالة السداد
       ============================================================
       كل المصروفات تُعتبر "مدفوعة" لأنها:
         - مصروفات ورديات → تُخصم فوراً من الدرج
         - مصروفات إدارية → عمليات تشغيلية مدفوعة
       ============================================================ */
    protected function expensesSummary(array $range, $branchId): array
    {
        $q = Expense::whereBetween('created_at', [$range['from'], $range['to']]);

        if ($branchId && $branchId !== 'all') {
            $q->where(function ($sub) use ($branchId) {
                $sub->whereHas('shift', fn($s) => $s->where('branch_id', $branchId))
                    ->orWhere(function ($q2) use ($branchId) {
                        $q2->whereNull('shift_id')
                           ->whereHas('user', fn($u) => $u->where('branch_id', $branchId));
                    });
            });
        }

        $list = (clone $q)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(function ($e) {
                return [
                    'id'               => $e->id,
                    'title'            => $e->title,
                    'amount'           => (float) $e->amount,
                    'user'             => $e->user,
                    'created_at'       => $e->created_at,
                    'shift_id'         => $e->shift_id,
                    // ✅ حالة السداد — جميعها مدفوعة
                    'payment_status'   => 'paid',
                    'paid_amount'      => (float) $e->amount,
                    'remaining_amount' => 0,
                ];
            });

        return [
            'total' => (float) (clone $q)->sum('amount'),
            'count' => (clone $q)->count(),
            'by_day' => (clone $q)
                ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->get(),
            'list' => $list,
        ];
    }

    /* ============================================================
       4) السحوبات — مع حالة السداد (من الـ Debt المرتبط)
       ============================================================
       كل سحب في ShiftController::withdraw يُنشئ Debt مع:
         notes = 'سحب من الدرج: ' . reason
       
       نربط بينهما عبر (user_id + reason + تاريخ الإنشاء)
       ============================================================ */
    protected function withdrawalsSummary(array $range, $branchId): array
    {
        $q = Withdrawal::whereBetween('created_at', [$range['from'], $range['to']]);

        if ($branchId && $branchId !== 'all') {
            $q->whereHas('shift', fn($s) => $s->where('branch_id', $branchId));
        }

        $withdrawals = (clone $q)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        /* ============================================================
           جمع الديون المرتبطة في استعلام واحد (بدون N+1)
           ============================================================ */
        $keys = $withdrawals->map(fn($w) => [
            'user_id' => $w->user_id,
            'notes'   => 'سحب من الدرج: ' . $w->reason,
        ])->unique(fn($k) => $k['user_id'] . '|' . $k['notes']);

        $debts = collect();
        if ($keys->isNotEmpty()) {
            $debts = \App\Models\Debt::whereIn('user_id', $keys->pluck('user_id')->unique())
                ->whereIn('notes', $keys->pluck('notes')->unique())
                ->get()
                ->keyBy(fn($d) => $d->user_id . '|' . $d->notes);
        }

        $list = $withdrawals->map(function ($w) use ($debts) {
            $key  = $w->user_id . '|سحب من الدرج: ' . $w->reason;
            $debt = $debts->get($key);

            return [
                'id'                => $w->id,
                'reason'            => $w->reason,
                'amount'            => (float) $w->amount,
                'user'              => $w->user,
                'created_at'        => $w->created_at,
                'shift_id'          => $w->shift_id,
                // ✅ حالة السداد
                'payment_status'    => $debt?->status ?? 'unknown',
                'paid_amount'       => $debt ? (float) $debt->paid_amount : 0,
                'remaining_amount'  => $debt ? (float) $debt->remaining_amount : (float) $w->amount,
                'debt_id'           => $debt?->id,
            ];
        });

        return [
            'total' => (float) (clone $q)->sum('amount'),
            'count' => (clone $q)->count(),
            'list'  => $list,
        ];
    }

    /* ============================================================
       5) المشتريات
       ============================================================ */
    protected function purchasesSummary(array $range, $branchId): array
    {
        $q = Purchase::whereBetween('created_at', [$range['from'], $range['to']]);
        $this->applyBranch($q, $branchId);

        return [
            'total' => (float) (clone $q)->sum('total_amount'),
            'count' => (clone $q)->count(),
            'list' => (clone $q)
                ->with('supplier:id,name')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ];
    }

    /* ============================================================
       6) سداد الديون — من cash_movements
       ============================================================
       ✅ يدعم الفرع عبر join مع shifts
       ============================================================ */
    protected function debtsPaidSummary(array $range, $branchId): array
    {
        $q = DB::table('cash_movements')
            ->where('cash_movements.type', 'debt_payment')
            ->whereBetween('cash_movements.created_at', [$range['from'], $range['to']]);

        if ($branchId && $branchId !== 'all') {
            $q->join('shifts', 'cash_movements.shift_id', '=', 'shifts.id')
              ->where('shifts.branch_id', $branchId);
        }

        return [
            'total' => (float) $q->sum('cash_movements.amount'),
            'count' => $q->count(),
        ];
    }

    /* ============================================================
       7) الرواتب
       ============================================================
       ✅ يدعم الفرع عبر user.branch_id
       ============================================================ */
    protected function salariesSummary(array $range, $branchId): array
    {
        $q = Salary::whereBetween('paid_at', [
                $range['from']->toDateString(),
                $range['to']->toDateString()
            ])
            ->where('status', 'paid');

        if ($branchId && $branchId !== 'all') {
            $q->whereHas('user', fn($u) => $u->where('branch_id', $branchId));
        }

        return [
            'total' => (float) $q->sum('net_salary'),
            'count' => $q->count(),
        ];
    }
}