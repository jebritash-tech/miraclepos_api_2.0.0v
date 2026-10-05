<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return User::with('branch')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:6',
            'salary' => 'nullable|numeric',
            'role' => 'required',
            'branch_id' => 'required|exists:branches,id'
        ]);

        // ✅ لا تستخدم bcrypt — الـ cast 'hashed' في User model يتولى ذلك
        $user = User::create($data);

        return response()->json([
            'message' => 'تم إضافة المستخدم بنجاح',
            'user' => $user->load('branch'),
            'users' => User::with('branch')->get()
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required',
            'email' => ['required', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:6',
            'salary' => 'nullable|numeric',
            'role' => 'required',
            'branch_id' => 'required|exists:branches,id'
        ]);

        // ✅ إذا لم تُدخَل كلمة مرور جديدة — احذف الحقل
        if (empty($data['password'])) {
            unset($data['password']);
        }
        // ✅ إذا أُدخلت — اتركها كما هي، الـ cast 'hashed' يتولى التشفير

        $passwordChanged = !empty($data['password']);

        $user->update($data);

        // ✅ اختياري: احذف التوكنات عند تغيير كلمة المرور
        //    لضمان أن المستخدم يُطرد من الأجهزة القديمة
        if ($passwordChanged) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'تم تحديث المستخدم بنجاح',
            'user' => $user->load('branch'),
            'users' => User::with('branch')->get()
        ]);
    }

    public function destroy($id)
    {
        User::destroy($id);

        return response()->json([
            'message' => 'تم حذف المستخدم بنجاح',
            'users' => User::with('branch')->get()
        ]);
    }

    public function currentUser()
    {
        return auth()->user()->load('branch');
    }
}