<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use App\Services\SalaryService;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    protected SalaryService $salaryService;

    public function __construct(
        SalaryService $salaryService
    ) {
        $this->salaryService = $salaryService;
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard()
    {
        return response()->json(

            $this->salaryService->dashboard()

        );
    }

    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Salary::with([

            'user'

        ]);

        if (

            $request->filled('month')

        ) {

            $query->where(

                'month',

                $request->month

            );

        }

        if (

            $request->filled('year')

        ) {

            $query->where(

                'year',

                $request->year

            );

        }

        if (

            $request->filled('status')

        ) {

            $query->where(

                'status',

                $request->status

            );

        }

        return response()->json(

            $query

            ->latest()

            ->paginate(20)

        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Monthly Salary
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([

            'user_id' =>

                'required|exists:users,id',

            'month' =>

                'required|integer|min:1|max:12',

            'year' =>

                'required|integer',

            'allowances' =>

                'nullable|numeric',

            'deductions' =>

                'nullable|numeric'

        ]);

        $salary =

            $this->salaryService->create(

                $validated

            );

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الراتب بنجاح.',
                'salary' => $salary,
                'salaries' => $this->getFilteredSalaries($request) // دالة مساعدة
            ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Salary $salary
    ) {

        $validated =

            $request->validate([

                'allowances' =>

                    'required|numeric',

                'deductions' =>

                    'required|numeric'

            ]);

        $salary =

            $this->salaryService->update(

                $salary,

                $validated

            );

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الراتب.',
                'salary' => $salary,
                'salaries' => $this->getFilteredSalaries($request)
            ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Pay
    |--------------------------------------------------------------------------
    */

    public function pay(
        Request $request,
        Salary $salary
    ) {

        $validated =

            $request->validate([

                'payment_method' =>

                    'required|in:cash,bank',

                'bank_name' =>

                    'nullable|string',

                'bank_reference' =>

                    'nullable|string',

                'notes' =>

                    'nullable|string'

            ]);

        $salary =

            $this->salaryService->pay(

                $salary,

                $validated

            );

            return response()->json([
                'success' => true,
                'message' => 'تم صرف الراتب.',
                'salary' => $salary,
                'salaries' => $this->getFilteredSalaries($request)
            ]);

    }

    /*
    |--------------------------------------------------------------------------
    | Generate Monthly Salaries
    |--------------------------------------------------------------------------
    */

    public function generate(
        Request $request
    ) {

        $validated = $request->validate([

            'month' =>

                'required|integer|min:1|max:12',

            'year' =>

                'required|integer'

        ]);

        $result =

            $this->salaryService

            ->generateMonthlySalaries(

                $validated['month'],

                $validated['year']

            );

            return response()->json([
                'success' => true,
                'count' => count($result),
                'message' => 'تم إنشاء ' . count($result) . ' راتب.',
                'salaries' => $this->getFilteredSalaries($request)
            ]);

    }

    private function getFilteredSalaries(Request $request)
    {
        $query = Salary::with('user');
        if ($request->filled('month')) $query->where('month', $request->month);
        if ($request->filled('year')) $query->where('year', $request->year);
        if ($request->filled('status')) $query->where('status', $request->status);
        return $query->latest()->paginate(20);
    }
    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Salary $salary
    ) {

        $this->salaryService->delete(

            $salary

        );

        return response()->json([

            'success' => true,

            'message' =>

                'تم حذف الراتب.'

        ]);

    }
}