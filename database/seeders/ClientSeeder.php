<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = DB::table('companies')->where('ruc', '20613251988')->first()->id;

        // Cliente con RUC
        DB::table('clients')->insert([
            'company_id' => $companyId,
            'document_type' => '6',
            'document_number' => '20000000001',
            'business_name' => 'EMPRESA CLIENTE SAC',
            'address' => 'Av. Ejemplo 123',
            'district' => 'LIMA',
            'province' => 'LIMA', 
            'department' => 'LIMA',
            'email' => 'cliente@empresa.com',
            'phone' => '01-1234567',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Cliente con DNI
        DB::table('clients')->insert([
            'company_id' => $companyId,
            'document_type' => '1',
            'document_number' => '12345678',
            'business_name' => 'JUAN PEREZ LOPEZ',
            'address' => 'Jr. Los Olivos 456',
            'district' => 'LIMA',
            'province' => 'LIMA',
            'department' => 'LIMA',
            'email' => 'juan.perez@email.com',
            'phone' => '987654321',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Cliente genérico para ventas rápidas
        DB::table('clients')->insert([
            'company_id' => $companyId,
            'document_type' => '0',
            'document_number' => '00000000',
            'business_name' => 'CLIENTE VARIOS',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}