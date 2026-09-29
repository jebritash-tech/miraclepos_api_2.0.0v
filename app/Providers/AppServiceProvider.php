<?php

namespace App\Providers;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;
use App\Observers\AuditableObserver;
use App\Models\{
    Sale, Refund, Purchase, Medicine, MedicineBatch,
    User, Expense, Withdrawal, Debt, Salary, Shift
};
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ تسجيل Observer تلقائي على الجداول الحساسة
        $auditableModels = [
            Sale::class,
            Refund::class,
            Purchase::class,
            Medicine::class,
            MedicineBatch::class,
            User::class,
            Expense::class,
            Withdrawal::class,
            Debt::class,
            Salary::class,
        ];

        foreach ($auditableModels as $model) {
            $model::observe(AuditableObserver::class);
        }
        // This sets the URL that the reset password email will use
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return "https://miraclepos-frontend-dev-main.test/reset-password.html?token={$token}&email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
