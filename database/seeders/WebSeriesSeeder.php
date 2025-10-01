<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentSeries;
use App\Models\Company;

class WebSeriesSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener company_id dinámicamente
        $company = Company::first();

        if (!$company) {
            $this->command->error('No se encontró ninguna compañía. Ejecuta CompanySeeder primero.');
            return;
        }

        // Crear serie NV02 para pedidos web
        DocumentSeries::firstOrCreate(
            [
                'series' => 'NV02',
                'company_id' => $company->id
            ],
            [
                'document_type' => '00',
                'description' => 'Notas de Venta - Pedidos Tienda Online',
                'current_number' => 1,
                'initial_number' => 1,
                'final_number' => 99999999,
                'is_default' => false,
                'is_electronic' => false,
                'status' => 'active',
            ]
        );

        $this->command->info("Serie NV02 creada exitosamente para {$company->business_name}!");
    }
}
