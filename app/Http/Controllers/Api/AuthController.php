<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الاعتماد هذه غير صحيحة.'],
            ]);
        }

        // إنشاء التوكن
        $token = $user->createToken('pos-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('branch') // جلب بيانات المستخدم مع الفرع
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'غير مصرح'], 401);
        }

        return $user->load('branch');
    }
}