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


/*
|--------------------------------------------------------------------------
| Reset Core Data — إعادة تعيين البيانات الأساسية فقط
|--------------------------------------------------------------------------
| ✅ يعمل migrate:fresh (يحذف كل الجداول ويعيد إنشاءها)
| ✅ يزرع البيانات الأساسية فقط: الفروع، الوحدات، التصنيفات،
|    المستخدمين، قواعد التسعير، الإعدادات
| ❌ لا يزرع الأدوية (MedicinesSeeder لا يُستدعى)
|
| ⚠️ تحذير: هذا الراوت يحذف كل البيانات (بما فيها الأدوية والمبيعات)
|    لكنه لا يعيد زرع الأدوية — ستكون قاعدة البيانات فارغة من الأدوية
|    بعد التشغيل. استخدمه فقط في بيئات التطوير أو عند بداية نظيفة.
|
| الاستخدام: افتح في المتصفح
|   https://your-domain.com/reset-core-data
|--------------------------------------------------------------------------
*/
Route::get('/reset-core-data', function () {
    try {
        $results = [];

        /*
        |------------------------------------------------------------------
        | 1. migrate:fresh — حذف كل الجداول وإعادة إنشاءها
        |------------------------------------------------------------------
        */
        Artisan::call('migrate:fresh', [
            '--force' => true,
        ]);

        $results['migrate'] = [
            'success' => true,
            'output'  => Artisan::output(),
        ];

        /*
        |------------------------------------------------------------------
        | 2. زرع البيانات الأساسية فقط (CoreDataSeeder)
        |------------------------------------------------------------------
        | ⚠️ ملاحظة: لا نستخدم db:seed بدون --class لأنه سيشغّل
        |    DatabaseSeeder الذي قد يستدعي MedicinesSeeder أيضاً.
        |    لذلك نحدد CoreDataSeeder بشكل صريح.
        |------------------------------------------------------------------
        */
        Artisan::call('db:seed', [
            '--class' => 'CoreDataSeeder',
            '--force' => true,
        ]);

        $results['seed'] = [
            'success' => true,
            'output'  => Artisan::output(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة تعيين البيانات الأساسية بنجاح',
            'note'    => 'لم يتم زرع الأدوية — قاعدة البيانات فارغة من الأدوية',
            'details' => $results,
            'credentials' => [
                'admin'   => 'admin@miraclepos.test / password',
                'cashier' => 'cashier@miraclepos.test / password',
            ],
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'فشل إعادة التعيين: ' . $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => config('app.debug') ? $e->getTraceAsString() : null,
        ], 500);
    }
});
