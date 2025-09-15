<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Console\Command;

class TestClientCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:test-creation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la creación de clientes para verificar que no hay errores';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== PRUEBA DE CREACIÓN DE CLIENTES ===');
        $this->newLine();

        // Obtener la primera empresa
        $company = Company::first();
        
        if (!$company) {
            $this->error('❌ No hay empresas en la base de datos');
            return Command::FAILURE;
        }

        $this->info("✅ Empresa encontrada: {$company->business_name}");
        $this->newLine();

        // Datos de prueba
        $testData = [
            'company_id' => $company->id,
            'document_type' => '6',
            'document_number' => '20600766725',
            'business_name' => 'SMART INDUSTRY S.A.C.',
            'commercial_name' => 'Smart Industry',
            'address' => 'PJ. EL ARTE NRO. 248 URB. CARLOS CUETO FERNANDINI',
            'district' => 'LOS OLIVOS',
            'province' => 'LIMA',
            'department' => 'LIMA',
            'ubigeo' => '150117',
            'phone' => '999888777',
            'email' => 'contacto@smartindustry.com',
            'contact_person' => 'Juan Pérez',
            'credit_limit' => 5000.00,
            'payment_days' => 30,
            'client_type' => 'regular',
            'status' => 'active',
            'created_by' => 1, // Asumiendo que existe un usuario con ID 1
        ];

        try {
            $this->info('📝 Intentando crear cliente...');
            
            $client = Client::create($testData);
            
            $this->info("✅ Cliente creado exitosamente:");
            $this->line("   ID: {$client->id}");
            $this->line("   RUC: {$client->document_number}");
            $this->line("   Razón Social: {$client->business_name}");
            $this->line("   Límite de Crédito: S/ {$client->credit_limit}");
            $this->line("   Días de Pago: {$client->payment_days}");
            
            $this->newLine();
            $this->info('🗑️ Eliminando cliente de prueba...');
            $client->delete();
            $this->info('✅ Cliente de prueba eliminado');
            
        } catch (\Exception $e) {
            $this->error('❌ Error al crear cliente:');
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('=== PRUEBA COMPLETADA EXITOSAMENTE ===');
        $this->info('Ahora puedes crear clientes desde: /admin/clients/create');

        return Command::SUCCESS;
    }
}