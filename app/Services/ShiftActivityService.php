<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\ShiftActivity;

class ShiftActivityService
{
    const OPEN = 'open';

    const CLOSE = 'close';

    const SALE = 'sale';

    const EXPENSE = 'expense';

    const WITHDRAW = 'withdraw';

    const DEBT = 'debt'; // Added for new debts

    const DEBT_PAYMENT = 'debt_payment';

    const REFUND = 'refund';

    /**
     * تسجيل نشاط داخل الوردية
     */
    public function log(
        Shift $shift,
        string $type,
        string $title,
        float $amount = 0,
        ?string $description = null,
        array $meta = []
    ): ShiftActivity {

        return ShiftActivity::create([

            'shift_id' => $shift->id,

            'user_id' => auth()->id(),

            'type' => $type,

            'title' => $title,

            'amount' => $amount,

            'description' => $description,

            'meta' => $meta

        ]);

    }
}