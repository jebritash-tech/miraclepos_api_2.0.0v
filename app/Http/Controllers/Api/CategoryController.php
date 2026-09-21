<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required']);
        $category = Category::create($data);
        return response()->json([
            'message' => 'تم إضافة التصنيف بنجاح',
            'category' => $category,
            'categories' => Category::all()
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validate(['name' => 'required|unique:categories,name,'.$id]);
        $category->update($data);
        return response()->json([
            'message' => 'تم تحديث التصنيف بنجاح',
            'category' => $category->fresh(),
            'categories' => Category::all()
        ]);
    }

    public function destroy($id)
    {
        Category::destroy($id);
        return response()->json([
            'message' => 'تم حذف التصنيف بنجاح',
            'categories' => Category::all()
        ]);
    }
}