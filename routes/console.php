<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\BackupService;
use Illuminate\Support\Facades\Log;

Schedule::call(function () {
    try {
        $service = new BackupService();
        $result = $service->create('auto');
        Log::info('✅ Auto backup succeeded', $result);
    } catch (\Throwable $e) {
        Log::error('❌ Auto backup failed: ' . $e->getMessage());
        // TODO: إرسال إشعار SMS/Email للمدير
    }
})->dailyAt('23:59')->name('daily-backup');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
