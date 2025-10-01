<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\DocumentSeries;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'series' => 'NV02',
            'number' => $this->faker->numberBetween(1, 9999),
            'full_number' => 'NV02-' . str_pad($this->faker->numberBetween(1, 9999), 8, '0', STR_PAD_LEFT),
            'document_type' => '00',
            'issue_date' => now()->toDateString(),
            'issue_time' => now()->toTimeString(),
            'currency_code' => 'PEN',
            'client_document_type' => '1',
            'client_document_number' => $this->faker->numerify('########'),
            'client_business_name' => $this->faker->name(),
            'client_address' => $this->faker->address(),
            'client_email' => $this->faker->email(),
            'subtotal' => 100.00,
            'total_amount' => 100.00,
            'payment_method' => 'cash',
            'status' => 'draft',
        ];
    }
}
