<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([

            [
                'branch_id' => 1,
                'name' => 'Admin',
                'email' => 'admin@pharmacy.test',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'salary' =>250000,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'branch_id' => 1,
                'name' => 'Cashier',
                'email' => 'cashier@pharmacy.test',
                'password' => Hash::make('123456'),
                'role' => 'cashier',
                'salary' =>250000,
                'created_at' => now(),
                'updated_at' => now(),
            ]

        ]);
    }
}
