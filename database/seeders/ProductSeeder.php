<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = DB::table('companies')->where('ruc', '20613251988')->first()->id;

        // Producto gravado
        DB::table('products')->insert([
            'company_id' => $companyId,
            'code' => 'PROD001',
            'name' => 'PRODUCTO DE EJEMPLO',
            'product_type' => 'product',
            'unit_code' => 'NIU',
            'unit_price' => 50.00,
            'sale_price' => 50.00,
            'tax_type' => '10',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Servicio gravado
        DB::table('products')->insert([
            'company_id' => $companyId,
            'code' => 'SERV001',
            'name' => 'DESARROLLO DE SOFTWARE',
            'product_type' => 'service',
            'unit_code' => 'ZZ',
            'unit_description' => 'SERVICIO',
            'unit_price' => 800.00,
            'sale_price' => 800.00,
            'tax_type' => '10',
            'track_inventory' => false,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Consultoría por horas
        DB::table('products')->insert([
            'company_id' => $companyId,
            'code' => 'CONS001',
            'name' => 'CONSULTORIA TECNICA',
            'product_type' => 'service',
            'unit_code' => 'HUR',
            'unit_description' => 'HORA',
            'unit_price' => 120.00,
            'sale_price' => 120.00,
            'tax_type' => '10',
            'track_inventory' => false,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Producto exonerado
        DB::table('products')->insert([
            'company_id' => $companyId,
            'code' => 'LIBRO001',
            'name' => 'LIBRO EDUCATIVO',
            'product_type' => 'product',
            'unit_code' => 'NIU',
            'unit_price' => 25.00,
            'sale_price' => 25.00,
            'tax_type' => '20',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}