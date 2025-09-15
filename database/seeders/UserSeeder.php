<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador
        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@green.pe',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario vendedor
        DB::table('users')->insert([
            'name' => 'Vendedor Demo',
            'email' => 'vendedor@green.pe',
            'email_verified_at' => now(),
            'password' => Hash::make('vendedor123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}