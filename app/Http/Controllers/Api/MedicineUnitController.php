<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicineUnit;
class MedicineUnitController extends Controller
{
    //
    public function index($medicineId)
    {

        return MedicineUnit::with(

            'unit:id,name,symbol'

        )

        ->where(

            'medicine_id',

            $medicineId

        )

        ->orderBy(

            'factor'

        )

        ->get();

    }
    public function store(Request $request)
    {

        $request->validate([

            'medicine_id'=>'required',

            'unit_id'=>'required',

            'factor'=>'required|integer|min:1',

            'selling_price'=>'required|numeric|min:0',

            'barcode'=>'nullable'

        ]);

        return MedicineUnit::create(

            $request->all()

        );

    }
    public function update(Request $request,MedicineUnit $medicineUnit)
    {

        $medicineUnit->update(

            $request->all()

        );

        return $medicineUnit;

    }
    public function destroy(MedicineUnit $medicineUnit)
    {

        $medicineUnit->delete();

        return response()->json([

            'success'=>true

        ]);

    }
}
