<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\ElectronicInvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPendingInvoicesCommand extends Command
{
    protected $signature = 'invoices:send-pending 
                            {--company=* : IDs de empresas específicas a procesar}
                            {--document-type=* : Tipos de documento a procesar (01,03,07,08)}
                            {--limit=50 : Número máximo de documentos a procesar}
                            {--from-date= : Fecha inicial (Y-m-d)}
                            {--to-date= : Fecha final (Y-m-d)}
                            {--dry-run : Solo mostrar qué documentos se procesarían sin enviarlos}';

    protected $description = 'Envía documentos pendientes a SUNAT vía QPse';

    protected ElectronicInvoiceService $electronicService;

    public function __construct(ElectronicInvoiceService $electronicService)
    {
        parent::__construct();
        $this->electronicService = $electronicService;
    }

    public function handle(): int
    {
        $this->info('🚀 Iniciando envío de documentos pendientes a SUNAT...');

        // Verificar configuración
        if (!$this->electronicService->isConfigured()) {
            $this->error('❌ QPse no está configurado correctamente. Verificar credenciales.');
            return self::FAILURE;
        }

        // Construir query para documentos pendientes
        $query = $this->buildQuery();
        
        // Contar documentos
        $totalCount = $query->count();
        
        if ($totalCount === 0) {
            $this->info('ℹ️  No hay documentos pendientes que cumplan los criterios especificados.');
            return self::SUCCESS;
        }

        $this->info("📄 Encontrados {$totalCount} documentos pendientes.");

        // Obtener documentos con límite
        $limit = (int) $this->option('limit');
        $invoices = $query->limit($limit)->get();

        $this->info("🔄 Procesando {$invoices->count()} documentos (límite: {$limit})...");

        // Verificar si es simulación
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('🔍 MODO SIMULACIÓN - No se enviarán documentos realmente');
            $this->showPendingDocuments($invoices);
            return self::SUCCESS;
        }

        // Procesar documentos
        return $this->processInvoices($invoices);
    }

    protected function buildQuery()
    {
        $query = Invoice::query()
            ->with(['company', 'client', 'details'])
            ->whereIn('document_type', ['01', '03', '07', '08'])
            ->where('sunat_status', 'pending')
            ->orderBy('issue_date')
            ->orderBy('id');

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

    protected function showPendingDocuments($invoices): void
    {
        $this->table(
            ['ID', 'Empresa', 'Tipo', 'Número', 'Cliente', 'Fecha', 'Total', 'Moneda'],
            $invoices->map(function (Invoice $invoice) {
                return [
                    $invoice->id,
                    substr($invoice->company->business_name, 0, 30),
                    $this->getDocumentTypeName($invoice->document_type),
                    $invoice->full_number,
                    substr($invoice->client_business_name, 0, 25),
                    $invoice->issue_date->format('d/m/Y'),
                    number_format($invoice->total_amount, 2),
                    $invoice->currency_code
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
                $this->info("📄 Procesando {$invoice->full_number} ({$this->getDocumentTypeName($invoice->document_type)})...");

                $result = $this->sendInvoiceByType($invoice);

                if ($result['success']) {
                    $success++;
                    $this->info("   ✅ Enviado exitosamente: {$invoice->full_number}");
                    
                    Log::info('Documento enviado por comando', [
                        'invoice_id' => $invoice->id,
                        'full_number' => $invoice->full_number,
                        'company' => $invoice->company->business_name
                    ]);
                } else {
                    $errors++;
                    $errorMsg = $result['error']['message'] ?? 'Error desconocido';
                    $this->error("   ❌ Error en {$invoice->full_number}: {$errorMsg}");
                    
                    Log::error('Error enviando documento por comando', [
                        'invoice_id' => $invoice->id,
                        'full_number' => $invoice->full_number,
                        'error' => $errorMsg
                    ]);
                }

                $progressBar->advance();
                
                // Pequeña pausa para evitar saturar el servicio
                usleep(500000); // 0.5 segundos

            } catch (\Exception $e) {
                $errors++;
                $this->error("   💥 Excepción en {$invoice->full_number}: {$e->getMessage()}");
                
                Log::error('Excepción enviando documento por comando', [
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

    protected function sendInvoiceByType(Invoice $invoice): array
    {
        return match($invoice->document_type) {
            '01' => $this->electronicService->sendFactura($invoice),
            '03' => $this->electronicService->sendBoleta($invoice),
            '07' => $this->electronicService->sendNotaCredito($invoice),
            '08' => $this->electronicService->sendNotaDebito($invoice),
            default => [
                'success' => false,
                'error' => ['message' => 'Tipo de documento no soportado: ' . $invoice->document_type]
            ]
        };
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
        $this->line('📊 <fg=cyan>RESUMEN DEL PROCESAMIENTO</>');
        $this->line(str_repeat('=', 50));
        
        $this->line("📄 Documentos procesados: <fg=white>{$processed}</>");
        $this->line("✅ Enviados exitosamente: <fg=green>{$success}</>");
        $this->line("❌ Con errores: <fg=red>{$errors}</>");
        
        if ($processed > 0) {
            $successRate = round(($success / $processed) * 100, 1);
            $this->line("📈 Tasa de éxito: <fg=yellow>{$successRate}%</>");
        }

        $this->line('');
        
        if ($success > 0) {
            $this->info("🎉 Se enviaron {$success} documentos exitosamente a SUNAT.");
        }
        
        if ($errors > 0) {
            $this->error("⚠️  {$errors} documentos presentaron errores. Revisar logs para más detalles.");
            $this->line("   Logs disponibles en: storage/logs/laravel.log");
        }
    }
}