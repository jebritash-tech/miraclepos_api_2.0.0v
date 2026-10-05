<?php

namespace App\Services;

use App\Repositories\WithdrawalRepository;
use App\Repositories\EmployeeDebtRepository;

class EmployeeFinanceService
{
    public function __construct(

        protected WithdrawalRepository $withdrawals,

        protected EmployeeDebtRepository $debts,

        protected ShiftService $shiftService

    ){}

   public function withdraw(array $data)
{
    $withdrawal =
        $this->withdrawals
        ->create([

            'user_id' =>
                $data['user_id'],

            'shift_id' =>
                $data['shift_id'],

            'amount' =>
                $data['amount'],

            'reason' =>
                $data['reason']

        ]);

    $debt =
        $this->debts
        ->create([

            'user_id'
                => $data['user_id'],

            'shift_id'
                => $data['shift_id'],

            'amount'
                => $data['amount'],

            'reason'
                => $data['reason']

        ]);

    $this->shiftService
        ->registerWithdrawal(

            $data['amount'],

            $withdrawal->shift

        );

    return [

        'withdrawal' =>
            $withdrawal,

        'debt' =>
            $debt

    ];
}
}