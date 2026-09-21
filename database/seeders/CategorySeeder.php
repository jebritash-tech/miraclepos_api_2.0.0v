<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'مسكنات وخافضات حرارة',
            'مضادات حيوية',
            'فيتامينات ومكملات غذائية',
            'أدوية الأطفال والرضع',
            'أدوية ضغط الدم والقلب',
            'أدوية السكري',
            'عناية بالبشرة والتجميل',
            'مضادات الحساسية والتهاب الجيوب',
            'أدوية الجهاز الهضمي ومضادات الحموضة',
            'قطرات ومراهم العين والأذن',
            'مستلزمات طبية وأجهزة قياس',
            'مضادات الالتهاب ومسكنات العظام'
        ];

        foreach ($categories as $name) {
            DB::table('categories')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
