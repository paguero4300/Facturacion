<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->insert([
            'ruc' => '20613251988',
            'business_name' => 'GREEN SAC',
            'commercial_name' => 'GREEN',
            'address' => 'Av. Villa Nueva 221',
            'district' => 'LIMA',
            'province' => 'LIMA',
            'department' => 'LIMA',
            'ubigeo' => '150101',
            'tax_regime' => 'GENERAL',
            'ose_provider' => 'qpse',
            'ose_username' => 'SHTQGRTS',
            'ose_password' => 'A9M0SP9F',
            'ose_endpoint' => 'https://demo-cpe.qpse.pe',
            'sunat_production' => false, // Ambiente demo
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}