<?php

namespace App\Console\Commands;

use App\Services\FactilizaService;
use Illuminate\Console\Command;

class TestClientFactilizaIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:test-factiliza';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la integración de Factiliza en el formulario de clientes';

    /**
     * Execute the console command.
     */
    public function handle(FactilizaService $factilizaService): int
    {
        $this->info('=== PRUEBA DE INTEGRACIÓN FACTILIZA EN CLIENTES ===');
        $this->newLine();

        // Verificar estado del servicio
        $this->info('1. Verificando estado del servicio...');
        $info = $factilizaService->infoToken();
        
        if (!$info['configurado']) {
            $this->error('❌ Token de Factiliza no configurado');
            $this->error('Configure el token en /admin/companies');
            return Command::FAILURE;
        }
        
        $this->info('✅ Token configurado correctamente');
        $this->newLine();

        // Probar DNI
        $this->info('2. Probando búsqueda de DNI...');
        $dni = '27427864';
        $result = $factilizaService->consultarDni($dni);
        
        if ($result['success']) {
            $this->info("✅ DNI {$dni} encontrado:");
            $data = $result['data'];
            $this->line("   Nombre: {$data['nombre_completo']}");
            $this->line("   Dirección: {$data['direccion']}");
            $this->line("   Distrito: {$data['distrito']}");
        } else {
            $this->error("❌ Error al buscar DNI: {$result['message']}");
        }
        
        $this->newLine();

        // Probar RUC
        $this->info('3. Probando búsqueda de RUC...');
        $ruc = '20131312955';
        $result = $factilizaService->consultarRuc($ruc);
        
        if ($result['success']) {
            $this->info("✅ RUC {$ruc} encontrado:");
            $data = $result['data'];
            $this->line("   Razón Social: {$data['nombre_o_razon_social']}");
            $this->line("   Estado: {$data['estado']}");
            $this->line("   Dirección: {$data['direccion']}");
            $this->line("   Distrito: {$data['distrito']}");
        } else {
            $this->error("❌ Error al buscar RUC: {$result['message']}");
        }

        $this->newLine();
        $this->info('=== INTEGRACIÓN LISTA ===');
        $this->info('Ahora puedes ir a: http://qpos.test/admin/clients/create');
        $this->info('Y probar la búsqueda automática escribiendo un DNI o RUC');

        return Command::SUCCESS;
    }
}