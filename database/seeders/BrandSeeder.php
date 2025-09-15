<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Company;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $companies = Company::all();

        $brands = [
            [
                'name' => 'Samsung',
                'description' => 'Tecnología e innovación coreana',
                'website' => 'https://www.samsung.com',
            ],
            [
                'name' => 'Apple',
                'description' => 'Productos premium de tecnología',
                'website' => 'https://www.apple.com',
            ],
            [
                'name' => 'Nike',
                'description' => 'Marca líder en deportes',
                'website' => 'https://www.nike.com',
            ],
            [
                'name' => 'Adidas',
                'description' => 'Equipamiento deportivo de calidad',
                'website' => 'https://www.adidas.com',
            ],
            [
                'name' => 'Sony',
                'description' => 'Entretenimiento y electrónicos',
                'website' => 'https://www.sony.com',
            ],
            [
                'name' => 'LG',
                'description' => 'Electrodomésticos y tecnología',
                'website' => 'https://www.lg.com',
            ],
        ];

        foreach ($companies as $company) {
            foreach ($brands as $brandData) {
                Brand::create([
                    'company_id' => $company->id,
                    'name' => $brandData['name'],
                    'description' => $brandData['description'],
                    'website' => $brandData['website'],
                    'status' => true,
                    'created_by' => 1, // Assuming user ID 1 exists
                ]);
            }
        }
    }
}