<?php

namespace App\Repositories;

use App\Models\EmployeeDebt;

class EmployeeDebtRepository
{
    public function create(array $data)
    {
        return EmployeeDebt::create($data);
    }

    public function pendingByUser($userId)
    {
        return EmployeeDebt::where(
            'user_id',
            $userId
        )
        ->where(
            'status',
            '!=',
            'paid'
        )
        ->get();
    }
}