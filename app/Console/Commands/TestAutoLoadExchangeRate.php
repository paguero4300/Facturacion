<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use Illuminate\Console\Command;

class TestAutoLoadExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:auto-load-exchange-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la carga automática de tipo de cambio al seleccionar USD';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 PRUEBA: CARGA AUTOMÁTICA DE TIPO DE CAMBIO');
        $this->newLine();

        $today = now()->toDateString();
        $this->info("📅 Fecha de hoy: {$today}");
        
        // Verificar si existe cache
        $cached = ExchangeRate::getForDate($today);
        
        if ($cached) {
            $this->info("✅ EXISTE tipo de cambio en cache:");
            $this->table(['Campo', 'Valor'], [
                ['Fecha', $cached->date->format('Y-m-d')],
                ['Compra', 'S/ ' . number_format($cached->buy_rate, 6)],
                ['Venta', 'S/ ' . number_format($cached->sell_rate, 6)],
                ['Consultado', $cached->fetched_at->format('Y-m-d H:i:s')],
            ]);
            
            $this->newLine();
            $this->info("🎯 COMPORTAMIENTO ESPERADO EN FACTURAS:");
            $this->info("1. Usuario selecciona moneda 'Dólares ($)'");
            $this->info("2. Campo 'Tipo de Cambio' se carga automáticamente: {$cached->sell_rate}");
            $this->info("3. Notificación: 'TC del {$cached->date->format('Y-m-d')}: S/ " . number_format($cached->sell_rate, 6) . " (desde cache)'");
            $this->info("4. Usuario puede usar el botón 'Obtener TC' para actualizar si desea");
            
        } else {
            $this->warn("❌ NO EXISTE tipo de cambio en cache para hoy");
            $this->newLine();
            $this->info("🎯 COMPORTAMIENTO ESPERADO EN FACTURAS:");
            $this->info("1. Usuario selecciona moneda 'Dólares ($)'");
            $this->info("2. Campo 'Tipo de Cambio' mantiene valor por defecto: 1.000000");
            $this->info("3. Notificación: 'Use el botón \"Obtener TC\" para consultar el tipo de cambio actual'");
            $this->info("4. Usuario debe hacer clic en 'Obtener TC' para consultar");
        }

        $this->newLine();
        $this->info("💡 VENTAJAS DE LA CARGA AUTOMÁTICA:");
        $this->info("   ✅ Experiencia más fluida para el usuario");
        $this->info("   ✅ No necesita hacer clic si ya hay cache");
        $this->info("   ✅ Sigue ahorrando tokens (usa cache)");
        $this->info("   ✅ Opción de actualizar manualmente si es necesario");

        $this->newLine();
        $this->info("🔄 FLUJO COMPLETO:");
        $this->info("   1. Primera factura USD del día → Botón 'Obtener TC' → API → Cache");
        $this->info("   2. Siguientes facturas USD → Seleccionar USD → Auto-carga desde cache");
        $this->info("   3. Si necesita actualizar → Botón 'Obtener TC' → API/Cache");

        return Command::SUCCESS;
    }
}