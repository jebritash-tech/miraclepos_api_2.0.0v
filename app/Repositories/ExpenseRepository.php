<?php

namespace App\Repositories;

use App\Models\Expense;

class ExpenseRepository
{
    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function allByShift(int $shiftId)
    {
        return Expense::where(
            'shift_id',
            $shiftId
        )
        ->latest()
        ->get();
    }

    public function totalByShift(int $shiftId): float
    {
        return Expense::where(
            'shift_id',
            $shiftId
        )->sum('amount');
    }
}