<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class EnforceSessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $timeoutMinutes = (int) Setting::get('security.session_timeout_minutes', 720);
        if ($timeoutMinutes <= 0) return $next($request);

        $token = $user->currentAccessToken();
        if (!$token || !$token->last_used_at) {
            return $next($request);
        }

        // إذا انقضت المدة منذ آخر استخدام
        $minutesElapsed = now()->diffInMinutes($token->last_used_at);

        if ($minutesElapsed > $timeoutMinutes) {
            $token->delete();

            return response()->json([
                'message' => 'انتهت الجلسة، يرجى تسجيل الدخول من جديد',
                'code'    => 'SESSION_EXPIRED',
            ], 401);
        }

        return $next($request);
    }
}