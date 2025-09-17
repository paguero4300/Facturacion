<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Services\ProductImageBulkUploadService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use EightyNine\ExcelImport\ExcelImportAction;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Producto')
                ->icon('heroicon-o-plus'),
            
            Actions\Action::make('download_template')
                ->label('Descargar Plantilla')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    // Crear un archivo Excel usando PhpSpreadsheet
                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();
                    
                    // Definir encabezados de columnas
                    $headers = [
                        'empresa', 'codigo', 'nombre', 'descripcion', 'modelo', 'unidad',
                        'posee_igv', 'categoria', 'marca', 'precio', 'fecha_de_vencimiento',
                        'precio_unidad_1', 'descripcion_unidad_1', 'factor_unidad_1', 'precio_costo_unidad_1',
                        'precio_unidad_2', 'descripcion_unidad_2', 'factor_unidad_2', 'precio_costo_unidad_2',
                        'stock_actual', 'imagenes'
                    ];
                    
                    // Escribir encabezados en la primera fila
                    $sheet->fromArray($headers, null, 'A1');
                    
                    // Escribir fila de ejemplo en la segunda fila
                    $exampleRow = [
                        '20613251988', 'PROD001', 'Producto Ejemplo', 'Descripción del producto ejemplo',
                        'Modelo2024', 'UND', 'SI', 'Electrónicos', 'Samsung', '15.75',
                        '2024-12-31', '15.75', 'Unidad', '1', '10.50', '189.00',
                        'Caja', '12', '126.00', '100', 'productos/ejemplo.jpg'
                    ];
                    $sheet->fromArray($exampleRow, null, 'A2');
                    
                    // Ajustar el ancho de las columnas automáticamente
                    foreach (range('A', $sheet->getHighestColumn()) as $column) {
                        $sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                    
                    // Crear el writer para Excel
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    
                    $filename = 'plantilla-productos-' . now()->format('Y-m-d') . '.xlsx';
                    
                    return response()->streamDownload(function () use ($writer) {
                        $writer->save('php://output');
                    }, $filename, [
                        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                })
                ->tooltip('Descarga una plantilla Excel con formato y ejemplo para importar productos'),
            
            ExcelImportAction::make()
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->use(ProductImporter::class)
                ->slideOver()
                ->modalHeading('Importar Productos desde Excel')
                ->modalDescription('Sube un archivo Excel con los productos siguiendo el formato establecido.')
                ->modalSubmitActionLabel('Importar')
                ->modalCancelActionLabel('Cancelar'),
            
            ExportAction::make()
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->exports([
                    ExcelExport::make('productos')
                        ->fromTable()
                        ->withFilename(fn () => 'productos-' . now()->format('Y-m-d-H-i-s'))
                ])
                ->modalHeading('Exportar Productos')
                ->modalDescription('Descarga todos los productos en formato Excel.')
                ->modalSubmitActionLabel('Exportar')
                ->modalCancelActionLabel('Cancelar'),
            
            Actions\Action::make('bulk_upload_images')
                ->label('Cargar Imágenes Masivamente')
                ->icon('heroicon-o-photo')
                ->color('warning')
                ->form([
                    FileUpload::make('zip_file')
                        ->label('Archivo ZIP con imágenes')
                        ->acceptedFileTypes(['application/zip'])
                        ->maxSize(50 * 1024) // 50MB
                        ->required()
                        ->helperText('Sube un archivo ZIP con imágenes nombradas con el código del producto (ej: PROD001.jpg)')
                ])
                ->action(function (array $data) {
                    $service = new ProductImageBulkUploadService();
                    
                    try {
                        $results = $service->processZipFile($data['zip_file']);
                        
                        $message = "Procesamiento completado: {$results['success']} exitosos, {$results['errors']} errores, {$results['skipped']} omitidos";
                        
                        if ($results['success'] > 0) {
                            Notification::make()
                                ->title('Imágenes cargadas exitosamente')
                                ->body($message)
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No se procesaron imágenes')
                                ->body($message)
                                ->warning()
                                ->send();
                        }
                        
                        // Mostrar detalles si hay errores
                        if ($results['errors'] > 0 || $results['skipped'] > 0) {
                            $details = collect($results['details'])
                                ->where('type', '!=', 'success')
                                ->take(5)
                                ->map(fn($detail) => "{$detail['filename']}: {$detail['message']}")
                                ->join('\n');
                            
                            Notification::make()
                                ->title('Detalles del procesamiento')
                                ->body($details)
                                ->warning()
                                ->persistent()
                                ->send();
                        }
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al procesar el archivo')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->modalHeading('Carga Masiva de Imágenes')
                ->modalDescription('Sube un archivo ZIP con imágenes. Cada imagen debe estar nombrada con el código del producto correspondiente.')
                ->modalSubmitActionLabel('Procesar ZIP')
                ->modalCancelActionLabel('Cancelar'),
        ];
    }
}