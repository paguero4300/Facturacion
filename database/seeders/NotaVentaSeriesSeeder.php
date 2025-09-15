<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\DocumentSeries;

class NotaVentaSeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las empresas
        $companies = Company::all();

        foreach ($companies as $company) {
            // Verificar si ya existe una serie NV para esta empresa
            $existingSeries = DocumentSeries::where('company_id', $company->id)
                ->where('document_type', '09')
                ->where('series', 'NV01')
                ->first();

            if (!$existingSeries) {
                DocumentSeries::create([
                    'company_id' => $company->id,
                    'document_type' => '09', // Nota de Venta
                    'series' => 'NV01',
                    'current_number' => 1,
                    'status' => 'active',
                    'description' => 'Serie para Notas de Venta - Uso Interno',
                ]);

                $this->command->info("Serie NV01 creada para empresa: {$company->business_name}");
            } else {
                $this->command->info("Serie NV01 ya existe para empresa: {$company->business_name}");
            }
        }
    }
}