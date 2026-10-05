<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'user_role',
        'action', 'model_type', 'model_id',
        'description', 'old_values', 'new_values',
        'ip_address', 'user_agent',
        'branch_id', 'shift_id', 'severity',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============================================================
    // Scopes
    // ============================================================
    public function scopeCritical($q)
    {
        return $q->where('severity', 'critical');
    }

    public function scopeToday($q)
    {
        return $q->whereDate('created_at', today());
    }

    public function scopeForUser($q, $userId)
    {
        return $q->where('user_id', $userId);
    }
}