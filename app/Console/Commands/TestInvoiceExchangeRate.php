<?php

namespace App\Console\Commands;

use App\Services\FactilizaService;
use Illuminate\Console\Command;

class TestInvoiceExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:test-exchange-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la integración de tipo de cambio en facturas';

    /**
     * Execute the console command.
     */
    public function handle(FactilizaService $factilizaService): int
    {
        $this->info('=== PRUEBA DE TIPO DE CAMBIO EN FACTURAS ===');
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
        $this->info('2. Consultando tipo de cambio...');
        $result = $factilizaService->consultarTipoCambio();
        
        if ($result['success']) {
            $data = $result['data'];
            $this->info('✅ Consulta exitosa:');
            $this->table(['Campo', 'Valor'], [
                ['Fecha', $data['fecha']],
                ['Compra', 'S/ ' . number_format($data['compra'], 6)],
                ['Venta', 'S/ ' . number_format($data['venta'], 6)],
                ['Diferencia', 'S/ ' . number_format($data['venta'] - $data['compra'], 6)]
            ]);
            
            $this->newLine();
            $this->info('3. Simulando integración en factura...');
            $exchangeRate = (float) $data['venta'];
            $this->info("✅ Tipo de cambio que se establecería: {$exchangeRate}");
            
            // Simular cálculo
            $amountUSD = 100.00;
            $amountPEN = $amountUSD * $exchangeRate;
            $this->info("💰 Ejemplo: US$ {$amountUSD} = S/ " . number_format($amountPEN, 2));
            
        } else {
            $this->error("❌ Error al consultar tipo de cambio: {$result['message']}");
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('=== FUNCIONALIDAD EN FACTURAS ===');
        $this->info('📋 Pasos para usar:');
        $this->info('1. Ve a /admin/invoices/create');
        $this->info('2. Selecciona moneda "Dólares ($)"');
        $this->info('3. Aparecerá el campo "Tipo de Cambio"');
        $this->info('4. Haz clic en "Obtener TC" para consultar automáticamente');
        $this->info('5. El tipo de cambio se actualizará con el valor de venta');
        
        $this->newLine();
        $this->info('=== CARACTERÍSTICAS ===');
        $this->info('✅ Consulta automática del tipo de cambio');
        $this->info('✅ Usa el precio de venta (más alto)');
        $this->info('✅ Notificación con fecha y valor');
        $this->info('✅ Manejo de errores');
        $this->info('✅ Solo visible cuando moneda = USD');

        return Command::SUCCESS;
    }
}