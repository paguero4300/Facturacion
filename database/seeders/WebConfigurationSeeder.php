<?php

namespace Database\Seeders;

use App\Models\WebConfiguration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WebConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WebConfiguration::create([
            'company_id' => 1, // Asumiendo que existe una empresa con ID 1
            'telefono_huancayo' => '(+51) 944 492 316',
            'telefono_lima' => '(+51) 944 492 317',
            'email' => 'contacto@detalles.com',
            'horario_atencion' => 'Lun - Dom: 9:00 - 20:00',
            'facebook' => 'https://facebook.com/detallesymas',
            'instagram' => 'https://instagram.com/detallesymas',
            'tiktok' => 'https://tiktok.com/@detallesymas',
        ]);
    }
}