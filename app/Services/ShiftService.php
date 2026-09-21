<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\Sale;

class ShiftService
{
    protected ShiftActivityService $activity;

    public function __construct(ShiftActivityService $activity)
    {
        $this->activity = $activity;
    }

    /*
    |--------------------------------------------------------------------------
    | EXPECTED CASH — المصدر الوحيد للحقيقة
    |--------------------------------------------------------------------------
    */

    public function recalculateExpectedCash(Shift $shift): void
    {
        $shift->expected_cash =
            (float) $shift->opening_cash
            + (float) $shift->cash_sales
            - (float) $shift->withdraw_amount
            - (float) $shift->refund_amount
            - (float) $shift->expenses_amount;

        $shift->save();
    }

    /*
    |--------------------------------------------------------------------------
    | SALE
    |--------------------------------------------------------------------------
    */

    public function registerSale(Sale $sale): void
    {
        $shift = Shift::find($sale->shift_id);
        if (!$shift) return;

        $shift->sales_count += 1;

        if ($sale->payment_method === 'cash') {
            $shift->cash_sales += $sale->total_amount;
        } else {
            $shift->card_sales += $sale->total_amount;
        }

        $this->recalculateExpectedCash($shift);

        $this->activity->log(
            $shift,
            ShiftActivityService::SALE,
            'فاتورة بيع',
            $sale->total_amount,
            'فاتورة رقم: ' . $sale->id,
            ['sale_id' => $sale->id]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REFUND
    |--------------------------------------------------------------------------
    */

    /**
     * ✅ إصلاح: الإرجاع يُعكس نفس طريقة الدفع الأصلية
     *
     * - إذا كانت الفاتورة الأصلية نقدية → النقد يخرج من الدرج
     *   (refund_amount += X، و expected_cash يقل تلقائياً)
     *
     * - إذا كانت الفاتورة الأصلية بنكية → النقد يخرج من البنك
     *   (card_sales -= X، ولا يتأثر الدرج)
     *
     * @param float $amount               مبلغ الإرجاع
     * @param Shift $shift                الوردية
     * @param string $originalPaymentMethod طريقة دفع الفاتورة الأصلية
     */
    public function registerRefund(
        float $amount,
        Shift $shift,
        string $originalPaymentMethod = 'cash'
    ): void {
        $isCashRefund = strtolower($originalPaymentMethod) === 'cash';

        if ($isCashRefund) {
            // النقد خرج فعلاً من الدرج
            $shift->refund_amount += $amount;
        } else {
            // المبلغ يعود للبنك — لا يمس الدرج
            $shift->card_sales = max(0, (float) $shift->card_sales - $amount);
        }

        $this->recalculateExpectedCash($shift);

        $this->activity->log(
            $shift,
            ShiftActivityService::REFUND,
            $isCashRefund ? 'مرتجع نقدي' : 'مرتجع بنكي',
            $amount,
            $isCashRefund ? null : "إرجاع على الحساب البنكي: {$originalPaymentMethod}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXPENSE
    |--------------------------------------------------------------------------
    */

    public function registerExpense(float $amount, Shift $shift): void
    {
        $shift->expenses_amount += $amount;
        $this->recalculateExpectedCash($shift);

        $this->activity->log(
            $shift,
            ShiftActivityService::EXPENSE,
            'مصروف',
            $amount
        );
    }

    /*
    |--------------------------------------------------------------------------
    | WITHDRAWAL
    |--------------------------------------------------------------------------
    */

    public function registerWithdrawal(float $amount, Shift $shift): void
    {
        $shift->withdraw_amount += $amount;
        $this->recalculateExpectedCash($shift);

        $this->activity->log(
            $shift,
            ShiftActivityService::WITHDRAW,
            'سحب موظف',
            $amount
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEBT PAYMENT
    |--------------------------------------------------------------------------
    | ✅ Bug fix #1: تقبل notes = null
    |--------------------------------------------------------------------------
    */

    public function registerDebtPayment(
        float $amount,
        Shift $shift,
        ?string $notes = null
    ): void {
        $shift->cash_sales += $amount;
        $shift->debts_amount += $amount;
        $this->recalculateExpectedCash($shift);

        $this->activity->log(
            $shift,
            ShiftActivityService::DEBT_PAYMENT,
            'سداد دين',
            $amount,
            $notes ?? ''
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NEW DEBT
    |--------------------------------------------------------------------------
    | ✅ Bug fix #1: تقبل notes = null
    |--------------------------------------------------------------------------
    */

    public function registerDebt(
        float $amount,
        Shift $shift,
        ?string $notes = null
    ): void {
        $shift->debts_amount += $amount;
        $this->recalculateExpectedCash($shift);

        $this->activity->log(
            $shift,
            ShiftActivityService::DEBT,
            'تسجيل دين جديد',
            $amount,
            $notes ?? ''
        );
    }
}