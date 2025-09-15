<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\QpseTokenService;
use Illuminate\Console\Command;

class QpseStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'qpse:status {--ruc= : RUC de la empresa específica}';

    /**
     * The console command description.
     */
    protected $description = 'Mostrar estado de configuración QPse para empresas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Estado de Configuración QPse');
        $this->line('');

        $ruc = $this->option('ruc');
        
        if ($ruc) {
            $company = Company::where('ruc', $ruc)->first();
            if (!$company) {
                $this->error("No se encontró empresa con RUC: {$ruc}");
                return;
            }
            $this->showCompanyStatus($company);
        } else {
            $companies = Company::where('ose_provider', 'qpse')->get();
            
            if ($companies->isEmpty()) {
                $this->warn('No se encontraron empresas configuradas con QPse');
                return;
            }

            foreach ($companies as $company) {
                $this->showCompanyStatus($company);
                $this->line('');
            }
        }
    }

    private function showCompanyStatus(Company $company)
    {
        $this->info("🏢 {$company->business_name}");
        $this->line("   RUC: {$company->ruc}");
        
        // Estado básico
        $this->line("   Proveedor OSE: " . ($company->ose_provider === 'qpse' ? '✅ QPse' : '❌ ' . $company->ose_provider));
        $this->line("   Endpoint: " . ($company->ose_endpoint ?: '❌ No configurado'));
        $this->line("   Usuario: " . ($company->ose_username ? '✅ Configurado' : '❌ No configurado'));
        $this->line("   Contraseña: " . ($company->ose_password ? '✅ Configurada' : '❌ No configurada'));

        // Estado del token
        $tokenService = app(QpseTokenService::class);
        $status = $tokenService->getQpseStatus($company);

        $this->line("   Token de Acceso: " . ($status['has_access_token'] ? '✅ Disponible' : '❌ No disponible'));
        
        if ($status['has_access_token']) {
            $tokenStatus = $this->getTokenStatusText($status['token_status']);
            $this->line("   Estado del Token: {$tokenStatus}");
            
            if (isset($status['token_expires_at'])) {
                $expiresAt = \Carbon\Carbon::parse($status['token_expires_at']);
                $this->line("   Expira: " . $expiresAt->format('d/m/Y H:i:s'));
                
                $hoursUntilExpiry = $status['token_expires_in_hours'];
                if ($hoursUntilExpiry < 0) {
                    $this->line("   ⏰ Expiró hace " . abs(round($hoursUntilExpiry, 1)) . " horas");
                } else {
                    $this->line("   ⏰ Expira en " . round($hoursUntilExpiry, 1) . " horas");
                }
            }
        }

        // Estado general
        $overallStatus = $this->getOverallStatus($status);
        $this->line("   Estado General: {$overallStatus}");

        // Recomendaciones
        $completeStatus = $tokenService->getCompleteStatus($company);
        if (!empty($completeStatus['recommendations'])) {
            $this->line("   💡 Recomendaciones:");
            foreach ($completeStatus['recommendations'] as $recommendation) {
                $this->line("      • {$recommendation}");
            }
        }

        // Acciones disponibles
        if (!empty($completeStatus['actions_available'])) {
            $this->line("   🔧 Acciones disponibles:");
            foreach ($completeStatus['actions_available'] as $action) {
                $actionText = match($action) {
                    'refresh_token' => 'Renovar token (php artisan qpse:refresh-token --ruc=' . $company->ruc . ')',
                    'get_token' => 'Obtener token inicial',
                    'test_connection' => 'Probar conexión (php artisan qpse:test-connection --ruc=' . $company->ruc . ')',
                    default => $action
                };
                $this->line("      • {$actionText}");
            }
        }
    }

    private function getTokenStatusText(string $tokenStatus): string
    {
        return match($tokenStatus) {
            'valid' => '✅ Válido',
            'expires_soon' => '⚠️ Expira Pronto',
            'expired' => '❌ Expirado',
            'no_token' => '❌ Sin Token',
            'unknown_expiration' => '❓ Expiración Desconocida',
            default => '❓ Estado Desconocido'
        };
    }

    private function getOverallStatus(array $status): string
    {
        if (!$status['has_credentials'] || empty($status['endpoint'])) {
            return '🔧 Necesita Configuración';
        }

        if (!$status['has_access_token'] || $status['token_status'] === 'expired') {
            return '🔄 Necesita Renovar Token';
        }

        if ($status['token_status'] === 'expires_soon') {
            return '⚠️ Token Expira Pronto';
        }

        if ($status['is_configured'] && $status['token_status'] === 'valid') {
            return '✅ Completamente Configurado';
        }

        return '❓ Estado Desconocido';
    }
}