<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        return response()->json(Branch::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255'
        ]);

        $branch = Branch::create($data);

        // إعادة القائمة الكاملة بعد الإضافة
        return response()->json([
            'message' => 'تم إضافة الفرع بنجاح',
            'branch' => $branch,
            'branches' => Branch::all() // إعادة القائمة الكاملة
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255'
        ]);

        $branch->update($data);

        // إعادة القائمة الكاملة بعد التحديث
        return response()->json([
            'message' => 'تم تحديث الفرع بنجاح',
            'branch' => $branch->fresh(), // reload from DB
            'branches' => Branch::all() // إعادة القائمة الكاملة
        ]);
    }

    public function destroy($id)
    {
        Branch::destroy($id);

        // إعادة القائمة الكاملة بعد الحذف
        return response()->json([
            'message' => 'تم حذف الفرع بنجاح',
            'branches' => Branch::all() // إعادة القائمة الكاملة
        ]);
    }
}