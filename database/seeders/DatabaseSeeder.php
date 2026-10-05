<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoreDataSeeder::class,      // 1. البيانات الأساسية (الفروع، الوحدات، التصنيفات، المستخدمين، الإعدادات)
            MedicinesSeeder::class,     // 2. كتالوج الأدوية (يعتمد على الوحدات من الخطوة 1)
        ]);
    }
}
