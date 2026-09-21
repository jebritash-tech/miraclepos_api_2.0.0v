<?php

namespace App\Services;

use App\Models\Shift;
use App\Repositories\ExpenseRepository;

class ExpenseService
{
    public function __construct(
        protected ExpenseRepository $expenses,
        protected ShiftService $shiftService
    ) {}

    /**
     * إنشاء مصروف
     *
     * - إذا كان shift_id = null → مصروف إداري (لا يؤثر على أي وردية)
     * - إذا كان shift_id موجوداً → مصروف وردية (يُحدّث درج الوردية)
     */
    public function create(array $data)
    {
        $expense = $this->expenses->create($data);

        // ✅ فقط إذا كان مرتبطاً بور دية، حدّث الوردية
        if (!empty($data['shift_id'])) {
            $shift = Shift::find($data['shift_id']);
            if ($shift) {
                $this->shiftService->registerExpense($expense->amount, $shift);
            }
        }

        return $expense;
    }
}