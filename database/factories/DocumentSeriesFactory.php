<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentSeries>
 */
class DocumentSeriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'series' => 'NV02',
            'document_type' => '00',
            'description' => 'Nota de Venta Web',
            'current_number' => 0,
            'is_active' => true,
        ];
    }
}
