<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * ═══════════════════════════════════════════════════════════════
     * DatabaseSeeder — نقطة الدخول الرئيسية
     * ═══════════════════════════════════════════════════════════════
     * 
     * التشغيل:
     *   php artisan migrate:fresh --seed
     * 
     * أو:
     *   php artisan db:seed
     * ═══════════════════════════════════════════════════════════════
     */
    public function run(): void
    {
        $this->call([
            CoreDataSeeder::class,
        ]);

        // يمكن إضافة Seeders أخرى هنا لاحقاً
        // $this->call([
        //     DemoDataSeeder::class, // للبيئات التجريبية فقط
        // ]);
    }
}