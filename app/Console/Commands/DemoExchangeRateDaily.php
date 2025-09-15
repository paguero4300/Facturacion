<?php

namespace App\Console\Commands;

use App\Services\FactilizaService;
use App\Models\ExchangeRate;
use Illuminate\Console\Command;

class DemoExchangeRateDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:exchange-rate-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demostrar que el tipo de cambio se consulta UNA SOLA VEZ AL DÍA';

    /**
     * Execute the console command.
     */
    public function handle(FactilizaService $factilizaService): int
    {
        $this->info('🎯 DEMOSTRACIÓN: UNA CONSULTA POR DÍA');
        $this->newLine();

        $today = now()->toDateString();
        
        // Verificar estado actual
        $this->info("📅 Fecha de hoy: {$today}");
        $existing = ExchangeRate::getForDate($today);
        
        if ($existing) {
            $this->info("✅ YA EXISTE tipo de cambio para hoy");
            $this->info("   Consultado desde API: {$existing->fetched_at->format('Y-m-d H:i:s')}");
            $this->info("   Compra: S/ {$existing->buy_rate}");
            $this->info("   Venta: S/ {$existing->sell_rate}");
        } else {
            $this->warn("❌ NO existe tipo de cambio para hoy");
        }
        
        $this->newLine();
        $this->info("🧪 SIMULANDO MÚLTIPLES FACTURAS USD EN EL MISMO DÍA:");
        $this->newLine();

        $apiCalls = 0;
        $cacheHits = 0;
        $totalFacturas = 20;

        // Simular múltiples facturas
        for ($i = 1; $i <= $totalFacturas; $i++) {
            $result = $factilizaService->consultarTipoCambio();
            
            if ($result['success']) {
                $source = isset($result['data']['cached']) && $result['data']['cached'] ? 'CACHE' : 'API';
                
                if ($source === 'CACHE') {
                    $cacheHits++;
                    $icon = '💾';
                } else {
                    $apiCalls++;
                    $icon = '🌐';
                }
                
                $this->line("   Factura #{$i}: {$icon} {$source} - TC: S/ {$result['data']['venta']}");
            } else {
                $this->error("   Factura #{$i}: ❌ ERROR");
            }
        }

        $this->newLine();
        $this->info("📊 RESUMEN DEL DÍA:");
        $this->table(['Métrica', 'Valor'], [
            ['Total de facturas USD', $totalFacturas],
            ['Consultas desde CACHE', $cacheHits],
            ['Consultas desde API', $apiCalls],
            ['Tokens consumidos HOY', $apiCalls],
            ['Ahorro de tokens', $totalFacturas - $apiCalls],
            ['% de ahorro', round((($totalFacturas - $apiCalls) / $totalFacturas) * 100, 1) . '%'],
        ]);

        $this->newLine();
        $this->info("🎯 CONCLUSIÓN:");
        
        if ($apiCalls <= 1) {
            $this->info("✅ PERFECTO: Solo {$apiCalls} consulta(s) a la API por día");
            $this->info("✅ Todas las demás consultas usan el CACHE");
            $this->info("✅ Máximo 1 token por día, sin importar cuántas facturas");
        } else {
            $this->warn("⚠️ Se realizaron {$apiCalls} consultas a la API");
            $this->warn("   Esto no debería pasar en el mismo día");
        }

        $this->newLine();
        $this->info("💡 BENEFICIOS:");
        $this->info("   • Sin cache: {$totalFacturas} facturas = {$totalFacturas} tokens");
        $this->info("   • Con cache: {$totalFacturas} facturas = {$apiCalls} token(s)");
        $this->info("   • Ahorro: " . ($totalFacturas - $apiCalls) . " tokens por día");

        return Command::SUCCESS;
    }
}