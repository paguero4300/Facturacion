<?php

namespace App\Console\Commands;

use App\Services\FactilizaService;
use Illuminate\Console\Command;

class TestTipoCambioApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'factiliza:test-tipo-cambio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la API de tipo de cambio de Factiliza';

    /**
     * Execute the console command.
     */
    public function handle(FactilizaService $factilizaService): int
    {
        $this->info('=== PRUEBA DE API TIPO DE CAMBIO FACTILIZA ===');
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

        // Probar consulta de tipo de cambio
        $this->info('2. Consultando tipo de cambio del día...');
        $result = $factilizaService->consultarTipoCambio();
        
        if ($result['success']) {
            $data = $result['data'];
            $this->info('✅ Consulta exitosa:');
            $this->table(['Campo', 'Valor'], [
                ['Fecha', $data['fecha']],
                ['Compra', 'S/ ' . number_format($data['compra'], 6)],
                ['Venta', 'S/ ' . number_format($data['venta'], 6)],
                ['Diferencia', 'S/ ' . number_format($data['venta'] - $data['compra'], 6)],
                ['Fuente', isset($data['cached']) && $data['cached'] ? 'Cache' : 'API'],
                ['Consultado', $data['fetched_at'] ?? 'Ahora']
            ]);
            
            // Probar segunda consulta para verificar cache
            $this->newLine();
            $this->info('3. Probando cache (segunda consulta)...');
            $result2 = $factilizaService->consultarTipoCambio();
            
            if ($result2['success'] && isset($result2['data']['cached']) && $result2['data']['cached']) {
                $this->info('✅ Cache funcionando correctamente');
            } else {
                $this->warn('⚠️ Cache no detectado en segunda consulta');
            }
        } else {
            $this->error("❌ Error al consultar tipo de cambio: {$result['message']}");
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('=== ENDPOINT DISPONIBLE ===');
        $this->info('📡 GET /api/factiliza/tipo-cambio');
        $this->info('🌐 Ejemplo: curl http://localhost:8000/api/factiliza/tipo-cambio');
        
        $this->newLine();
        $this->info('=== RESPUESTA ESPERADA ===');
        $this->line('{');
        $this->line('  "success": true,');
        $this->line('  "message": "Consulta exitosa",');
        $this->line('  "data": {');
        $this->line('    "fecha": "' . $data['fecha'] . '",');
        $this->line('    "compra": ' . $data['compra'] . ',');
        $this->line('    "venta": ' . $data['venta']);
        $this->line('  }');
        $this->line('}');

        return Command::SUCCESS;
    }
}