<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class DefaultClientSeeder extends Seeder
{
    public function run(): void
    {
        // Cliente genérico para boletas y notas de venta
        Client::firstOrCreate(
            [
                'document_type' => '0',
                'document_number' => '00000000',
            ],
            [
                'company_id' => 1,
                'business_name' => 'CLIENTE VARIOS',
                'commercial_name' => 'CLIENTE VARIOS',
                'address' => '-',
                'district' => '-',
                'province' => '-',
                'department' => '-',
                'phone' => '-',
                'email' => null,
                'client_type' => 'regular',
                'status' => 'active',
            ]
        );
    }
}