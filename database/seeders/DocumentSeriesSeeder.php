<?php

namespace Database\Seeders;

use App\Models\DocumentSeries;
use Illuminate\Database\Seeder;

class DocumentSeriesSeeder extends Seeder
{
    public function run(): void
    {
        $series = [
            [
                'company_id' => 1,
                'document_type' => '01', // Factura
                'series' => 'F001',
                'description' => 'Facturas Electrónicas',
                'current_number' => 1,
                'initial_number' => 1,
                'final_number' => 99999999,
                'is_default' => true,
                'is_electronic' => true,
                'status' => 'active',
            ],
            [
                'company_id' => 1,
                'document_type' => '03', // Boleta
                'series' => 'B001',
                'description' => 'Boletas de Venta Electrónicas',
                'current_number' => 1,
                'initial_number' => 1,
                'final_number' => 99999999,
                'is_default' => true,
                'is_electronic' => true,
                'status' => 'active',
            ],
            [
                'company_id' => 1,
                'document_type' => '09', // Nota de Venta
                'series' => 'NV01',
                'description' => 'Notas de Venta',
                'current_number' => 1,
                'initial_number' => 1,
                'final_number' => 99999999,
                'is_default' => true,
                'is_electronic' => false,
                'status' => 'active',
            ],
        ];

        foreach ($series as $serie) {
            DocumentSeries::updateOrCreate(
                [
                    'company_id' => $serie['company_id'],
                    'document_type' => $serie['document_type'],
                    'series' => $serie['series'],
                ],
                $serie
            );
        }
    }
}