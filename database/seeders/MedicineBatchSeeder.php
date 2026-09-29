<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class MedicineBatchSeeder extends Seeder
{
    public function run(): void
    {
        foreach(range(1,15) as $medicineId){

            DB::table('medicine_batches')->insert([

                'medicine_id' => $medicineId,

                'branch_id' => 1,

                'batch_number' => 'B'.rand(1000,9999),

                'expiry_date' => now()
                    ->addMonths(rand(1,24)),

                'quantity' => rand(20,500),

                'selling_price' => rand(200,5000),

                'cost_price' => rand(100,4000),

                'min_stock' => 10,

                'created_at' => now(),

                'updated_at' => now()

            ]);

        }
    }
}
