<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Imports\ProductImporter;
use App\Models\Warehouse;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use App\Services\ProductTemplateService;
use App\Filament\Actions\CustomImportAction;
use App\Services\ProductBarcodePdfService;
use Illuminate\Support\Facades\Log;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Botón para descargar PDF de códigos de barras
            Action::make('downloadBarcodePdf')
                ->label('PDF Códigos')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->action(function () {
                    Log::channel('pdf')->info('=== BOTÓN PDF CÓDIGOS PRESIONADO ===');
                    
                    try {
                        // Obtener productos del listado actual (respetando filtros)
                        Log::channel('pdf')->info('Obteniendo query filtrada...');
                        $tableQuery = parent::getFilteredTableQuery();
                        Log::channel('pdf')->info('Query obtenida:', ['query_type' => get_class($tableQuery)]);
                        
                        Log::channel('pdf')->info('Llamando a getFilteredProducts...');
                        $products = ProductBarcodePdfService::getFilteredProducts($tableQuery);
                        
                        Log::channel('pdf')->info('Productos obtenidos, generando PDF...');
                        // Generar y descargar PDF
                        $result = ProductBarcodePdfService::generateBarcodePdf($products);
                        
                        Log::channel('pdf')->info('PDF generado, retornando resultado');
                        return $result;
                        
                    } catch (\Exception $e) {
                        Log::channel('pdf')->error('ERROR en acción del botón:', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        // Mostrar notificación de error al usuario
                        \Filament\Notifications\Notification::make()
                            ->title('Error al generar PDF')
                            ->body('Ocurrió un error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                            
                        throw $e;
                    }
                })
                ->tooltip('Descargar PDF con códigos de barras de productos'),
                
            CustomImportAction::make()
                ->label('Importar Productos')
                ->icon('heroicon-o-arrow-up-tray')
                ->importer(ProductImporter::class)
                ->color('success'),
                
            Actions\CreateAction::make()
                ->label('Crear Producto')
                ->icon('heroicon-o-plus'),
        ];
    }

}