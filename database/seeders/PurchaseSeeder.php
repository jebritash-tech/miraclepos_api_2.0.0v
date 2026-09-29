<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach(range(1,100) as $i){

            $purchaseId = DB::table('purchases')
                ->insertGetId([

                    'supplier_id' => rand(1,5),

                    'total_amount' => rand(50000,300000),

                    'created_at' => now()
                        ->subDays(rand(1,365)),

                    'updated_at' => now()

                ]);

            foreach(range(1,rand(2,6)) as $item){

                DB::table('purchase_items')->insert([

                    'purchase_id' => $purchaseId,

                    'medicine_batch_id' => rand(1,15),

                    'quantity' => rand(10,100),

                    'price' => rand(100,2000),

                    'created_at' => now(),

                    'updated_at' => now()

                ]);

            }

        }
    }
}
