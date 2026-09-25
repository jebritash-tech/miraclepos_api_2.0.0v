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
Route::get('/migrate-fresh', function () {
    try {
        
        // 2. الحل: تنفيذ migrate فقط
        Artisan::call('migrate:fresh', [
            '--force' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'step' => 'migrate',
            'output' => Artisan::output(),
            'message' => 'تم migrate:fresh. الآن افتح /seed-core-data',
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

Route::get('/reset-core-data', function () {
    try {
        set_time_limit(300); // 5 دقائق
        
        // 1. قطع الاتصال مع المتصفح مبكراً
        // (لكن هذه الطريقة صعبة في Laravel بدون Response streaming)
        
        // 2. الحل: تنفيذ migrate فقط
        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--no-interaction' => true,
        ]);
        
        return response()->json([
            'success' => true,
            'step' => 'migrate',
            'output' => Artisan::output(),
            'message' => 'تم migrate:fresh. الآن افتح /seed-core-data',
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
