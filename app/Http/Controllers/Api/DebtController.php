<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\EmployeeDebt;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    /* ============================================================
       GET /api/debts
       يجمع الديون من:
         - debts (الأدمن + سحوبات الوردية)
         - employee_debts (سحوبات الموظفين)
       مع فلاتر + إحصائيات
       ============================================================ */
    public function index(Request $request)
    {
        /* ---------- جلب من debts ---------- */
        $debtsQuery = Debt::with([
            'user:id,name,email',
            'branch:id,name',
            'sale:id,total_amount,created_at',
            'payments.user:id,name',
        ]);

        /* ---------- جلب من employee_debts ---------- */
        $employeeQuery = EmployeeDebt::with([
            'user:id,name,email',
            'shift:id,opened_at,closed_at',
        ]);

        /* ---------- تطبيق الفلاتر المشتركة ---------- */
        $this->applyFilters($debtsQuery, $request, 'debt');
        $this->applyFilters($employeeQuery, $request, 'employee');

        /* ---------- دمج النتائج ---------- */
        $debtsItems = $debtsQuery->latest()->get()->map(fn($d) => $this->normalizeDebt($d));
        $employeeItems = $employeeQuery->latest()->get()->map(fn($e) => $this->normalizeEmployeeDebt($e));
        /* ---------- دمج النتائج ---------- */
        // ✅ نحوّل النتيجة إلى Base Collection لأن عناصرها arrays (ليست Models)
        $debtsItems = collect(
            $debtsQuery->latest()->get()
                ->map(fn($d) => $this->normalizeDebt($d))
                ->all()
        );

        $employeeItems = collect(
            $employeeQuery->latest()->get()
                ->map(fn($e) => $this->normalizeEmployeeDebt($e))
                ->all()
        );

        // الآن merge() يعمل بشكل صحيح (Base Collection)
        $merged = $debtsItems
            ->merge($employeeItems)
            ->sortByDesc('created_at')
            ->values();

        /* ---------- Pagination يدوي ---------- */
        $perPage = min((int) $request->input('per_page', 15), 100);
        $page    = max(1, (int) $request->input('page', 1));
        $total   = $merged->count();
        $lastPage = max(1, (int) ceil($total / $perPage));

        $items = $merged
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        /* ---------- الإحصائيات (بدون فلاتر — عامة) ---------- */
        $stats = $this->buildStats();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $page,
                'last_page'    => $lastPage,
                'per_page'     => $perPage,
                'total'        => $total,
            ],
            'stats' => $stats,
        ]);
    }

    /* ============================================================
       POST /api/debts
       إنشاء دين مخصص من الأدمن
       ============================================================ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'    => 'nullable|exists:branches,id',
            'user_id'      => 'nullable|exists:users,id',
            'total_amount' => 'required|numeric|min:1',
            'due_date'     => 'nullable|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $debt = Debt::create([
            'user_id'          => $validated['user_id'] ?? auth()->id(),
            'branch_id'        => $validated['branch_id'] ?? auth()->user()->branch_id,
            'total_amount'     => $validated['total_amount'],
            'paid_amount'      => 0,
            'remaining_amount' => $validated['total_amount'],
            'status'           => 'pending',
            'due_date'         => $validated['due_date'] ?? null,
            'notes'            => $validated['notes'] ?? 'دين مخصص من الإدارة',
        ]);

        return response()->json([
            'message' => 'تم تسجيل الدين بنجاح',
            'debt'    => $debt->load(['user:id,name', 'branch:id,name']),
        ], 201);
    }

    /* ============================================================
       GET /api/debts/{id}
       ============================================================ */
    public function show(Request $request, $id)
    {
        // يدعم شكلين:
        //   - رقم صحيح → من debts
        //   - emp_123 → من employee_debts
        if (is_string($id) && str_starts_with($id, 'emp_')) {
            $realId = (int) substr($id, 4);
            $employee = EmployeeDebt::with(['user:id,name,email', 'shift:id,opened_at,closed_at'])
                ->findOrFail($realId);

            return response()->json($this->normalizeEmployeeDebt($employee));
        }

        $debt = Debt::with([
            'user:id,name,email',
            'branch:id,name',
            'sale:id,total_amount,created_at',
            'payments.user:id,name',
        ])->findOrFail($id);

        return response()->json($this->normalizeDebt($debt));
    }

    /* ============================================================
       POST /api/debts/{id}/payment
       سداد على debt فقط (employee_debts لا يدعم السداد حالياً)
       ============================================================ */
    public function payment(Request $request, $id)
    {
        // منع السداد على employee_debts
        if (is_string($id) && str_starts_with($id, 'emp_')) {
            return response()->json([
                'message' => 'لا يمكن تسجيل سداد على سحوبات الموظفين من هذه الواجهة',
            ], 422);
        }

        $debt = Debt::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes'  => 'nullable|string|max:500',
        ]);

        if ($request->amount > $debt->remaining_amount) {
            return response()->json([
                'message'   => 'المبلغ المدفوع أكبر من المتبقي',
                'remaining' => (float) $debt->remaining_amount,
            ], 422);
        }

        DB::transaction(function () use ($request, $debt) {
            DebtPayment::create([
                'debt_id' => $debt->id,
                'user_id' => auth()->id(),
                'amount'  => $request->amount,
                'notes'   => $request->notes ?? null,
            ]);

            $debt->paid_amount += $request->amount;
            $debt->remaining_amount -= $request->amount;

            if ($debt->remaining_amount <= 0) {
                $debt->remaining_amount = 0;
                $debt->status = 'paid';
            } else {
                $debt->status = 'partial';
            }

            $debt->save();
        });

        return response()->json([
            'message' => 'تم تسجيل السداد بنجاح',
            'debt'    => $this->normalizeDebt(
                $debt->fresh()->load(['user:id,name', 'branch:id,name', 'payments.user:id,name'])
            ),
        ]);
    }

    /* ============================================================
       PUT /api/debts/{id}
       ============================================================ */
    public function update(Request $request, $id)
    {
        if (is_string($id) && str_starts_with($id, 'emp_')) {
            return response()->json(['message' => 'غير مدعوم'], 422);
        }

        $debt = Debt::findOrFail($id);

        $validated = $request->validate([
            'total_amount' => 'sometimes|numeric|min:0',
            'due_date'     => 'nullable|date',
            'notes'        => 'nullable|string|max:500',
            'status'       => 'sometimes|in:pending,partial,paid',
        ]);

        if (isset($validated['total_amount'])) {
            $validated['remaining_amount'] = max(0, $validated['total_amount'] - $debt->paid_amount);

            if ($validated['remaining_amount'] <= 0) {
                $validated['status'] = 'paid';
            } elseif ($debt->paid_amount > 0) {
                $validated['status'] = 'partial';
            } else {
                $validated['status'] = 'pending';
            }
        }

        $debt->update($validated);

        return response()->json([
            'message' => 'تم تحديث الدين',
            'debt'    => $this->normalizeDebt($debt->fresh()),
        ]);
    }
        /* ============================================================
       GET /api/debts/pending-for-me
       يُرجع الديون المعلقة للمستخدم الحالي (debts + employee_debts)
       يُستخدم في POS modal لاختيار دين محدد للسداد
       ============================================================ */
    /* في DebtController.php */
    public function pending(Request $request)
    {
        $userId = auth()->id();

        $debts = Debt::where('user_id', $userId)
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('created_at')
            ->get()
            ->map(fn($d) => [
                'id'                => (string) $d->id,
                'source'            => 'debt',
                'source_label'      => $d->sale_id ? 'دين فاتورة' : 'سحب من الدرج',
                'total_amount'      => (float) $d->total_amount,
                'paid_amount'       => (float) $d->paid_amount,
                'remaining_amount'  => (float) $d->remaining_amount,
                'status'            => $d->status,
                'created_at'        => $d->created_at?->toIso8601String(),
                'notes'             => $d->notes,
            ]);

        $empDebts = EmployeeDebt::where('user_id', $userId)
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('created_at')
            ->get()
            ->map(fn($e) => [
                'id'                => 'emp_' . $e->id,
                'source'            => 'employee',
                'source_label'      => 'سحب موظف',
                'total_amount'      => (float) $e->amount,
                'paid_amount'       => (float) $e->paid_amount,
                'remaining_amount'  => max(0, (float) $e->amount - (float) $e->paid_amount),
                'status'            => $e->status,
                'created_at'        => $e->created_at?->toIso8601String(),
                'notes'             => $e->reason,
            ]);

        $all = $debts->concat($empDebts)->sortBy('created_at')->values();

        return response()->json([
            'debts'           => $all,
            'total_remaining' => $all->sum('remaining_amount'),
            'count'           => $all->count(),
        ]);
    }
    /* ============================================================
       DELETE /api/debts/{id}
       ============================================================ */
    public function destroy($id)
    {
        if (is_string($id) && str_starts_with($id, 'emp_')) {
            return response()->json(['message' => 'غير مدعوم'], 422);
        }

        Debt::findOrFail($id)->delete();

        return response()->json(['message' => 'تم حذف الدين']);
    }

    /* ============================================================
       Helpers — Filtering
       ============================================================ */
    private function applyFilters($query, Request $request, string $source): void
    {
        // فلتر الحالة
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // فلتر الفرع (فقط للـ debts)
        if ($source === 'debt' && $request->filled('branch_id') && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        // فلتر المصدر
        if ($request->filled('source') && $request->source !== 'all') {
            $src = $request->source;

            if ($source === 'employee') {
                // لو الفلتر ليس employee → استثنِ
                if (!in_array($src, ['employee', 'withdrawal'])) {
                    $query->whereRaw('1 = 0'); // نتائج فارغة
                }
            } else {
                // debts
                match ($src) {
                    'admin'      => $query->whereNull('sale_id')
                                          ->where(function ($q) {
                                              $q->whereNull('notes')
                                                ->orWhere('notes', 'not like', 'سحب من الدرج%');
                                          }),
                    'withdrawal' => $query->where('notes', 'like', 'سحب من الدرج%'),
                    'sale'       => $query->whereNotNull('sale_id'),
                    'employee'   => $query->whereRaw('1 = 0'), // لا يوجد employee في debts
                    default      => null,
                };
            }
        }

        // البحث
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search, $source) {
                $q->where('id', 'like', "%{$search}%");

                if ($source === 'employee') {
                    $q->orWhere('reason', 'like', "%{$search}%");
                } else {
                    $q->orWhere('notes', 'like', "%{$search}%");
                }

                $q->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // فلتر التاريخ
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
    }

    /* ============================================================
       Helpers — Normalization
       ============================================================ */
    private function normalizeDebt(Debt $debt): array
    {
        $type = $this->detectDebtType($debt);

        return [
            'id'                => (string) $debt->id,
            'real_id'           => $debt->id,
            'source'            => $type,
            'source_label'      => $this->debtTypeLabel($type),
            'source_color'      => $this->debtTypeColor($type),
            'user'              => $debt->user,
            'branch'            => $debt->branch,
            'shift'             => null,
            'sale'              => $debt->sale,
            'total_amount'      => (float) $debt->total_amount,
            'paid_amount'       => (float) $debt->paid_amount,
            'remaining_amount'  => (float) $debt->remaining_amount,
            'status'            => $debt->status,
            'due_date'          => $debt->due_date,
            'notes'             => $debt->notes,
            'payments'          => $debt->relationLoaded('payments') ? $debt->payments : [],
            'created_at'        => $debt->created_at?->toIso8601String(),
            'updated_at'        => $debt->updated_at?->toIso8601String(),
        ];
    }

    private function normalizeEmployeeDebt(EmployeeDebt $employee): array
    {
        $remaining = (float) $employee->amount - (float) $employee->paid_amount;

        return [
            'id'                => 'emp_' . $employee->id,
            'real_id'           => $employee->id,
            'source'            => 'employee',
            'source_label'      => 'سحب موظف',
            'source_color'      => 'rose',
            'user'              => $employee->user,
            'branch'            => null,
            'shift'             => $employee->shift,
            'sale'              => null,
            'total_amount'      => (float) $employee->amount,
            'paid_amount'       => (float) $employee->paid_amount,
            'remaining_amount'  => max(0, $remaining),
            'status'            => $employee->status,
            'due_date'          => null,
            'notes'             => $employee->reason,
            'payments'          => [],
            'created_at'        => $employee->created_at?->toIso8601String(),
            'updated_at'        => $employee->updated_at?->toIso8601String(),
        ];
    }

    /* ============================================================
       Helpers — Stats
       ============================================================ */
    private function buildStats(): array
    {
        $adminCount = Debt::whereNull('sale_id')
            ->where(function ($q) {
                $q->whereNull('notes')
                  ->orWhere('notes', 'not like', 'سحب من الدرج%');
            })->count();

        $withdrawalCount = Debt::where('notes', 'like', 'سحب من الدرج%')->count();
        $saleCount = Debt::whereNotNull('sale_id')->count();
        $employeeCount = EmployeeDebt::count();

        $totalRemaining =
            (float) Debt::whereIn('status', ['pending', 'partial'])->sum('remaining_amount')
            + (float) EmployeeDebt::whereIn('status', ['pending', 'partial'])
                ->selectRaw('SUM(amount - paid_amount) as r')->value('r');

        $totalPaid =
            (float) Debt::sum('paid_amount')
            + (float) EmployeeDebt::sum('paid_amount');

        $totalAmount =
            (float) Debt::sum('total_amount')
            + (float) EmployeeDebt::sum('amount');

        $countPending =
            Debt::whereIn('status', ['pending', 'partial'])->count()
            + EmployeeDebt::whereIn('status', ['pending', 'partial'])->count();

        $countPaid =
            Debt::where('status', 'paid')->count()
            + EmployeeDebt::where('status', 'paid')->count();

        return [
            'total_remaining'    => $totalRemaining,
            'total_paid'         => $totalPaid,
            'total_amount'       => $totalAmount,
            'count_pending'      => $countPending,
            'count_paid'         => $countPaid,
            'count_all'          => Debt::count() + EmployeeDebt::count(),
            'count_admin'        => $adminCount,
            'count_withdrawal'   => $withdrawalCount,
            'count_sale'         => $saleCount,
            'count_employee'     => $employeeCount,
        ];
    }

    /* ============================================================
       Helpers — Type
       ============================================================ */
    private function detectDebtType(Debt $debt): string
    {
        if ($debt->sale_id) {
            return 'sale';
        }
        if (str_starts_with($debt->notes ?? '', 'سحب من الدرج')) {
            return 'withdrawal';
        }
        return 'admin';
    }

    private function debtTypeLabel(string $type): string
    {
        return match ($type) {
            'sale'       => 'دين فاتورة',
            'withdrawal' => 'سحب من الدرج',
            'admin'      => 'دين مخصص',
            default      => 'غير محدد',
        };
    }

    private function debtTypeColor(string $type): string
    {
        return match ($type) {
            'sale'       => 'blue',
            'withdrawal' => 'amber',
            'admin'      => 'purple',
            default      => 'gray',
        };
    }
}