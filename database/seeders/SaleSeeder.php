<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        foreach(range(1,100) as $i){

            $date = now()
                ->subDays(rand(0,365))
                ->setHour(rand(8,22));

            $saleId = DB::table('sales')
                ->insertGetId([

                    'branch_id' => 1,

                    'user_id' => rand(1,2),

                    'total_amount' => rand(1000,50000),

                    'profit_amount' => rand(300,12000),

                    'payment_method' => collect([
                        'cash',
                        'card'
                    ])->random(),

                    'created_at' => $date,

                    'updated_at' => $date

                ]);

            foreach(range(1,rand(1,5)) as $item){

                DB::table('sale_items')->insert([

                    'sale_id' => $saleId,

                    'medicine_batch_id' => rand(1,15),

                    'quantity' => rand(1,5),

                    'price' => rand(200,5000),

                    'profit' => rand(50,1000),

                    'created_at' => $date,

                    'updated_at' => $date

                ]);

            }

        }
    }
}
