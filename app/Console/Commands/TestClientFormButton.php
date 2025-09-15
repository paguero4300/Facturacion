<?php

namespace App\Console\Commands;

use App\Services\FactilizaService;
use Illuminate\Console\Command;

class TestClientFormButton extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:test-form-button';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la funcionalidad del botón de consulta en el formulario de clientes';

    /**
     * Execute the console command.
     */
    public function handle(FactilizaService $factilizaService): int
    {
        $this->info('=== PRUEBA DEL BOTÓN DE CONSULTA FACTILIZA ===');
        $this->newLine();

        // Verificar estado del servicio
        $this->info('1. Verificando configuración...');
        $info = $factilizaService->infoToken();
        
        if (!$info['configurado']) {
            $this->error('❌ Token de Factiliza no configurado');
            $this->error('Configure el token en /admin/companies');
            return Command::FAILURE;
        }
        
        $this->info('✅ Token configurado correctamente');
        $this->newLine();

        // Simular búsqueda de DNI
        $this->info('2. Simulando consulta de DNI desde formulario...');
        $dni = '27427864';
        $result = $factilizaService->consultarDni($dni);
        
        if ($result['success']) {
            $this->info("✅ DNI {$dni} - Datos que se auto-completarían:");
            $data = $result['data'];
            $this->line("   📝 Razón Social: {$data['nombre_completo']}");
            $this->line("   📍 Dirección: " . ($data['direccion'] ?: 'No disponible'));
            $this->line("   🏘️ Distrito: " . ($data['distrito'] ?: 'No disponible'));
            $this->line("   🗺️ Provincia: " . ($data['provincia'] ?: 'No disponible'));
            $this->line("   🌍 Departamento: " . ($data['departamento'] ?: 'No disponible'));
            $this->line("   📊 Ubigeo: " . ($data['ubigeo_sunat'] ?: 'No disponible'));
        } else {
            $this->error("❌ Error al consultar DNI: {$result['message']}");
        }
        
        $this->newLine();

        // Simular búsqueda de RUC
        $this->info('3. Simulando consulta de RUC desde formulario...');
        $ruc = '20131312955';
        $result = $factilizaService->consultarRuc($ruc);
        
        if ($result['success']) {
            $this->info("✅ RUC {$ruc} - Datos que se auto-completarían:");
            $data = $result['data'];
            $this->line("   📝 Razón Social: {$data['nombre_o_razon_social']}");
            $this->line("   📍 Dirección: {$data['direccion']}");
            $this->line("   🏘️ Distrito: {$data['distrito']}");
            $this->line("   🗺️ Provincia: {$data['provincia']}");
            $this->line("   🌍 Departamento: {$data['departamento']}");
            $this->line("   📊 Ubigeo: {$data['ubigeo_sunat']}");
            $this->line("   📊 Estado: {$data['estado']}");
            $this->line("   📊 Condición: {$data['condicion']}");
        } else {
            $this->error("❌ Error al consultar RUC: {$result['message']}");
        }

        $this->newLine();
        $this->info('=== FUNCIONALIDAD DEL BOTÓN LISTA ===');
        $this->info('📋 Instrucciones de uso:');
        $this->line('1. Ve a: http://qpos.test/admin/clients/create');
        $this->line('2. Selecciona el tipo de documento (DNI o RUC)');
        $this->line('3. Escribe el número de documento');
        $this->line('4. Haz clic en el botón "🔍 Consultar"');
        $this->line('5. Los campos se completarán automáticamente');
        
        $this->newLine();
        $this->info('🎯 Ejemplos para probar:');
        $this->line("   DNI: {$dni}");
        $this->line("   RUC: {$ruc}");

        return Command::SUCCESS;
    }
}