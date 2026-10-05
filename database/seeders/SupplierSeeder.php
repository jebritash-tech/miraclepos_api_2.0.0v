<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [

            'شركة الشفاء',
            'شركة الجزيرة',
            'شركة ابن سينا',
            'شركة الخليج',
            'شركة الحكمة'

        ];

        foreach ($suppliers as $supplier) {

            DB::table('suppliers')->insert([
                'name' => $supplier,
                'phone' => "0968390877",
                'created_at' => now(),
                'updated_at' => now()
            ]);

        }
    }
}
