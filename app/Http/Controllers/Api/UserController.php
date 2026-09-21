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
        $data['password'] = bcrypt($data['password']);
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
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
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