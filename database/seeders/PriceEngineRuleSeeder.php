<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceEngineRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        \App\Models\PriceEngineRule::insert([

            [
            'name'=>'ربح 30%',

            

            'type'=>'markup',

            'value'=>30,

            'priority'=>1,

            'apply_on'=>true,

            'is_active'=>true,

            'is_default'=>true

            ],

            [
            'name'=>'تقريب لأقرب 50',

            'code'=>'ROUND_50',

            'type'=>'rounding',

            'value'=>50,

            'priority'=>100,
            'is_active'=>false,
            'enabled'=>true

            ]

            ]);
    }
}
