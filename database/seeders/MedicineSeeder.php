<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = [

            'Panadol',
            'Augmentin',
            'Flagyl',
            'Brufen',
            'Cataflam',
            'Amoxil',
            'Ventolin',
            'Zinnat',
            'Vitamin C',
            'Paracetamol',
            'Omeprazole',
            'Diclofenac',
            'Aspirin',
            'Glucophage',
            'Insulin'

        ];

        foreach ($medicines as $index => $medicine) {

            DB::table('medicines')->insert([

                'category_id' => rand(1,7),

                'name' => $medicine,

                'scientific_name' => $medicine,

                'notes' => 'لا يوجد ملاحظات',

                'created_at' => now(),

                'updated_at' => now(),

            ]);

        }
    }
}
