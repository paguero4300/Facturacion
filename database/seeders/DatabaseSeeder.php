<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Orden correcto de dependencias
        $this->call([
            UserSeeder::class,           // Primero usuarios
            CompanySeeder::class,        // Luego empresa
            DocumentSeriesSeeder::class, // Series dependen de empresa
            ClientSeeder::class,         // Clientes dependen de empresa
            ProductSeeder::class,        // Productos dependen de empresa
        ]);
    }
}
