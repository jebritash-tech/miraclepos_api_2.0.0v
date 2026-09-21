<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ExpenseService;
use App\Models\Shift;
use App\Models\Expense;

class ExpenseController extends Controller
{
    /* ============================================================
       POST /api/expenses
       ============================================================
       - admin  → shift_id = null دائمًا (مصروف إداري مستقل)
       - cashier → يحتاج وردية مفتوحة (أو shift_id مُرسَل من المزامنة)
       ============================================================ */
    public function store(Request $request, ExpenseService $service)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'amount'   => 'required|numeric|min:1',
            'shift_id' => 'nullable|exists:shifts,id',
            'notes'    => 'nullable|string',
        ]);

        $user = $request->user();
        $shiftId = $request->input('shift_id');

        /* ============================================================
           admin → لا يُربط بأي وردية
           ============================================================ */
        if ($user->role === 'admin') {
            $shiftId = null;
        }

        /* ============================================================
           cashier → يجب أن يكون مرتبطاً بوردية
           ============================================================ */
        else {
            // أولوية: shift_id المُرسَل (من المزامنة)
            if ($shiftId) {
                $shiftExists = Shift::where('id', $shiftId)
                    ->where('user_id', $user->id)
                    ->exists();

                if (!$shiftExists) {
                    return response()->json([
                        'message' => 'الوردية المحددة لا تخص المستخدم الحالي.'
                    ], 403);
                }
            }
            // احتياطي: ابحث عن وردية مفتوحة
            else {
                $activeShift = Shift::where('user_id', $user->id)
                    ->where('status', 'open')
                    ->first();

                if (!$activeShift) {
                    return response()->json([
                        'message' => 'لا توجد وردية مفتوحة لتسجيل المصروف تحتها.'
                    ], 422);
                }

                $shiftId = $activeShift->id;
            }
        }

        $expense = $service->create([
            'shift_id' => $shiftId,
            'user_id'  => $user->id,
            'title'    => $request->title,
            'amount'   => $request->amount,
            'notes'    => $request->notes,
            // ✅ السطر الجديد
            'created_at' => $request->filled('created_at')
                ? \Carbon\Carbon::parse($request->created_at)
                : now(),
        ]);

        return response()->json([
            'message' => 'تم تسجيل المصروف بنجاح',
            'expense' => $expense->load(['user:id,name', 'shift:id']),
        ], 201);
    }

    /* ============================================================
       GET /api/expenses
       ============================================================
       Filters:
         - type=all|admin|shift
         - shift_id
         - search
         - from, to (dates)
         - per_page, page
       ============================================================ */
    public function index(Request $request)
    {
        $query = Expense::with(['user:id,name', 'shift:id']);

        /* ---- فلتر النوع ---- */
        $type = $request->input('type', 'all');
        if ($type === 'admin') {
            $query->whereNull('shift_id');
        } elseif ($type === 'shift') {
            $query->whereNotNull('shift_id');
        }

        /* ---- فلتر الوردية ---- */
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }

        /* ---- بحث ---- */
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        /* ---- فلتر التاريخ ---- */
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $expenses = $query->orderBy('created_at', 'desc')->get();

        /* ---- الإحصائيات (على المجموعة المُفلترة) ---- */
        $stats = [
            'total'        => (float) $expenses->sum('amount'),
            'count'        => $expenses->count(),
            'admin_total'  => (float) $expenses->whereNull('shift_id')->sum('amount'),
            'admin_count'  => $expenses->whereNull('shift_id')->count(),
            'shift_total'  => (float) $expenses->whereNotNull('shift_id')->sum('amount'),
            'shift_count'  => $expenses->whereNotNull('shift_id')->count(),
        ];

        /* ---- الإحصائيات العامة (غير مفلترة) ---- */
        $allExpenses = Expense::selectRaw('SUM(amount) as total, COUNT(*) as count')
            ->selectRaw('SUM(CASE WHEN shift_id IS NULL THEN amount ELSE 0 END) as admin_total')
            ->selectRaw('SUM(CASE WHEN shift_id IS NOT NULL THEN amount ELSE 0 END) as shift_total')
            ->selectRaw('SUM(CASE WHEN shift_id IS NULL THEN 1 ELSE 0 END) as admin_count')
            ->selectRaw('SUM(CASE WHEN shift_id IS NOT NULL THEN 1 ELSE 0 END) as shift_count')
            ->first();

        return response()->json([
            'data' => $expenses,
            'stats' => $stats,
            'stats_global' => [
                'total'       => (float) ($allExpenses->total ?? 0),
                'count'       => (int) ($allExpenses->count ?? 0),
                'admin_total' => (float) ($allExpenses->admin_total ?? 0),
                'admin_count' => (int) ($allExpenses->admin_count ?? 0),
                'shift_total' => (float) ($allExpenses->shift_total ?? 0),
                'shift_count' => (int) ($allExpenses->shift_count ?? 0),
            ],
        ]);
    }

    /* ============================================================
       GET /api/expenses/{id}
       ============================================================ */
    public function show($id)
    {
        $expense = Expense::with(['user:id,name', 'shift:id'])->findOrFail($id);

        return response()->json($expense);
    }

    /* ============================================================
       DELETE /api/expenses/{id}  (اختياري)
       ============================================================ */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);

        // إذا كان مرتبطاً بور دية، يجب تعديل إحصائياتها
        if ($expense->shift_id) {
            $shift = Shift::find($expense->shift_id);
            if ($shift) {
                $shift->expenses_amount = max(0, $shift->expenses_amount - $expense->amount);
                app(\App\Services\ShiftService::class)->recalculateExpectedCash($shift);
            }
        }

        $expense->delete();

        return response()->json(['message' => 'تم حذف المصروف']);
    }
}