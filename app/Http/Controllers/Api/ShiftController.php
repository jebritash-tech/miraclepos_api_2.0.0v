<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\CashMovement;
use App\Services\AuditLogger;
use App\Services\ShiftActivityService;
use App\Services\ShiftService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftController extends Controller
{
    protected ShiftActivityService $activity;
    protected ShiftService $shiftService;

    public function __construct(
        ShiftActivityService $activity,
        ShiftService $shiftService
    )
    {
        $this->activity = $activity;
        $this->shiftService = $shiftService;
    }

    public function index()
    {
        $shifts = Shift::with('user:id,name', 'branch:id,name')
            ->latest()
            ->paginate(5);

        $shifts->getCollection()->transform(function ($shift) {
            $shift->difference = $shift->closing_cash === null
                ? null
                : (float)$shift->closing_cash - (float)$shift->expected_cash;
            return $shift;
        });

        return $shifts;
    }

    public function show(Shift $shift)
    {
        $shift->load([
            'user:id,name',
            'branch:id,name',
            'activities' => function($q){
                $q->latest();
            }
        ]);

        return response()->json([
            'shift' => $shift,
            'activities' => $shift->activities
        ]);
    }

    public function current(Request $request)
    {
        $shift = Shift::where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        if (!$shift) {
            return response()->json(['opened' => false]);
        }

        return response()->json([
            'opened' => true,
            'shift' => $shift
        ]);
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0'
        ]);

        $user = auth()->user();

        $existing = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'لديك وردية مفتوحة بالفعل.'
            ], 409);
        }

        $shift = Shift::create([
            'user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'opening_cash' => $request->opening_cash,
            'expected_cash' => $request->opening_cash,
            'cash_sales' => 0,
            'card_sales' => 0,
            'refund_amount' => 0,
            'expenses_amount' => 0,
            'debts_amount' => 0,
            'withdraw_amount' => 0,
            'sales_count' => 0,
            'status' => 'open',
            'opened_at' => now()
        ]);

        AuditLogger::log(
            'shift_opened',
            $shift,
            "فتح وردية برصيد ابتدائي {$shift->opening_cash}",
            [],
            ['opening_cash' => $shift->opening_cash],
            'info',
            ['branch_id' => $shift->branch_id, 'shift_id' => $shift->id]
        );

        $this->activity->log($shift, ShiftActivityService::OPEN, 'فتح الوردية');

        return response()->json([
            'message' => 'تم فتح الوردية بنجاح',
            'shift' => $shift,
            'shifts' => $this->getShiftList($request),
            'stats' => $this->getShiftStats()
        ]);
    }

    public function close(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0'
        ]);

        $shift = Shift::where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();

        if (!$shift) {
            return response()->json(['message' => 'لا توجد وردية مفتوحة.'], 404);
        }

        $shift->closing_cash = $request->closing_cash;
        $shift->status = 'closed';
        $shift->closed_at = now();
        $shift->save();

        try {
            $difference = (float) $shift->closing_cash - (float) $shift->expected_cash;
            $differenceText = $difference == 0
                ? 'مطابق'
                : ($difference > 0
                    ? 'زيادة +' . number_format($difference, 2)
                    : 'عجز ' . number_format($difference, 2));

            AuditLogger::log(
                'shift_closed',
                $shift,
                "إغلاق وردية #{$shift->id} — الرصيد الفعلي: {$shift->closing_cash}، الفرق: {$differenceText}",
                [],
                [
                    'closing_cash' => (float) $shift->closing_cash,
                    'expected_cash' => (float) $shift->expected_cash,
                    'difference' => $difference,
                    'sales_count' => (int) $shift->sales_count,
                ],
                'info',
                ['branch_id' => $shift->branch_id, 'shift_id' => $shift->id]
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'تم إغلاق الوردية',
            'shift' => $shift,
            'shifts' => $this->getShiftList($request),
            'stats' => $this->getShiftStats()
        ]);
    }

    /* ============================================================
       WITHDRAW — يقبل shift_id من الطلب (للمزامنة Offline)
       ============================================================ */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'reason'      => 'required|string|max:255',
            'borrower_id' => 'nullable|exists:users,id',
            'shift_id'    => 'nullable|exists:shifts,id',
            'created_at'  => 'nullable|date',  // ✅ جديد
        ]);

        // ✅ يقبل shift_id من الطلب (للمزامنة)، وإلا يبحث عن وردية مفتوحة
        if ($request->filled('shift_id')) {
            $shift = Shift::findOrFail($request->shift_id);

            // حماية: تأكد أن الوردية تخص المستخدم الحالي
            if ((int) $shift->user_id !== (int) auth()->id()) {
                return response()->json([
                    'message' => 'الوردية لا تخص المستخدم الحالي.'
                ], 403);
            }
        } else {
            $shift = Shift::where('status', 'open')
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        return DB::transaction(function () use ($request, $shift) {

            $userId = auth()->id();
            $amount = (float) $request->amount;
            $borrowerId = $request->input('borrower_id', $userId);

            // 1. CashMovement
            $operationTime = $request->filled('created_at')
                ? \Carbon\Carbon::parse($request->created_at)
                : now();

            CashMovement::create([
                'shift_id'   => $shift->id,
                'user_id'    => $userId,
                'type'       => 'withdraw',
                'amount'     => $amount,
                'notes'      => $request->reason,
                'created_at' => $operationTime,  // ✅
            ]);

            // 2. Withdrawal
            $withdrawal = \App\Models\Withdrawal::create([
                'shift_id'   => $shift->id,
                'user_id'    => $userId,
                'amount'     => $amount,
                'reason'     => $request->reason,
                'created_at' => $operationTime,  // ✅
            ]);

            // 3. Debt
            $debt = \App\Models\Debt::create([
                'user_id'          => $borrowerId,
                'branch_id'        => $shift->branch_id,
                'total_amount'     => $amount,
                'paid_amount'      => 0,
                'remaining_amount' => $amount,
                'status'           => 'pending',
                'notes'            => 'سحب من الدرج: ' . $request->reason,
                'due_date'         => now()->addDays(30),
                'created_at'       => $operationTime,  // ✅
            ]);

            // 4. Audit
            AuditLogger::critical(
                'withdraw',
                $withdrawal,
                "سحب نقدي بمبلغ {$amount} — السبب: {$request->reason}",
                [],
                [],
                [
                    'amount'    => $amount,
                    'debt_id'   => $debt->id,
                    'shift_id'  => $shift->id,
                    'branch_id' => $shift->branch_id,
                ]
            );

            // 5. تحديث الوردية
            $this->shiftService->registerWithdrawal($amount, $shift);

            return response()->json([
                'message' => 'تم تسجيل السحب',
                'shifts'  => $this->getShiftList($request),
                'stats'   => $this->getShiftStats(),
            ]);
        });
    }

    /* ============================================================
       DEBT PAYMENT — يقبل debt_id اختياري + shift_id للمزامنة
       ============================================================ */
    public function debtPayment(Request $request)
    {
        $request->validate([
            'amount'  => 'required|numeric|min:0.01',
            'notes'   => 'nullable|string|max:255',
            'debt_id' => 'nullable|string',   // 'emp_X' أو رقم
            'shift_id' => 'nullable|exists:shifts,id',
            'created_at' => 'nullable|date',  // ✅ جديد
        ]);

        // ✅ يقبل shift_id من الطلب (للمزامنة)، وإلا يبحث عن وردية مفتوحة
        if ($request->filled('shift_id')) {
            $shift = Shift::findOrFail($request->shift_id);

            if ((int) $shift->user_id !== (int) auth()->id()) {
                return response()->json([
                    'message' => 'الوردية لا تخص المستخدم الحالي.'
                ], 403);
            }
        } else {
            $shift = Shift::where('status', 'open')
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }

        return DB::transaction(function () use ($request, $shift) {

            $userId = auth()->id();
            $amount = (float) $request->amount;

            // 1. CashMovement
            $operationTime = $request->filled('created_at')
                ? \Carbon\Carbon::parse($request->created_at)
                : now();

            CashMovement::create([
                'shift_id'   => $shift->id,
                'user_id'    => $userId,
                'type'       => 'debt_payment',
                'amount'     => $amount,
                'notes'      => $request->notes,
                'created_at' => $operationTime,  // ✅
            ]);

            // 2. تحديث الوردية
            $this->shiftService->registerDebtPayment(
                $amount,
                $shift,
                $request->notes ?? ''
            );

            // 3. توزيع المبلغ
            if ($request->filled('debt_id')) {
                // ✅ سداد على دين محدد
                $this->attributePaymentToSpecificDebt(
                    $userId,
                    $request->debt_id,
                    $amount,
                    $request->notes
                );
            } else {
                // ✅ توزيع FIFO تلقائي
                $this->attributeDebtPayment($userId, $amount, $request->notes);
            }

            return response()->json([
                'message' => 'تم تسجيل السداد بنجاح',
                'shifts'  => $this->getShiftList($request),
                'stats'   => $this->getShiftStats(),
            ]);
        });
    }

    /* ============================================================
       attributePaymentToSpecificDebt — سداد على دين محدد
       ============================================================ */
    private function attributePaymentToSpecificDebt(
        int $userId,
        string $debtId,
        float $amount,
        ?string $notes
    ): void {
        if (str_starts_with($debtId, 'emp_')) {
            /* ===== employee_debts ===== */
            $realId = (int) substr($debtId, 4);

            $emp = \App\Models\EmployeeDebt::where('id', $realId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$emp) return;

            $remaining = (float) $emp->amount - (float) $emp->paid_amount;
            $toPay = min($amount, $remaining);

            if ($toPay > 0) {
                $emp->paid_amount += $toPay;
                $emp->status = $emp->paid_amount >= $emp->amount ? 'paid' : 'partial';
                $emp->save();
            }
        } else {
            /* ===== debts ===== */
            $realId = (int) $debtId;

            $debt = \App\Models\Debt::where('id', $realId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$debt) return;

            $toPay = min($amount, (float) $debt->remaining_amount);

            if ($toPay <= 0) return;

            \App\Models\DebtPayment::create([
                'debt_id' => $debt->id,
                'user_id' => $userId,
                'amount'  => $toPay,
                'notes'   => $notes,
            ]);

            $debt->paid_amount      += $toPay;
            $debt->remaining_amount -= $toPay;

            if ($debt->remaining_amount <= 0) {
                $debt->remaining_amount = 0;
                $debt->status = 'paid';
            } else {
                $debt->status = 'partial';
            }

            $debt->save();
        }
    }

    /* ============================================================
       attributeDebtPayment — توزيع FIFO تلقائي
       ============================================================ */
    private function attributeDebtPayment(int $userId, float $amount, ?string $notes): void
    {
        $remaining = $amount;

        // أ) debts
        $debts = \App\Models\Debt::where('user_id', $userId)
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        foreach ($debts as $debt) {
            if ($remaining <= 0) break;

            $toPay = min($remaining, (float) $debt->remaining_amount);
            if ($toPay <= 0) continue;

            \App\Models\DebtPayment::create([
                'debt_id' => $debt->id,
                'user_id' => $userId,
                'amount'  => $toPay,
                'notes'   => $notes,
            ]);

            $debt->paid_amount      += $toPay;
            $debt->remaining_amount -= $toPay;

            if ($debt->remaining_amount <= 0) {
                $debt->remaining_amount = 0;
                $debt->status = 'paid';
            } else {
                $debt->status = 'partial';
            }

            $debt->save();
            $remaining -= $toPay;
        }

        // ب) employee_debts
        if ($remaining > 0) {
            $empDebts = \App\Models\EmployeeDebt::where('user_id', $userId)
                ->whereIn('status', ['pending', 'partial'])
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            foreach ($empDebts as $emp) {
                if ($remaining <= 0) break;

                $remForDebt = (float) $emp->amount - (float) $emp->paid_amount;
                $toPay = min($remaining, $remForDebt);
                if ($toPay <= 0) continue;

                $emp->paid_amount += $toPay;
                $emp->status = $emp->paid_amount >= $emp->amount ? 'paid' : 'partial';
                $emp->save();

                $remaining -= $toPay;
            }
        }

        // ج) المتبقي غير المُوزَّع
        if ($remaining > 0) {
            Log::info('debt_payment has unattributed remainder', [
                'user_id'      => $userId,
                'total_amount' => $amount,
                'unattributed' => $remaining,
                'notes'        => $notes,
            ]);
        }
    }

    private function getShiftList(Request $request)
    {
        return Shift::with(['user:id,name', 'branch:id,name'])
            ->latest()
            ->paginate(5)
            ->through(function ($shift) {
                $shift->difference = $shift->closing_cash !== null
                    ? (float)$shift->closing_cash - (float)$shift->expected_cash
                    : null;
                return $shift;
            });
    }

    private function getShiftStats()
    {
        // حسب الحاجة
    }
}