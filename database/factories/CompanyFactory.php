<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_name' => $this->faker->company(),
            'document_type' => '6',
            'document_number' => '20123456789',
            'address' => $this->faker->address(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
