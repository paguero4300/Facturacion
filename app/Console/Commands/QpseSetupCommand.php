<?php

namespace App\Console\Commands;

use App\Services\QpseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class QpseSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qpse:setup {ruc?} {--plan=01}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configurar QPse para facturación electrónica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Configurando QPse para facturación electrónica...');
        
        // Verificar configuración básica
        if (!config('qpse.token')) {
            $this->error('❌ QPSE_TOKEN no configurado en .env');
            $this->line('Añade tu token de QPse en el archivo .env:');
            $this->line('QPSE_TOKEN=tu_token_aqui');
            return 1;
        }

        $ruc = $this->argument('ruc') ?: config('qpse.company.ruc');
        
        if (!$ruc || $ruc === '20000000001') {
            $ruc = $this->ask('¿Cuál es el RUC de tu empresa?', '20000000001');
        }

        $plan = $this->option('plan');
        
        $this->line("📋 Configuración:");
        $this->line("   RUC: {$ruc}");
        $this->line("   Plan: {$plan} (" . ($plan === '01' ? 'Por comprobantes' : 'Por empresa') . ')');
        $this->line("   Entorno: " . config('qpse.mode'));
        $this->line("   URL: " . config('qpse.url'));

        if (!$this->confirm('¿Continuar con la configuración?', true)) {
            $this->info('Configuración cancelada.');
            return 0;
        }

        try {
            $qpseService = new QpseService();
            
            $this->info('🏢 Creando empresa en QPse...');
            
            $response = $qpseService->crearEmpresa($ruc, $plan);
            
            if (isset($response['username']) && isset($response['password'])) {
                $this->info('✅ Empresa creada exitosamente');
                $this->line("   Usuario: {$response['username']}");
                $this->line("   Contraseña: {$response['password']}");
                
                // Actualizar .env automáticamente
                $this->updateEnvFile($ruc, $response['username'], $response['password']);
                
                // Probar obtener token
                $this->info('🔐 Probando obtener token de acceso...');
                $qpseService->setCredenciales($response['username'], $response['password']);
                $token = $qpseService->obtenerToken();
                
                $this->info('✅ Token obtenido correctamente');
                $this->info('🎉 Configuración completada exitosamente');
                
                $this->newLine();
                $this->info('📝 Próximos pasos:');
                $this->line('1. Verifica que las variables se actualizaron en tu .env');
                $this->line('2. Prueba enviar un documento de ejemplo');
                $this->line('3. Usa los facades Qpse o QpseGreenter en tu código');
                
            } else {
                $this->error('❌ Error: No se recibieron credenciales de QPse');
                $this->line('Respuesta: ' . json_encode($response, JSON_PRETTY_PRINT));
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error configurando QPse: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    /**
     * Actualizar archivo .env con las credenciales
     */
    protected function updateEnvFile(string $ruc, string $username, string $password): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            $this->warn('⚠️  No se pudo actualizar .env automáticamente');
            return;
        }
        
        $envContent = File::get($envPath);
        
        // Actualizar RUC
        $envContent = preg_replace('/^COMPANY_RUC=.*/m', "COMPANY_RUC={$ruc}", $envContent);
        $envContent = preg_replace('/^GREENTER_COMPANY_RUC=.*/m', "GREENTER_COMPANY_RUC={$ruc}", $envContent);
        
        // Actualizar credenciales QPse
        $envContent = preg_replace('/^QPSE_USERNAME=.*/m', "QPSE_USERNAME={$username}", $envContent);
        $envContent = preg_replace('/^QPSE_PASSWORD=.*/m', "QPSE_PASSWORD={$password}", $envContent);
        
        File::put($envPath, $envContent);
        
        $this->info('✅ Archivo .env actualizado automáticamente');
    }
}
