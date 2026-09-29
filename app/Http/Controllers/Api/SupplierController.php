<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return response()->json(Supplier::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'nullable',
            'email' => 'nullable|email'
        ]);
        $supplier = Supplier::create($data);
        return response()->json([
            'message' => 'تم إضافة المورد بنجاح',
            'supplier' => $supplier,
            'suppliers' => Supplier::all()
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'nullable',
            'email' => 'nullable|email'
        ]);
        $supplier->update($data);
        return response()->json([
            'message' => 'تم تحديث المورد بنجاح',
            'supplier' => $supplier->fresh(),
            'suppliers' => Supplier::all()
        ]);
    }

    public function destroy($id)
    {
        Supplier::destroy($id);
        return response()->json([
            'message' => 'تم حذف المورد بنجاح',
            'suppliers' => Supplier::all()
        ]);
    }
}