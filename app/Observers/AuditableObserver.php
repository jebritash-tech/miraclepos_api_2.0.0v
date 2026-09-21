<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        AuditLogger::log(
            'created',
            $model,
            'إنشاء ' . class_basename($model),
            [],
            $this->sanitize($model->getAttributes())
        );
    }

    public function updated(Model $model): void
    {
        $old = [];
        $new = [];

        foreach ($model->getChanges() as $key => $value) {
            if (in_array($key, ['updated_at', 'remember_token'])) continue;
            $old[$key] = $model->getOriginal($key);
            $new[$key] = $value;
        }

        if (empty($old)) return; // لم يتغير شيء فعلاً

        $severity = $this->determineSeverity($model, array_keys($old));

        AuditLogger::log(
            'updated',
            $model,
            'تعديل ' . class_basename($model),
            $this->sanitize($old),
            $this->sanitize($new),
            $severity
        );
    }

    public function deleted(Model $model): void
    {
        AuditLogger::log(
            'deleted',
            $model,
            'حذف ' . class_basename($model),
            $this->sanitize($model->getOriginal()),
            [],
            'warning'
        );
    }

    /**
     * تحديد الخطورة حسب الحقول المتغيرة
     */
    protected function determineSeverity(Model $model, array $changedFields): string
    {
        $criticalFields = [
            'total_amount', 'profit_amount', 'price', 'sell_price', 'buy_price',
            'amount', 'salary', 'paid_amount', 'remaining_amount',
        ];

        foreach ($changedFields as $field) {
            if (in_array($field, $criticalFields)) {
                return 'critical';
            }
        }

        return 'info';
    }

    /**
     * إخفاء الحقول الحساسة
     */
    protected function sanitize(array $values): array
    {
        $hidden = ['password', 'pin_hash', 'remember_token', 'email_verified_at'];
        foreach ($hidden as $key) {
            unset($values[$key]);
        }
        return $values;
    }
}