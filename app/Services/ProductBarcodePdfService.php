<?php

namespace App\Services;

use App\Models\Product;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProductBarcodePdfService
{
    /**
     * Generar PDF con códigos de barras de productos
     */
    public static function generateBarcodePdf(Collection $products)
    {
        Log::channel('pdf')->info('=== INICIANDO GENERACIÓN PDF ===');
        Log::channel('pdf')->info('Número de productos recibidos:', ['count' => $products->count()]);
        
        try {
            // Preparar datos para el PDF
            $data = [
                'products' => $products,
                'title' => 'CÓDIGOS DE BARRAS - PRODUCTOS',
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'total_products' => $products->count()
            ];
            
            Log::channel('pdf')->info('Datos preparados para PDF:', $data);
            
            // Verificar que la vista existe
            if (!view()->exists('pdf.product-barcodes')) {
                Log::channel('pdf')->error('ERROR: Vista pdf.product-barcodes no existe');
                throw new \Exception('Vista PDF no encontrada');
            }
            
            Log::channel('pdf')->info('Vista PDF encontrada, generando PDF...');
            
            // Generar PDF usando Spatie Laravel PDF
            $fileName = 'codigos-barras-productos-' . now()->format('Y-m-d-H-i-s') . '.pdf';
            Log::channel('pdf')->info('Nombre del archivo:', ['filename' => $fileName]);
            
            $pdfBuilder = Pdf::view('pdf.product-barcodes', $data)
                ->format('a4')
                ->orientation('portrait')
                ->margins(15, 15, 15, 15);
                
            Log::channel('pdf')->info('PDF Builder creado, generando contenido base64...');
            
            // Usar streamDownload con base64 (método recomendado por Spatie)
            return response()->streamDownload(function () use ($pdfBuilder) {
                Log::channel('pdf')->info('Generando contenido base64...');
                echo base64_decode($pdfBuilder->base64());
                Log::channel('pdf')->info('Contenido base64 decodificado y enviado');
            }, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);
            
            Log::channel('pdf')->info('StreamDownload response creado exitosamente');
            
        } catch (\Exception $e) {
            Log::channel('pdf')->error('ERROR en generación PDF:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener productos filtrados del listado actual
     */
    public static function getFilteredProducts($tableQuery = null): Collection
    {
        Log::channel('pdf')->info('=== OBTENIENDO PRODUCTOS ===');
        Log::channel('pdf')->info('TableQuery recibido:', ['has_query' => $tableQuery !== null]);
        
        try {
            if ($tableQuery) {
                Log::channel('pdf')->info('Usando query filtrada de la tabla');
                // Usar la query filtrada de la tabla
                $products = $tableQuery
                    ->select(['code', 'name', 'barcode'])
                    ->orderBy('code')
                    ->get();
                    
                Log::channel('pdf')->info('Productos obtenidos de query filtrada:', ['count' => $products->count()]);
                return $products;
            }
            
            // Fallback: obtener todos los productos activos
            $companyId = auth()->user()->company_id ?? 2;
            Log::channel('pdf')->info('Usando fallback, company_id:', ['company_id' => $companyId]);
            
            $products = Product::where('company_id', $companyId)
                ->where('status', 'active')
                ->select(['code', 'name', 'barcode'])
                ->orderBy('code')
                ->get();
                
            Log::channel('pdf')->info('Productos obtenidos de fallback:', ['count' => $products->count()]);
            
            if ($products->count() > 0) {
                Log::channel('pdf')->info('Muestra de productos:', [
                    'first_product' => $products->first()->toArray()
                ]);
            }
            
            return $products;
            
        } catch (\Exception $e) {
            Log::channel('pdf')->error('ERROR obteniendo productos:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}