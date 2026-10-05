<?php

namespace App\Services;

use App\Models\Salary;
use App\Models\User;
use App\Models\Expense;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    /*
    |--------------------------------------------------------------------------
    | Create Monthly Salary
    |--------------------------------------------------------------------------
    */

    public function create(array $data): Salary
    {
        return DB::transaction(function () use ($data) {

            $exists = Salary::where('user_id', $data['user_id'])
                ->where('month', $data['month'])
                ->where('year', $data['year'])
                ->first();

            if ($exists) {

                throw new \Exception(
                    'تم إنشاء راتب هذا الموظف لهذا الشهر مسبقاً.'
                );

            }

            $employee = User::findOrFail(

                $data['user_id']

            );

            $basic = $employee->salary;

            $allowances = $data['allowances'] ?? 0;

            $deductions = $data['deductions'] ?? 0;

            $net =

                $basic

                +

                $allowances

                -

                $deductions;

            return Salary::create([

                'user_id' => $data['user_id'],

                'month' => $data['month'],

                'year' => $data['year'],

                'basic_salary' => $basic,

                'allowances' => $allowances,

                'deductions' => $deductions,

                'net_salary' => $net,

                'status' => 'pending'

            ]);

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Pay Salary
    |--------------------------------------------------------------------------
    */

    public function pay(Salary $salary, array $data): Salary
    {
        return DB::transaction(function () use ($salary, $data) {
            if ($salary->status === 'paid') {
                throw new \Exception('تم صرف الراتب مسبقاً.');
            }

            // ✅ إزالة السطر المكرر
            $shift = Shift::where('branch_id', auth()->user()->branch_id)
                ->where('status', 'open')
                ->first();

            Expense::create([
                'user_id'         => auth()->id(),
                'shift_id'        => $shift?->id,
                'amount'          => $salary->net_salary,
                'title'           => 'راتب شهر ' . $salary->month . '/' . $salary->year,
                'category'        => 'salary',
                'payment_method'  => $data['payment_method'],
                'bank_name'       => $data['bank_name'] ?? null,
                'bank_reference'  => $data['bank_reference'] ?? null,
                'notes'           => $data['notes'] ?? null,
            ]);

            $salary->update([
                'status'          => 'paid',
                'paid_at'         => now(),
                'payment_method'  => $data['payment_method'],
                'bank_name'       => $data['bank_name'] ?? null,
                'bank_reference'  => $data['bank_reference'] ?? null,
                'notes'           => $data['notes'] ?? null,
            ]);

            return $salary->fresh()->load('user');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update Salary
    |--------------------------------------------------------------------------
    */

    public function update(Salary $salary, array $data): Salary
    {
        if (

            $salary->status === 'paid'

        ) {

            throw new \Exception(

                'لا يمكن تعديل راتب تم صرفه.'

            );

        }

        $salary->allowances =

            $data['allowances'];

        $salary->deductions =

            $data['deductions'];

        $salary->net_salary =

            $salary->basic_salary

            +

            $salary->allowances

            -

            $salary->deductions;

        $salary->save();

        return $salary->fresh();
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(Salary $salary): void
    {
        if (

            $salary->status === 'paid'

        ) {

            throw new \Exception(

                'لا يمكن حذف راتب تم صرفه.'

            );

        }

        $salary->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard()
    {
        return [

            'total' =>

                Salary::sum(

                    'net_salary'

                ),

            'paid' =>

                Salary::where(

                    'status',

                    'paid'

                )->sum(

                    'net_salary'

                ),

            'pending' =>

                Salary::where(

                    'status',

                    'pending'

                )->sum(

                    'net_salary'

                )

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Monthly Salaries
    |--------------------------------------------------------------------------
    */

    public function generateMonthlySalaries(int $month, int $year): array
    {
        return DB::transaction(function () use ($month, $year) {
            $created = [];

            // ✅ استخدام scope أو استعلام واضح
            $employees = User::where('role', 'cashier')
                ->where('is_active', true)
                ->get();

            foreach ($employees as $employee) {
                $exists = Salary::where('user_id', $employee->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $salary = Salary::create([
                    'user_id'       => $employee->id,
                    'month'         => $month,
                    'year'          => $year,
                    'basic_salary'  => $employee->salary,
                    'allowances'    => 0,
                    'deductions'    => 0,
                    'net_salary'    => $employee->salary,
                    'status'        => 'pending',
                ]);

                // ✅ أضف العنصر إلى المصفوفة
                $created[] = $salary;
            }

            return $created;
        });
    }
}