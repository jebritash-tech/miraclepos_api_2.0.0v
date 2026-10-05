<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PinService;
use Illuminate\Http\Request;

class PinController extends Controller
{
    protected PinService $pinService;

    public function __construct(PinService $pinService)
    {
        $this->pinService = $pinService;
    }

    /* POST /api/pin/set */
    public function setPin(Request $request)
    {
        $request->validate([
            'pin'     => 'required|string|min:4|max:8',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $current = $request->user();
        $userId = $request->input('user_id');

        if ($userId && $userId != $current->id && $current->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $target = $userId ? User::findOrFail($userId) : $current;

        $target->setPin($request->pin);
        $target->save();

        return response()->json([
            'message' => 'تم تعيين PIN بنجاح',
            'user'    => ['id' => $target->id, 'name' => $target->name],
        ]);
    }

    /* POST /api/pin/remove */
    public function removePin(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $current = $request->user();
        if ($request->user_id != $current->id && $current->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $target = User::findOrFail($request->user_id);
        $target->clearPin();
        $target->save();

        return response()->json(['message' => 'تم إزالة PIN']);
    }

    /* POST /api/pin/verify */
    public function verify(Request $request)
    {
        $request->validate([
            'pin'     => 'required|string|min:4|max:8',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $userId = $request->user_id ?: $request->user()->id;
        $user = User::findOrFail($userId);

        $result = $this->pinService->verify($user, $request->pin);

        if (!$result['valid']) {
            // ✅ تسجيل المحاولة الفاشلة في Audit Log
            $reason = $result['reason'] ?? 'wrong';
            $description = match ($reason) {
                'locked'   => "محاولة PIN على حساب مقفل — {$user->name}",
                'no_pin'   => "محاولة PIN على حساب بدون PIN معيّن — {$user->name}",
                'wrong'    => "محاولة PIN فاشلة — {$user->name}" . 
                            (isset($result['remaining']) ? " (متبقي {$result['remaining']} محاولات)" : ''),
                default    => "محاولة PIN فاشلة — {$user->name}",
            };

            AuditLogger::log(
                'pin_failed',
                $user,
                $description,
                [],
                ['reason' => $reason, 'attempts_left' => $result['remaining'] ?? null],
                'warning',  // ← تنبيه أمني
                [
                    'branch_id' => $user->branch_id,
                ]
            );

            return response()->json($result, $reason === 'locked' ? 423 : 401);
        }

        // ✅ تسجيل النجاح
        AuditLogger::log(
            'pin_verified',
            $user,
            "تحقق PIN ناجح — {$user->name}",
            [],
            [],
            'info',
            [
                'branch_id' => $user->branch_id,
            ]
        );

        return response()->json([
            'valid' => true,
            'user'  => [
                'id'   => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }

    /* GET /api/pin/status — حالة PIN لكل المستخدمين */
    public function status(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $users = User::where('is_active', true)
            ->with('branch:id,name')
            ->get(['id', 'name', 'role', 'branch_id', 'pin_hash', 'pin_set_at'])
            ->map(function ($u) {
                // ✅ تحويل آمن: سواء كان Carbon أو string
                $pinSetAt = null;
                if (!empty($u->pin_set_at)) {
                    try {
                        $pinSetAt = \Carbon\Carbon::parse($u->pin_set_at)->toIso8601String();
                    } catch (\Throwable $e) {
                        $pinSetAt = null;
                    }
                }

                return [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'role'       => $u->role,
                    'branch'     => $u->branch?->name,
                    'has_pin'    => $u->hasPin(),
                    'pin_set_at' => $pinSetAt,
                ];
            });

        return response()->json(['users' => $users]);
    }
}