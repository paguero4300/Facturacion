<?php

namespace App\Console\Commands;

use App\Services\FactilizaService;
use App\Models\ExchangeRate;
use Illuminate\Console\Command;

class ManageExchangeRateCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-rate:manage 
                            {action : Acción a realizar (fetch|stats|clean|refresh)}
                            {--date= : Fecha específica (YYYY-MM-DD)}
                            {--days=30 : Días a mantener en limpieza}
                            {--force : Forzar actualización}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestionar cache de tipos de cambio';

    /**
     * Execute the console command.
     */
    public function handle(FactilizaService $factilizaService): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'fetch':
                return $this->fetchExchangeRate($factilizaService);
            case 'stats':
                return $this->showStats($factilizaService);
            case 'clean':
                return $this->cleanOldRates($factilizaService);
            case 'refresh':
                return $this->refreshToday($factilizaService);
            default:
                $this->error("Acción no válida: {$action}");
                $this->info('Acciones disponibles: fetch, stats, clean, refresh');
                return Command::FAILURE;
        }
    }

    /**
     * Obtener tipo de cambio para una fecha
     */
    private function fetchExchangeRate(FactilizaService $factilizaService): int
    {
        $date = $this->option('date') ?: now()->toDateString();
        $force = $this->option('force');

        $this->info("=== OBTENER TIPO DE CAMBIO ===");
        $this->info("Fecha: {$date}");
        $this->info("Forzar actualización: " . ($force ? 'Sí' : 'No'));
        $this->newLine();

        // Verificar si ya existe
        if (!$force) {
            $existing = ExchangeRate::getForDate($date);
            if ($existing) {
                $this->info("✅ Tipo de cambio ya existe en cache:");
                $this->table(['Campo', 'Valor'], [
                    ['Fecha', $existing->date->format('Y-m-d')],
                    ['Compra', 'S/ ' . number_format($existing->buy_rate, 6)],
                    ['Venta', 'S/ ' . number_format($existing->sell_rate, 6)],
                    ['Consultado', $existing->fetched_at->format('Y-m-d H:i:s')],
                ]);
                return Command::SUCCESS;
            }
        }

        // Consultar desde API
        $this->info("🔄 Consultando desde API...");
        $result = $factilizaService->consultarTipoCambio($date, $force);

        if ($result['success']) {
            $data = $result['data'];
            $this->info("✅ Tipo de cambio obtenido:");
            $this->table(['Campo', 'Valor'], [
                ['Fecha', $data['fecha']],
                ['Compra', 'S/ ' . number_format($data['compra'], 6)],
                ['Venta', 'S/ ' . number_format($data['venta'], 6)],
                ['Fuente', $data['cached'] ? 'Cache' : 'API'],
            ]);
            
            if (!$data['cached']) {
                $this->info("💾 Guardado en cache para futuras consultas");
            }
            
            return Command::SUCCESS;
        } else {
            $this->error("❌ Error: {$result['message']}");
            return Command::FAILURE;
        }
    }

    /**
     * Mostrar estadísticas del cache
     */
    private function showStats(FactilizaService $factilizaService): int
    {
        $this->info("=== ESTADÍSTICAS DEL CACHE ===");
        $this->newLine();

        $stats = $factilizaService->getExchangeRateStats();
        
        if ($stats['count'] === 0) {
            $this->warn("📊 No hay datos en el cache");
            return Command::SUCCESS;
        }

        $this->info("📊 Estadísticas (últimos 7 días):");
        $this->table(['Métrica', 'Valor'], [
            ['Registros', $stats['count']],
            ['Promedio Compra', 'S/ ' . number_format($stats['avg_buy'], 6)],
            ['Promedio Venta', 'S/ ' . number_format($stats['avg_sell'], 6)],
            ['Mínimo Compra', 'S/ ' . number_format($stats['min_buy'], 6)],
            ['Máximo Compra', 'S/ ' . number_format($stats['max_buy'], 6)],
            ['Mínimo Venta', 'S/ ' . number_format($stats['min_sell'], 6)],
            ['Máximo Venta', 'S/ ' . number_format($stats['max_sell'], 6)],
        ]);

        if ($stats['latest']) {
            $latest = $stats['latest'];
            $this->newLine();
            $this->info("📅 Último registro:");
            $this->table(['Campo', 'Valor'], [
                ['Fecha', $latest->date->format('Y-m-d')],
                ['Compra', 'S/ ' . number_format($latest->buy_rate, 6)],
                ['Venta', 'S/ ' . number_format($latest->sell_rate, 6)],
                ['Consultado', $latest->fetched_at->format('Y-m-d H:i:s')],
            ]);
        }

        // Verificar si hay tipo de cambio para hoy
        $hasToday = $factilizaService->hasExchangeRateForToday();
        $this->newLine();
        $this->info("🗓️ Tipo de cambio para hoy: " . ($hasToday ? '✅ Disponible' : '❌ No disponible'));

        return Command::SUCCESS;
    }

    /**
     * Limpiar registros antiguos
     */
    private function cleanOldRates(FactilizaService $factilizaService): int
    {
        $days = (int) $this->option('days');
        
        $this->info("=== LIMPIAR CACHE ANTIGUO ===");
        $this->info("Manteniendo últimos {$days} días");
        $this->newLine();

        if ($this->confirm("¿Está seguro de eliminar registros anteriores a " . now()->subDays($days)->format('Y-m-d') . "?")) {
            $deleted = $factilizaService->cleanOldExchangeRates($days);
            
            if ($deleted > 0) {
                $this->info("✅ Eliminados {$deleted} registros antiguos");
            } else {
                $this->info("ℹ️ No hay registros antiguos para eliminar");
            }
            
            return Command::SUCCESS;
        } else {
            $this->info("❌ Operación cancelada");
            return Command::FAILURE;
        }
    }

    /**
     * Actualizar tipo de cambio de hoy
     */
    private function refreshToday(FactilizaService $factilizaService): int
    {
        $this->info("=== ACTUALIZAR TIPO DE CAMBIO DE HOY ===");
        $this->newLine();

        $today = now()->toDateString();
        $this->info("🔄 Actualizando tipo de cambio para: {$today}");

        $result = $factilizaService->consultarTipoCambio($today, true);

        if ($result['success']) {
            $data = $result['data'];
            $this->info("✅ Tipo de cambio actualizado:");
            $this->table(['Campo', 'Valor'], [
                ['Fecha', $data['fecha']],
                ['Compra', 'S/ ' . number_format($data['compra'], 6)],
                ['Venta', 'S/ ' . number_format($data['venta'], 6)],
                ['Fuente', 'API (forzado)'],
            ]);
            
            return Command::SUCCESS;
        } else {
            $this->error("❌ Error: {$result['message']}");
            return Command::FAILURE;
        }
    }
}