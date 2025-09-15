<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\CompanyApiService;
use Illuminate\Console\Command;

class QpseTestConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'qpse:test-connection {--ruc= : RUC de la empresa} {--all : Probar todas las empresas}';

    /**
     * The console command description.
     */
    protected $description = 'Probar conexión con QPse para una empresa específica o todas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Probando conexión con QPse...');

        if ($this->option('all')) {
            $this->testAllCompanies();
        } else {
            $ruc = $this->option('ruc');
            if (!$ruc) {
                $ruc = $this->ask('Ingrese el RUC de la empresa');
            }
            $this->testSingleCompany($ruc);
        }
    }

    private function testAllCompanies()
    {
        $companies = Company::where('ose_provider', 'qpse')
            ->whereNotNull('ose_username')
            ->whereNotNull('ose_password')
            ->get();

        if ($companies->isEmpty()) {
            $this->warn('No se encontraron empresas configuradas con QPse');
            return;
        }

        $this->info("Probando {$companies->count()} empresas...");

        foreach ($companies as $company) {
            $this->testCompanyConnection($company);
        }
    }

    private function testSingleCompany(string $ruc)
    {
        $company = Company::where('ruc', $ruc)->first();

        if (!$company) {
            $this->error("No se encontró empresa con RUC: {$ruc}");
            return;
        }

        if ($company->ose_provider !== 'qpse') {
            $this->error("La empresa {$ruc} no está configurada para usar QPse");
            return;
        }

        $this->testCompanyConnection($company);
    }

    private function testCompanyConnection(Company $company)
    {
        $this->line('');
        $this->info("🏢 Empresa: {$company->business_name} (RUC: {$company->ruc})");
        
        // Verificar configuración básica
        if (empty($company->ose_username) || empty($company->ose_password)) {
            $this->error('❌ Credenciales no configuradas');
            return;
        }

        if (empty($company->ose_endpoint)) {
            $this->warn('⚠️  Endpoint no configurado, usando default');
            $company->ose_endpoint = 'https://demo-cpe.qpse.pe';
        }

        $this->line("   📍 Endpoint: {$company->ose_endpoint}");
        $this->line("   👤 Usuario: {$company->ose_username}");
        $this->line("   🔑 Contraseña: " . str_repeat('*', strlen($company->ose_password)));

        // Probar conexión
        $apiService = app(CompanyApiService::class);
        $result = $apiService->testQpseConnection(
            $company->ose_endpoint,
            $company->ose_username,
            $company->ose_password
        );

        if ($result['success']) {
            $this->info('   ✅ Conexión exitosa');
            if (isset($result['data']['token_obtained']) && $result['data']['token_obtained']) {
                $this->info('   🎫 Token obtenido correctamente');
                if (isset($result['data']['expires_in'])) {
                    $this->line("   ⏰ Expira en: {$result['data']['expires_in']} segundos");
                }
            }
        } else {
            $this->error('   ❌ Error de conexión');
            $this->error("   📝 Mensaje: {$result['error']}");
            
            if (isset($result['status_code'])) {
                $this->line("   🔢 Código HTTP: {$result['status_code']}");
            }

            if (isset($result['raw_response']) && !empty($result['raw_response'])) {
                $this->line('   📄 Respuesta completa:');
                $this->line('   ' . str_replace("\n", "\n   ", $result['raw_response']));
            }

            // Sugerencias basadas en el error
            $this->provideSuggestions($result);
        }
    }

    private function provideSuggestions(array $result)
    {
        $this->line('');
        $this->info('💡 Sugerencias:');

        if (isset($result['status_code'])) {
            switch ($result['status_code']) {
                case 401:
                    $this->line('   • Verifique que las credenciales sean correctas');
                    $this->line('   • Asegúrese de que no haya espacios extra');
                    $this->line('   • Confirme que la cuenta esté activa en QPse');
                    break;
                case 404:
                    $this->line('   • Verifique que el endpoint sea correcto');
                    $this->line('   • Demo: https://demo-cpe.qpse.pe');
                    $this->line('   • Producción: https://cpe.qpse.pe');
                    break;
                case 403:
                    $this->line('   • Su cuenta puede estar desactivada');
                    $this->line('   • Contacte al soporte de QPse');
                    break;
                case 500:
                    $this->line('   • Error interno del servidor QPse');
                    $this->line('   • Intente más tarde o contacte soporte');
                    break;
                default:
                    $this->line('   • Revise los logs para más detalles');
                    $this->line('   • Contacte al soporte técnico');
            }
        } else {
            $this->line('   • Verifique su conexión a internet');
            $this->line('   • Confirme que el endpoint sea accesible');
            $this->line('   • Revise la configuración del firewall');
        }

        $this->line('');
        $this->info('📚 Para más ayuda, consulte: QPSE_CONNECTION_TROUBLESHOOTING.md');
    }
}