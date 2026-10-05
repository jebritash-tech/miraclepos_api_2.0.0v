<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * تسجيل عملية في AuditLog
     */
    public static function log(
        string $action,
        ?Model $model = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        string $severity = 'info',
        array $context = []
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id'    => $user?->id,
            'user_name'  => $user?->name ?? 'نظام',
            'user_role'  => $user?->role,

            'action'     => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->getKey(),

            'description'=> $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,

            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 255),
            'branch_id'  => $context['branch_id']  ?? $user?->branch_id,
            'shift_id'   => $context['shift_id']   ?? null,
            'severity'   => $severity,
        ]);
    }

    /**
     * ✅ Bug fix #2: critical تقبل old/new/context بشكل صريح
     */
    public static function critical(
        string $action,
        ?Model $model = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        array $context = []
    ): AuditLog {
        return static::log(
            $action,
            $model,
            $description,
            $oldValues,
            $newValues,
            'critical',
            $context
        );
    }

    /**
     * ✅ Bug fix #2: warning تقبل old/new/context بشكل صريح
     */
    public static function warning(
        string $action,
        ?Model $model = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = [],
        array $context = []
    ): AuditLog {
        return static::log(
            $action,
            $model,
            $description,
            $oldValues,
            $newValues,
            'warning',
            $context
        );
    }
}