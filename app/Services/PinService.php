<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PinService
{
    public function isEnabled(): bool
    {
        return (bool) Setting::get('pin.enabled', true);
    }

    public function shouldRequireFor(string $action): bool
    {
        if (!$this->isEnabled()) return false;

        $map = [
            'shift_open'   => 'pin.on_shift_open',
            'shift_close'  => 'pin.on_shift_close',
            'withdraw'     => 'pin.on_withdraw',
            'void_sale'    => 'pin.on_void_sale',
            'price_change' => 'pin.on_price_change',
            'every_sale'   => 'pin.on_every_sale',
            'expense'      => 'pin.on_expense',         // ✅ جديد
            'debt_payment' => 'pin.on_debt_payment', 
        ];

        $key = $map[$action] ?? null;
        return $key ? (bool) Setting::get($key, false) : false;
    }

    /* ============================================================
       محاولات التحقق — حماية من brute force
       ============================================================ */
    protected function attemptsKey(int $userId): string
    {
        return "pin_attempts:user:{$userId}";
    }

    protected function lockoutKey(int $userId): string
    {
        return "pin_lockout:user:{$userId}";
    }

    public function isLocked(int $userId): bool
    {
        return Cache::has($this->lockoutKey($userId));
    }

    public function lockoutRemaining(int $userId): int
    {
        return (int) Cache::get($this->lockoutKey($userId), 0);
    }

    public function recordFailure(int $userId): array
    {
        $max = (int) Setting::get('pin.max_attempts', 5);
        $lockoutMinutes = (int) Setting::get('pin.lockout_minutes', 5);

        $attempts = (int) Cache::get($this->attemptsKey($userId), 0) + 1;
        Cache::put($this->attemptsKey($userId), $attempts, now()->addHours(1));

        if ($attempts >= $max) {
            Cache::put($this->lockoutKey($userId), $lockoutMinutes * 60, now()->addMinutes($lockoutMinutes));
            Cache::forget($this->attemptsKey($userId));
            return ['locked' => true, 'attempts' => $attempts];
        }

        return ['locked' => false, 'attempts' => $attempts, 'remaining' => $max - $attempts];
    }

    public function clearAttempts(int $userId): void
    {
        Cache::forget($this->attemptsKey($userId));
        Cache::forget($this->lockoutKey($userId));
    }

    /* ============================================================
       التحقق الرئيسي
       ============================================================ */
    public function verify(User $user, string $pin): array
    {
        if (!$this->isEnabled()) {
            return ['valid' => true, 'reason' => 'disabled'];
        }

        if ($this->isLocked($user->id)) {
            $minutes = ceil($this->lockoutRemaining($user->id) / 60);
            return [
                'valid'   => false,
                'reason'  => 'locked',
                'message' => "تم قفل الحساب. حاول بعد {$minutes} دقيقة",
            ];
        }

        if (!$user->hasPin()) {
            return [
                'valid'   => false,
                'reason'  => 'no_pin',
                'message' => 'لم يتم تعيين PIN لهذا المستخدم',
            ];
        }

        if (!$user->verifyPin($pin)) {
            $result = $this->recordFailure($user->id);

            if ($result['locked']) {
                return [
                    'valid'   => false,
                    'reason'  => 'locked',
                    'message' => 'تم قفل الحساب لتجاوز الحد الأقصى للمحاولات',
                ];
            }

            return [
                'valid'     => false,
                'reason'    => 'wrong',
                'message'   => 'PIN غير صحيح',
                'remaining' => $result['remaining'] ?? 0,
            ];
        }

        $this->clearAttempts($user->id);
        return ['valid' => true];
    }
}