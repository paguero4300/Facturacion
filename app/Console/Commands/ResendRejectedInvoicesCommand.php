<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\ElectronicInvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResendRejectedInvoicesCommand extends Command
{
    protected $signature = 'invoices:resend-rejected 
                            {--company=* : IDs de empresas específicas a procesar}
                            {--document-type=* : Tipos de documento a procesar (01,03,07,08)}
                            {--limit=20 : Número máximo de documentos a reprocesar}
                            {--from-date= : Fecha inicial (Y-m-d)}
                            {--to-date= : Fecha final (Y-m-d)}
                            {--include-observed : Incluir documentos con estado "observed"}
                            {--dry-run : Solo mostrar qué documentos se reprocesarían}';

    protected $description = 'Reenvía documentos rechazados/fallidos a SUNAT vía QPse';

    protected ElectronicInvoiceService $electronicService;

    public function __construct(ElectronicInvoiceService $electronicService)
    {
        parent::__construct();
        $this->electronicService = $electronicService;
    }

    public function handle(): int
    {
        $this->info('🔄 Iniciando reenvío de documentos rechazados...');

        // Verificar configuración
        if (!$this->electronicService->isConfigured()) {
            $this->error('❌ QPse no está configurado correctamente. Verificar credenciales.');
            return self::FAILURE;
        }

        // Construir query para documentos rechazados
        $query = $this->buildQuery();
        
        // Contar documentos
        $totalCount = $query->count();
        
        if ($totalCount === 0) {
            $this->info('ℹ️  No hay documentos rechazados que cumplan los criterios especificados.');
            return self::SUCCESS;
        }

        $this->info("📄 Encontrados {$totalCount} documentos rechazados/fallidos.");

        // Obtener documentos con límite
        $limit = (int) $this->option('limit');
        $invoices = $query->limit($limit)->get();

        $this->info("🔄 Reprocesando {$invoices->count()} documentos (límite: {$limit})...");

        // Verificar si es simulación
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('🔍 MODO SIMULACIÓN - No se reenviarán documentos realmente');
            $this->showRejectedDocuments($invoices);
            return self::SUCCESS;
        }

        // Procesar documentos
        return $this->processInvoices($invoices);
    }

    protected function buildQuery()
    {
        $statuses = ['rejected'];
        
        // Incluir observados si se especifica  
        if ($this->option('include-observed')) {
            $statuses[] = 'observed';
        }

        $query = Invoice::query()
            ->with(['company', 'client', 'details'])
            ->whereIn('document_type', ['01', '03', '07', '08'])
            ->whereIn('sunat_status', $statuses)
            ->orderBy('issue_date', 'desc')
            ->orderBy('id', 'desc');

        // Filtrar por empresa
        if ($companies = $this->option('company')) {
            $query->whereIn('company_id', $companies);
        }

        // Filtrar por tipo de documento
        if ($documentTypes = $this->option('document-type')) {
            $query->whereIn('document_type', $documentTypes);
        }

        // Filtrar por rango de fechas
        if ($fromDate = $this->option('from-date')) {
            $query->whereDate('issue_date', '>=', $fromDate);
        }

        if ($toDate = $this->option('to-date')) {
            $query->whereDate('issue_date', '<=', $toDate);
        }

        return $query;
    }

    protected function showRejectedDocuments($invoices): void
    {
        $this->table(
            ['ID', 'Empresa', 'Tipo', 'Número', 'Cliente', 'Fecha', 'Estado', 'Último Error'],
            $invoices->map(function (Invoice $invoice) {
                $lastError = $invoice->additional_data['last_error'] ?? 
                           $invoice->additional_data['qpse_error']['error']['message'] ?? 
                           'Error no especificado';
                
                return [
                    $invoice->id,
                    substr($invoice->company->business_name, 0, 25),
                    $this->getDocumentTypeName($invoice->document_type),
                    $invoice->full_number,
                    substr($invoice->client_business_name, 0, 20),
                    $invoice->issue_date->format('d/m/Y'),
                    ucfirst($invoice->sunat_status),
                    substr($lastError, 0, 40) . (strlen($lastError) > 40 ? '...' : '')
                ];
            })->toArray()
        );
    }

    protected function processInvoices($invoices): int
    {
        $processed = 0;
        $success = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($invoices->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();

        foreach ($invoices as $invoice) {
            $processed++;
            
            try {
                $this->line(''); // Nueva línea para separar del progress bar
                $this->info("🔄 Reenviando {$invoice->full_number} ({$this->getDocumentTypeName($invoice->document_type)})...");

                // Mostrar error anterior si existe
                $lastError = $invoice->additional_data['last_error'] ?? 
                           $invoice->additional_data['qpse_error']['error']['message'] ?? null;
                
                if ($lastError) {
                    $this->line("   📝 Error anterior: " . substr($lastError, 0, 80));
                }

                $result = $this->electronicService->resendDocument($invoice);

                if ($result['success']) {
                    $success++;
                    $this->info("   ✅ Reenviado exitosamente: {$invoice->full_number}");
                    
                    Log::info('Documento reenviado exitosamente por comando', [
                        'invoice_id' => $invoice->id,
                        'full_number' => $invoice->full_number,
                        'previous_status' => $invoice->sunat_status,
                        'company' => $invoice->company->business_name
                    ]);
                } else {
                    $errors++;
                    $errorMsg = $result['error']['message'] ?? 'Error desconocido';
                    $this->error("   ❌ Error en reenvío {$invoice->full_number}: {$errorMsg}");
                    
                    Log::error('Error reenviando documento por comando', [
                        'invoice_id' => $invoice->id,
                        'full_number' => $invoice->full_number,
                        'error' => $errorMsg,
                        'previous_error' => $lastError
                    ]);
                }

                $progressBar->advance();
                
                // Pausa más larga para reenvíos (servidor puede estar ocupado)
                sleep(1);

            } catch (\Exception $e) {
                $errors++;
                $this->error("   💥 Excepción en {$invoice->full_number}: {$e->getMessage()}");
                
                Log::error('Excepción reenviando documento por comando', [
                    'invoice_id' => $invoice->id,
                    'full_number' => $invoice->full_number,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->line(''); // Nueva línea después del progress bar

        // Mostrar resumen
        $this->showSummary($processed, $success, $errors);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function getDocumentTypeName(string $type): string
    {
        return match($type) {
            '01' => 'Factura',
            '03' => 'Boleta',
            '07' => 'N. Crédito',
            '08' => 'N. Débito',
            default => $type
        };
    }

    protected function showSummary(int $processed, int $success, int $errors): void
    {
        $this->line('');
        $this->line('📊 <fg=cyan>RESUMEN DEL REENVÍO</>');
        $this->line(str_repeat('=', 50));
        
        $this->line("📄 Documentos reprocesados: <fg=white>{$processed}</>");
        $this->line("✅ Reenviados exitosamente: <fg=green>{$success}</>");
        $this->line("❌ Que siguen con errores: <fg=red>{$errors}</>");
        
        if ($processed > 0) {
            $recoveryRate = round(($success / $processed) * 100, 1);
            $this->line("📈 Tasa de recuperación: <fg=yellow>{$recoveryRate}%</>");
        }

        $this->line('');
        
        if ($success > 0) {
            $this->info("🎉 Se recuperaron {$success} documentos exitosamente.");
        }
        
        if ($errors > 0) {
            $this->error("⚠️  {$errors} documentos siguen con errores. Revisar logs y considerar corrección manual.");
            $this->line("   Logs disponibles en: storage/logs/laravel.log");
            $this->line("   💡 Tip: Usar --include-observed para incluir documentos observados");
        } else if ($success === 0) {
            $this->warn("🤔 No se pudo recuperar ningún documento. Posibles causas:");
            $this->line("   • Problemas de conectividad con QPse/SUNAT");
            $this->line("   • Credenciales incorrectas o vencidas");
            $this->line("   • Errores estructurales en los documentos");
        }
    }
}