<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EmployeeFinanceService;

class EmployeeFinanceController extends Controller
{
    /**
     * Withdraw money from employee finance / shift.
     */
    public function withdraw(
        Request $request,
        EmployeeFinanceService $service
    ) {
        return $service->withdraw(
            $request->all()
        );
    }
}