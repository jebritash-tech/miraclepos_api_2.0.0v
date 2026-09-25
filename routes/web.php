<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\UnitController;

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Setup System — إعداد كامل (يشمل الأدوية)
|--------------------------------------------------------------------------
| ⚠️ خطر: يحذف كل الجداول ويعيد إنشاءها + يزرع كل البيانات بما فيها الأدوية
| استخدمه فقط في بداية المشروع
|--------------------------------------------------------------------------
*/
Route::get('/setup-system', function () {

    Artisan::call('migrate:fresh', [
        '--force' => true
    ]);

    Artisan::call('db:seed', [
        '--force' => true
    ]);

    return response()->json([
        'success' => true,
        'message' => 'System initialized successfully'
    ]);
});

Route::get('/migrate', function () {

    Artisan::call('migrate', [
        '--force' => true
    ]);

    return response()->json([
        'success' => true,
        'message' => 'System Has Created All Tables Successfully...Ready To Seed...'
    ]);
});


Route::get('/reset-core-data', function () {
    // ═══════════════════════════════════════════════════════
    // 0. إعدادات أساسية
    // ═══════════════════════════════════════════════════════
    set_time_limit(600);
    ignore_user_abort(true); // لا تتوقف لو أغلق المتصفح

    $log = [];
    $startTime = microtime(true);

    try {
        // ═══════════════════════════════════════════════════════
        // 1. منع التنفيذ المتوازي (Lock على مستوى التطبيق)
        // ═══════════════════════════════════════════════════════
        $lock = Cache::lock('reset-core-data', 600);
        
        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'error'   => 'عملية reset أخرى قيد التنفيذ بالفعل. انتظر قليلاً.',
            ], 429);
        }

        $log[] = '✅ تم الحصول على القفل التطبيقي';

        // ═══════════════════════════════════════════════════════
        // 2. قطع الاتصال الحالي وإعادة الاتصال (لتجنب أي locks قديمة)
        // ═══════════════════════════════════════════════════════
        DB::disconnect('pgsql');
        DB::reconnect('pgsql');
        $log[] = '🔄 تم إعادة الاتصال بقاعدة البيانات';

        // ═══════════════════════════════════════════════════════
        // 3. قتل جميع الجلسات الأخرى على قاعدة البيانات
        // ═══════════════════════════════════════════════════════
        $killed = DB::select("
            SELECT pg_terminate_backend(pid) AS killed, pid, state, query
            FROM pg_stat_activity
            WHERE datname = current_database()
              AND pid <> pg_backend_pid()
              AND state <> 'idle'
              AND backend_type = 'client backend'
        ");

        $killedCount = collect($killed)->where('killed', true)->count();
        $log[] = "🔪 تم إنهاء {$killedCount} جلسة نشطة";

        // ═══════════════════════════════════════════════════════
        // 4. إلغاء أي Autovacuum/Analyze معلق على الجداول
        // ═══════════════════════════════════════════════════════
        $canceled = DB::select("
            SELECT pg_cancel_backend(pid) AS canceled, pid, query
            FROM pg_stat_activity
            WHERE datname = current_database()
              AND pid <> pg_backend_pid()
              AND state = 'idle in transaction'
        ");
        $log[] = "⏹️ تم إلغاء " . count($canceled) . " معاملة خاملة";

        // ═══════════════════════════════════════════════════════
        // 5. إعادة الاتصال مرة أخرى بعد القتل
        // ═══════════════════════════════════════════════════════
        DB::disconnect('pgsql');
        DB::reconnect('pgsql');
        $log[] = '🔄 تم إعادة الاتصال بعد إنهاء الجلسات';

        // ═══════════════════════════════════════════════════════
        // 6. انتظار قصير لتحرير الـ Locks على مستوى PostgreSQL
        // ═══════════════════════════════════════════════════════
        sleep(2);

        // ═══════════════════════════════════════════════════════
        // 7. التحقق من عدم وجود Locks عالقة على الجداول
        // ═══════════════════════════════════════════════════════
        $remainingLocks = DB::select("
            SELECT l.locktype, l.relation::regclass AS table_name, l.mode, a.pid, a.state
            FROM pg_locks l
            JOIN pg_stat_activity a ON a.pid = l.pid
            WHERE l.database = (SELECT oid FROM pg_database WHERE datname = current_database())
              AND l.granted = true
              AND l.mode IN ('AccessExclusiveLock', 'ShareRowExclusiveLock', 'ExclusiveLock')
              AND a.pid <> pg_backend_pid()
        ");

        if (count($remainingLocks) > 0) {
            $log[] = "⚠️ لا تزال هناك " . count($remainingLocks) . " أقفال عالقة، محاولة إضافية...";
            
            // محاولة أخيرة: قتل كل شيء ما عدا اتصالنا
            DB::select("
                SELECT pg_terminate_backend(pid)
                FROM pg_stat_activity
                WHERE datname = current_database()
                  AND pid <> pg_backend_pid()
            ");
            sleep(3);
            DB::disconnect('pgsql');
            DB::reconnect('pgsql');
        }

        $log[] = '🔍 تم التحقق من نظافة الأقفال';

        // ═══════════════════════════════════════════════════════
        // 8. تعطيل الجلسات الجديدة مؤقتاً (اختياري - للأمان)
        //    عبر تغيير كلمة السر لنفسها مؤقتاً؟ لا — نتخطى هذه الخطوة
        //    لأنها خطيرة على Supabase Pooler.
        // ═══════════════════════════════════════════════════════

        // ═══════════════════════════════════════════════════════
        // 9. تنفيذ migrate:fresh
        // ═══════════════════════════════════════════════════════
        Artisan::call('migrate:fresh', [
            '--force'          => true,
            '--no-interaction' => true,
        ]);

        $migrateOutput = Artisan::output();
        $log[] = '✅ تم تنفيذ migrate:fresh';

        // ═══════════════════════════════════════════════════════
        // 10. (اختياري) تنفيذ seeders إن أردت
        // ═══════════════════════════════════════════════════════
        // Artisan::call('db:seed', ['--force' => true]);
        // $log[] = '✅ تم تنفيذ seeders';

        // ═══════════════════════════════════════════════════════
        // 11. تحرير القفل
        // ═══════════════════════════════════════════════════════
        $lock->release();

        $duration = round(microtime(true) - $startTime, 2);

        return response()->json([
            'success'  => true,
            'duration' => "{$duration}s",
            'steps'    => $log,
            'output'   => $migrateOutput,
            'message'  => 'تمت إعادة التهيئة بنجاح. الآن افتح /seed-core-data',
        ]);

    } catch (\Throwable $e) {
        // حرر القفل في حالة الخطأ
        if (isset($lock) && $lock) {
            try { $lock->release(); } catch (\Throwable $ignored) {}
        }

        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'steps'   => $log,
        ], 500);
    }
});
Route::get('/seed-core-data', function () {
    try {
        set_time_limit(300);
        
        Artisan::call('db:seed', [
            '--class' => 'CoreDataSeeder',
            '--force' => true,
            '--no-interaction' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'step' => 'seed',
            'output' => Artisan::output(),
            'message' => 'تم الزرع بنجاح',
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});
