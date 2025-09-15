<?php

namespace App\Console\Commands;

use App\Services\FactilizaService;
use Illuminate\Console\Command;

class TestFactilizaApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'factiliza:test {tipo=dni} {numero=27427864}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar las APIs de Factiliza para consulta de DNI y RUC';

    /**
     * Execute the console command.
     */
    public function handle(FactilizaService $factilizaService): int
    {
        $tipo = $this->argument('tipo');
        $numero = $this->argument('numero');

        $this->info('=== PRUEBA DE API FACTILIZA ===');
        $this->newLine();

        // Verificar estado del token
        $this->info('1. Estado del token:');
        $info = $factilizaService->infoToken();
        $this->table(['Campo', 'Valor'], [
            ['Configurado', $info['configurado'] ? 'Sí' : 'No'],
            ['Mensaje', $info['mensaje']],
            ['Longitud', $info['longitud'] ?? 'N/A'],
            ['Inicio', $info['inicio'] ?? 'N/A']
        ]);
        $this->newLine();

        if (!$info['configurado']) {
            $this->error('Token no configurado. No se pueden realizar consultas.');
            return Command::FAILURE;
        }

        // Realizar consulta según el tipo
        $this->info("2. Consulta de {$tipo} ({$numero}):");
        
        if ($tipo === 'dni') {
            $resultado = $factilizaService->consultarDni($numero);
        } elseif ($tipo === 'ruc') {
            $resultado = $factilizaService->consultarRuc($numero);
        } else {
            $this->error('Tipo inválido. Use "dni" o "ruc"');
            return Command::FAILURE;
        }

        if ($resultado['success']) {
            $this->info('✅ Consulta exitosa');
            $this->info('Mensaje: ' . $resultado['message']);
            
            if ($resultado['data']) {
                $this->newLine();
                $this->info('Datos obtenidos:');
                
                $data = $resultado['data'];
                $rows = [];
                
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $rows[] = [ucfirst(str_replace('_', ' ', $key)), $value ?: 'N/A'];
                }
                
                $this->table(['Campo', 'Valor'], $rows);
            }
        } else {
            $this->error('❌ Error en la consulta');
            $this->error('Mensaje: ' . $resultado['message']);
        }

        $this->newLine();
        $this->info('=== FIN DE PRUEBAS ===');

        return Command::SUCCESS;
    }
}