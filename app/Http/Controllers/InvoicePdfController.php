<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function Spatie\LaravelPdf\Support\pdf;

class InvoicePdfController extends Controller
{
    /**
     * Generar y descargar PDF de factura
     */
    public function download(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load([
            'company',
            'client', 
            'details.product',
            'paymentInstallments'
        ]);

        // Generar nombre del archivo
        $filename = $this->generateFilename($invoice);

        // Configurar Browsershot con argumentos seguros de Chromium
        $pdf = pdf()
            ->view('pdf.invoice', compact('invoice'))
            ->format(config('invoice-pdf.format', 'A4'))
            ->margins(
                config('invoice-pdf.margins.top', 10),
                config('invoice-pdf.margins.right', 10),
                config('invoice-pdf.margins.bottom', 10),
                config('invoice-pdf.margins.left', 10)
            )
            ->name($filename)
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->noSandbox()
                    ->setOption('args', [
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--disable-web-security',
                        '--disable-features=VizDisplayCompositor',
                        '--run-all-compositor-stages-before-draw',
                        '--disable-backgrounding-occluded-windows',
                        '--disable-renderer-backgrounding',
                        '--disable-field-trial-config',
                        '--disable-ipc-flooding-protection',
                        '--memory-pressure-off',
                        '--disable-seccomp-filter-sandbox',
                        '--disable-software-rasterizer',
                        '--disable-extensions',
                        '--disable-plugins',
                        '--disable-images',
                        '--disable-javascript',
                        '--virtual-time-budget=60000',
                        '--single-process',
                        '--no-zygote',
                        '--disable-namespace-sandbox'
                    ]);
            });

        return $pdf->download();
    }

    /**
     * Mostrar PDF en el navegador (sin descargar)
     */
    public function view(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load([
            'company',
            'client', 
            'details.product',
            'paymentInstallments'
        ]);

        // Generar nombre del archivo
        $filename = $this->generateFilename($invoice);

        // Configurar PDF para vista con argumentos seguros de Chromium
        $pdf = pdf()
            ->view('pdf.invoice', compact('invoice'))
            ->format(config('invoice-pdf.format', 'A4'))
            ->margins(
                config('invoice-pdf.margins.top', 10),
                config('invoice-pdf.margins.right', 10),
                config('invoice-pdf.margins.bottom', 10),
                config('invoice-pdf.margins.left', 10)
            )
            ->name($filename)
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->noSandbox()
                    ->setOption('args', [
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--disable-web-security',
                        '--disable-features=VizDisplayCompositor',
                        '--run-all-compositor-stages-before-draw',
                        '--disable-backgrounding-occluded-windows',
                        '--disable-renderer-backgrounding',
                        '--disable-field-trial-config',
                        '--disable-ipc-flooding-protection',
                        '--memory-pressure-off',
                        '--disable-seccomp-filter-sandbox',
                        '--disable-software-rasterizer',
                        '--disable-extensions',
                        '--disable-plugins',
                        '--disable-images',
                        '--disable-javascript',
                        '--virtual-time-budget=60000',
                        '--single-process',
                        '--no-zygote',
                        '--disable-namespace-sandbox'
                    ]);
            });

        return $pdf;
    }

    /**
     * Generar PDF y guardarlo en storage
     */
    public function store(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load([
            'company',
            'client', 
            'details.product',
            'paymentInstallments'
        ]);

        // Generar nombre del archivo
        $filename = $this->generateFilename($invoice);
        $path = 'invoices/' . $filename;

        // Generar PDF y guardarlo con argumentos seguros de Chromium
        $pdf = pdf()
            ->view('pdf.invoice', compact('invoice'))
            ->format(config('invoice-pdf.format', 'A4'))
            ->margins(
                config('invoice-pdf.margins.top', 10),
                config('invoice-pdf.margins.right', 10),
                config('invoice-pdf.margins.bottom', 10),
                config('invoice-pdf.margins.left', 10)
            )
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->noSandbox()
                    ->setOption('args', [
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--disable-web-security',
                        '--disable-features=VizDisplayCompositor',
                        '--run-all-compositor-stages-before-draw',
                        '--disable-backgrounding-occluded-windows',
                        '--disable-renderer-backgrounding',
                        '--disable-field-trial-config',
                        '--disable-ipc-flooding-protection',
                        '--memory-pressure-off',
                        '--disable-seccomp-filter-sandbox',
                        '--disable-software-rasterizer',
                        '--disable-extensions',
                        '--disable-plugins',
                        '--disable-images',
                        '--disable-javascript',
                        '--virtual-time-budget=60000',
                        '--single-process',
                        '--no-zygote',
                        '--disable-namespace-sandbox'
                    ]);
            });

        // Guardar en storage
        $pdfContent = $pdf->getBrowsershot()->pdf();
        \Storage::disk('public')->put($path, $pdfContent);

        return response()->json([
            'success' => true,
            'message' => 'PDF generado y guardado correctamente',
            'path' => $path,
            'url' => \Storage::disk('public')->url($path)
        ]);
    }

    /**
     * Generar múltiples PDFs (para descarga masiva)
     */
    public function downloadMultiple(Request $request)
    {
        $invoiceIds = $request->input('invoices', []);
        
        if (empty($invoiceIds)) {
            return response()->json(['error' => 'No se seleccionaron facturas'], 400);
        }

        $invoices = Invoice::with([
            'company',
            'client', 
            'details.product',
            'paymentInstallments'
        ])->whereIn('id', $invoiceIds)->get();

        if ($invoices->isEmpty()) {
            return response()->json(['error' => 'No se encontraron facturas'], 404);
        }

        // Si es solo una factura, descargar directamente
        if ($invoices->count() === 1) {
            return $this->download($invoices->first());
        }

        // Para múltiples facturas, crear un ZIP
        return $this->createZipDownload($invoices);
    }

    /**
     * Generar nombre del archivo PDF
     */
    private function generateFilename(Invoice $invoice): string
    {
        $documentType = match($invoice->document_type) {
            '01' => 'FACTURA',
            '03' => 'BOLETA',
            '07' => 'NOTA_CREDITO',
            '08' => 'NOTA_DEBITO',
            '09' => 'NOTA_VENTA',
            default => 'COMPROBANTE'
        };
        
        return $documentType . '_' . $invoice->full_number . '.pdf';
    }

    /**
     * Crear descarga ZIP para múltiples facturas
     */
    private function createZipDownload($invoices)
    {
        $zip = new \ZipArchive();
        $zipFileName = 'facturas_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Crear directorio temporal si no existe
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($invoices as $invoice) {
                $filename = $this->generateFilename($invoice);
                
                // Generar PDF con argumentos seguros de Chromium
                $pdfBuilder = pdf()
                    ->view('pdf.invoice', compact('invoice'))
                    ->format(config('invoice-pdf.format', 'A4'))
                    ->margins(
                        config('invoice-pdf.margins.top', 10),
                        config('invoice-pdf.margins.right', 10),
                        config('invoice-pdf.margins.bottom', 10),
                        config('invoice-pdf.margins.left', 10)
                    )
                    ->withBrowsershot(function ($browsershot) {
                        $browsershot->addChromiumArguments([
                            '--no-sandbox',
                            '--disable-setuid-sandbox',
                            '--disable-dev-shm-usage',
                            '--disable-gpu',
                            '--disable-web-security',
                            '--disable-features=VizDisplayCompositor',
                            '--run-all-compositor-stages-before-draw',
                            '--disable-backgrounding-occluded-windows',
                            '--disable-renderer-backgrounding',
                            '--disable-field-trial-config',
                            '--disable-ipc-flooding-protection',
                            '--memory-pressure-off'
                        ]);
                    });

                $pdfContent = $pdfBuilder->getBrowsershot()->pdf();

                // Agregar al ZIP
                $zip->addFromString($filename, $pdfContent);
            }
            
            $zip->close();

            // Descargar y eliminar archivo temporal
            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        return response()->json(['error' => 'Error al crear el archivo ZIP'], 500);
    }

    /**
     * Previsualizar PDF (para testing)
     */
    public function preview(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load([
            'company',
            'client',
            'details.product',
            'paymentInstallments'
        ]);

        // Retornar la vista HTML (sin PDF) para debugging
        return view('pdf.invoice', compact('invoice'));
    }

    /**
     * Vista previa HTML (alternativa temporal mientras se resuelve Browsershot)
     */
    public function htmlPreview(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load([
            'company',
            'client',
            'details.product',
            'paymentInstallments'
        ]);

        // Retornar la vista HTML directamente en una ventana nueva
        return view('pdf.invoice', compact('invoice'))
            ->with('preview_mode', true);
    }

    /**
     * Generar PDF temporal para MediaAction
     */
    public function temporaryUrl(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load([
            'company',
            'client', 
            'details.product',
            'paymentInstallments'
        ]);

        // Configurar PDF con argumentos seguros de Chromium
        $pdf = pdf()
            ->view('pdf.invoice', compact('invoice'))
            ->format(config('invoice-pdf.format', 'A4'))
            ->margins(
                config('invoice-pdf.margins.top', 10),
                config('invoice-pdf.margins.right', 10),
                config('invoice-pdf.margins.bottom', 10),
                config('invoice-pdf.margins.left', 10)
            )
            ->name($this->generateFilename($invoice))
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->noSandbox()
                    ->setOption('args', [
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--disable-web-security',
                        '--disable-features=VizDisplayCompositor',
                        '--run-all-compositor-stages-before-draw',
                        '--disable-backgrounding-occluded-windows',
                        '--disable-renderer-backgrounding',
                        '--disable-field-trial-config',
                        '--disable-ipc-flooding-protection',
                        '--memory-pressure-off',
                        '--disable-seccomp-filter-sandbox',
                        '--disable-software-rasterizer',
                        '--disable-extensions',
                        '--disable-plugins',
                        '--disable-images',
                        '--disable-javascript',
                        '--virtual-time-budget=60000',
                        '--single-process',
                        '--no-zygote',
                        '--disable-namespace-sandbox'
                    ]);
            });

        // Retornar PDF directamente
        return $pdf->inline();
    }

    /**
     * Generar ticket de 80mm para impresión
     */
    public function ticket(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load([
            'company',
            'client', 
            'details.product'
        ]);

        // Configurar PDF para ticket 80mm con argumentos seguros de Chromium
        $pdf = pdf()
            ->view('pdf.ticket-80mm', compact('invoice'))
            ->paperSize(80, 200, 'mm') // 80mm ancho, 200mm alto (se ajusta automáticamente)
            ->margins(0, 0, 0, 0) // Sin márgenes
            ->name($this->generateTicketFilename($invoice))
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->noSandbox()
                    ->setOption('args', [
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--disable-web-security',
                        '--disable-features=VizDisplayCompositor',
                        '--run-all-compositor-stages-before-draw',
                        '--disable-backgrounding-occluded-windows',
                        '--disable-renderer-backgrounding',
                        '--disable-field-trial-config',
                        '--disable-ipc-flooding-protection',
                        '--memory-pressure-off',
                        '--disable-seccomp-filter-sandbox',
                        '--disable-software-rasterizer',
                        '--disable-extensions',
                        '--disable-plugins',
                        '--disable-images',
                        '--disable-javascript',
                        '--virtual-time-budget=60000',
                        '--single-process',
                        '--no-zygote',
                        '--disable-namespace-sandbox'
                    ]);
            });

        return $pdf->download();
    }

    /**
     * Ver ticket de 80mm en navegador
     */
    public function ticketView(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load([
            'company',
            'client', 
            'details.product'
        ]);

        // Configurar PDF para ticket 80mm con argumentos seguros de Chromium
        $pdf = pdf()
            ->view('pdf.ticket-80mm', compact('invoice'))
            ->paperSize(80, 200, 'mm')
            ->margins(0, 0, 0, 0)
            ->name($this->generateTicketFilename($invoice))
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->noSandbox()
                    ->setOption('args', [
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--disable-web-security',
                        '--disable-features=VizDisplayCompositor',
                        '--run-all-compositor-stages-before-draw',
                        '--disable-backgrounding-occluded-windows',
                        '--disable-renderer-backgrounding',
                        '--disable-field-trial-config',
                        '--disable-ipc-flooding-protection',
                        '--memory-pressure-off',
                        '--disable-seccomp-filter-sandbox',
                        '--disable-software-rasterizer',
                        '--disable-extensions',
                        '--disable-plugins',
                        '--disable-images',
                        '--disable-javascript',
                        '--virtual-time-budget=60000',
                        '--single-process',
                        '--no-zygote',
                        '--disable-namespace-sandbox'
                    ]);
            });

        return $pdf->inline();
    }

    /**
     * Generar nombre de archivo para ticket
     */
    private function generateTicketFilename(Invoice $invoice): string
    {
        $documentType = match($invoice->document_type) {
            '01' => 'TICKET_FACTURA',
            '03' => 'TICKET_BOLETA',
            '07' => 'TICKET_NOTA_CREDITO',
            '08' => 'TICKET_NOTA_DEBITO',
            '09' => 'TICKET_NOTA_VENTA',
            default => 'TICKET_COMPROBANTE'
        };
        return $documentType . '_' . $invoice->full_number . '.pdf';
    }
}