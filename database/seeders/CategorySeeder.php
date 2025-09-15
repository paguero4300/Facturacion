<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Company;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $companies = Company::all();

        $categories = [
            [
                'name' => 'Electrónicos',
                'description' => 'Productos electrónicos y tecnológicos',
                'color' => '#3B82F6',
                'icon' => 'heroicon-o-computer-desktop',
            ],
            [
                'name' => 'Ropa y Accesorios',
                'description' => 'Vestimenta y accesorios de moda',
                'color' => '#EC4899',
                'icon' => 'heroicon-o-sparkles',
            ],
            [
                'name' => 'Hogar y Jardín',
                'description' => 'Productos para el hogar y jardín',
                'color' => '#10B981',
                'icon' => 'heroicon-o-home',
            ],
            [
                'name' => 'Deportes',
                'description' => 'Artículos deportivos y fitness',
                'color' => '#F59E0B',
                'icon' => 'heroicon-o-trophy',
            ],
            [
                'name' => 'Alimentación',
                'description' => 'Productos alimenticios y bebidas',
                'color' => '#EF4444',
                'icon' => 'heroicon-o-cake',
            ],
        ];

        foreach ($companies as $company) {
            foreach ($categories as $categoryData) {
                Category::create([
                    'company_id' => $company->id,
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'color' => $categoryData['color'],
                    'icon' => $categoryData['icon'],
                    'status' => true,
                    'created_by' => 1, // Assuming user ID 1 exists
                ]);
            }
        }
    }
}