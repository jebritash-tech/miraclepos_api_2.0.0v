<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    // 1. Sends the reset link via email
    public function sendResetLink(Request $request)
    {
        set_time_limit(120);
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'تم إرسال الرابط إلى بريدك'])
            : response()->json(['message' => 'حدث خطأ أثناء الإرسال'], 400);
    }

    // 2. Processes the new password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'تم تغيير كلمة المرور بنجاح'])
            : response()->json(['message' => 'الرابط غير صالح أو انتهت صلاحيته'], 400);
    }
}
