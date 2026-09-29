<?php

namespace App\Repositories;

use App\Models\Withdrawal;

class WithdrawalRepository
{
    public function create(array $data)
    {
        return Withdrawal::create($data);
    }

    public function allByShift($shiftId)
    {
        return Withdrawal::where(
            'shift_id',
            $shiftId
        )->latest()->get();
    }
}