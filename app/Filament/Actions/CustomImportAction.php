<?php

namespace App\Filament\Actions;

use Filament\Actions\ImportAction;
use Filament\Actions\Action;
use App\Services\ProductTemplateService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Set;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Validation\ValidationException;
use League\Csv\Reader as CsvReader;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Filament\Actions\Imports\ImportColumn;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomImportAction extends ImportAction
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Sobrescribir completamente el schema para mejor UX
        $this->schema(fn (ImportAction $action): array => array_merge([
            FileUpload::make('file')
                ->label('Archivo de Importación')
                ->placeholder('Selecciona un archivo CSV o Excel')
                ->acceptedFileTypes([
                    'text/csv', 
                    'text/x-csv', 
                    'application/csv', 
                    'application/x-csv', 
                    'text/comma-separated-values', 
                    'text/x-comma-separated-values', 
                    'text/plain', 
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ])
                ->rules([
                    'required',
                    'file',
                    'mimes:csv,txt,xls,xlsx',
                    'extensions:csv,txt,xls,xlsx'
                ])
                ->helperText('
                    Formatos soportados: Excel (.xlsx, .xls) o CSV (.csv)
                    
                    📥 **Usa nuestra plantilla Excel para mejores resultados**
                ')
                ->afterStateUpdated(function (FileUpload $component, Component $livewire, Set $set, ?TemporaryUploadedFile $state) use ($action): void {
                    Log::channel('carga')->info('=== ARCHIVO SUBIDO ===');
                    Log::channel('carga')->info('Tipo de archivo:', ['type' => get_class($state)]);
                    
                    if (! $state instanceof TemporaryUploadedFile) {
                        Log::channel('carga')->error('ERROR: El archivo no es TemporaryUploadedFile');
                        return;
                    }
                    
                    Log::channel('carga')->info('Archivo válido:', [
                        'name' => $state->getClientOriginalName(),
                        'size' => $state->getSize(),
                        'mime' => $state->getMimeType()
                    ]);

                    try {
                        Log::channel('carga')->info('Validando archivo...');
                        $livewire->validateOnly($component->getStatePath());
                        Log::channel('carga')->info('Archivo validado exitosamente');
                    } catch (ValidationException $exception) {
                        Log::channel('carga')->error('Error de validación:', ['error' => $exception->getMessage()]);
                        $component->state([]);
                        throw $exception;
                    }

                    Log::channel('carga')->info('Obteniendo stream del archivo...');
                    $csvStream = $this->getUploadedFileStream($state);

                    if (! $csvStream) {
                        Log::channel('carga')->error('ERROR: No se pudo obtener el stream del archivo');
                        return;
                    }
                    
                    Log::channel('carga')->info('Stream obtenido exitosamente');
                    
                    // Verificar si es archivo Excel
                    $fileName = $state->getClientOriginalName();
                    $isExcel = str_ends_with(strtolower($fileName), '.xlsx') || str_ends_with(strtolower($fileName), '.xls');
                    
                    if ($isExcel) {
                        Log::channel('carga')->info('Archivo Excel detectado, convirtiendo a CSV...');
                        
                        // Leer Excel y convertir a array
                        $excelData = Excel::toArray(new class implements ToArray, WithHeadingRow {
                            public function array(array $array) {
                                return $array;
                            }
                            public function headingRow(): int {
                                return 1;
                            }
                        }, $state->getRealPath());
                        
                        Log::channel('carga')->info('Datos Excel leídos:', $excelData);
                        
                        // Obtener la primera hoja
                        $sheetData = $excelData[0] ?? [];
                        
                        if (empty($sheetData)) {
                            Log::channel('carga')->error('ERROR: Archivo Excel vacío');
                            return;
                        }
                        
                        // Obtener cabeceras (primera fila)
                        $csvColumns = array_keys($sheetData[0] ?? []);
                        Log::channel('carga')->info('Cabeceras Excel encontradas:', $csvColumns);
                        
                    } else {
                        Log::channel('carga')->info('Archivo CSV detectado, procesando normalmente...');
                        $csvReader = CsvReader::createFromStream($csvStream);

                        if (filled($csvDelimiter = $this->getCsvDelimiter($csvReader))) {
                            $csvReader->setDelimiter($csvDelimiter);
                        }

                        $csvReader->setHeaderOffset($action->getHeaderOffset() ?? 0);

                        Log::channel('carga')->info('Leyendo cabeceras del archivo...');
                        $csvColumns = $csvReader->getHeader();
                        Log::channel('carga')->info('Cabeceras encontradas:', $csvColumns);
                    }

                    $lowercaseCsvColumnValues = array_map(Str::lower(...), $csvColumns);
                    $lowercaseCsvColumnKeys = array_combine(
                        $lowercaseCsvColumnValues,
                        $csvColumns,
                    );

                    // Mapeo automático de columnas - sin mostrar al usuario
                    Log::channel('carga')->info('Iniciando mapeo de columnas...');
                    $columnMap = array_reduce($action->getImporter()::getColumns(), function (array $carry, ImportColumn $column) use ($lowercaseCsvColumnKeys, $lowercaseCsvColumnValues) {
                        $guesses = $column->getGuesses();
                        $matched = Arr::first(
                            array_intersect(
                                $lowercaseCsvColumnValues,
                                array_map('strtolower', $guesses),
                            ),
                        );
                        $carry[$column->getName()] = $lowercaseCsvColumnKeys[$matched] ?? null;
                        
                        Log::channel('carga')->info('Mapeo columna:', [
                            'campo' => $column->getName(),
                            'guesses' => $guesses,
                            'matched' => $matched,
                            'resultado' => $carry[$column->getName()]
                        ]);

                        return $carry;
                    }, []);
                    
                    Log::channel('carga')->info('Mapeo final de columnas:', $columnMap);
                    $set('columnMap', $columnMap);
                })
                ->storeFiles(false)
                ->visibility('private')
                ->required()
                ->hiddenLabel(),
            
            // Campo oculto para el mapeo de columnas
            Hidden::make('columnMap')
                ->default([]),
        ], $action->getImporter()::getOptionsFormComponents()));
        
        // Sobrescribir la acción principal de importación
        $this->action(function (ImportAction $action, array $data): void {
            Log::channel('carga')->info('=== INICIANDO IMPORTACIÓN ===');
            Log::channel('carga')->info('Datos recibidos en action:', $data);
            
            /** @var TemporaryUploadedFile $file */
            $file = $data['file'];
            $fileName = $file->getClientOriginalName();
            $isExcel = str_ends_with(strtolower($fileName), '.xlsx') || str_ends_with(strtolower($fileName), '.xls');
            
            if ($isExcel) {
                Log::channel('carga')->info('Procesando archivo Excel para importación...');
                $this->processExcelImport($action, $file, $data);
            } else {
                Log::channel('carga')->info('Procesando archivo CSV, usando método original...');
                // Llamar al método original para CSV
                parent::action($action, $data);
            }
        });
        
        // Reemplazar la acción downloadExample
        $this->registerModalActions([
            Action::make('downloadExample')
                ->label('Descargar archivo Excel de ejemplo')
                ->link()
                ->action(fn () => ProductTemplateService::generateExcelTemplate()),
        ]);
    }
    
    private function processExcelImport(ImportAction $action, TemporaryUploadedFile $file, array $data): void
    {
        Log::channel('carga')->info('=== PROCESANDO EXCEL IMPORT ===');
        
        try {
            // Leer Excel y convertir a array
            $excelData = Excel::toArray(new class implements ToArray, WithHeadingRow {
                public function array(array $array) {
                    return $array;
                }
                public function headingRow(): int {
                    return 1;
                }
            }, $file->getRealPath());
            
            // Obtener la primera hoja (datos)
            $sheetData = $excelData[0] ?? [];
            
            if (empty($sheetData)) {
                Log::channel('carga')->error('ERROR: Archivo Excel vacío');
                return;
            }
            
            Log::channel('carga')->info('Filas a procesar:', ['count' => count($sheetData)]);
            
            // Crear el import record
            $import = app(\Filament\Actions\Imports\Models\Import::class);
            $import->user()->associate(auth()->user());
            $import->file_name = $file->getClientOriginalName();
            $import->file_path = $file->getRealPath();
            $import->importer = $action->getImporter();
            $import->total_rows = count($sheetData);
            $import->save();
            
            Log::channel('carga')->info('Import record creado:', ['id' => $import->id]);
            
            // Procesar cada fila
            $successCount = 0;
            $failCount = 0;
            
            foreach ($sheetData as $index => $row) {
                Log::channel('carga')->info('Procesando fila:', ['index' => $index, 'data' => $row]);
                
                try {
                    // Mapeo de columnas Excel (español) a campos internos (inglés)
                    $fieldMapping = [
                        'codigo' => 'code',
                        'nombre' => 'name',
                        'precio' => 'price',
                        'stock' => 'stock',
                        'categoria' => 'category',
                        'marca' => 'brand',
                        'codigo_de_barras' => 'barcode',
                        'descripcion' => 'description',
                        'unidad_de_medida' => 'unit_code',
                        'tipo_de_igv' => 'tax_type',
                        'precio_de_costo' => 'cost_price',
                        'precio_de_venta' => 'sale_price'
                    ];
                    
                    // Convertir datos de Excel a formato interno
                    $mappedData = [];
                    foreach ($row as $excelKey => $value) {
                        if (isset($fieldMapping[$excelKey])) {
                            $mappedData[$fieldMapping[$excelKey]] = $value;
                        }
                    }
                    
                    Log::channel('carga')->info('Datos mapeados:', $mappedData);
                    
                    // Crear columnMap para el constructor
                    $columnMap = $fieldMapping;
                    
                    // Crear instancia del importer
                    $importer = new ($action->getImporter())(
                        import: $import,
                        columnMap: $columnMap,
                        options: array_merge(
                            $action->getOptions(),
                            \Illuminate\Support\Arr::except($data, ['file', 'columnMap'])
                        )
                    );
                    
                    // Establecer los datos mapeados usando reflection
                    $reflection = new \ReflectionClass($importer);
                    $dataProperty = $reflection->getProperty('data');
                    $dataProperty->setAccessible(true);
                    $dataProperty->setValue($importer, $mappedData);
                    
                    Log::channel('carga')->info('Datos establecidos en importer');
                    
                    // Procesar la fila
                    $record = $importer->resolveRecord();
                    
                    if ($record) {
                        // Establecer el record usando reflection
                        $recordProperty = $reflection->getProperty('record');
                        $recordProperty->setAccessible(true);
                        $recordProperty->setValue($importer, $record);
                        
                        Log::channel('carga')->info('Record establecido, ejecutando beforeSave...');
                        
                        // Usar reflection para llamar al método protegido beforeSave
                        $beforeSaveMethod = $reflection->getMethod('beforeSave');
                        $beforeSaveMethod->setAccessible(true);
                        $beforeSaveMethod->invoke($importer);
                        
                        Log::channel('carga')->info('Guardando record...');
                        $record->save();
                        
                        Log::channel('carga')->info('Ejecutando afterSave...');
                        
                        // Usar reflection para llamar al método protegido afterSave
                        $afterSaveMethod = $reflection->getMethod('afterSave');
                        $afterSaveMethod->setAccessible(true);
                        $afterSaveMethod->invoke($importer);
                        
                        $successCount++;
                        Log::channel('carga')->info('Fila procesada exitosamente:', ['index' => $index]);
                    } else {
                        $failCount++;
                        Log::channel('carga')->warning('Fila fallida (record null):', ['index' => $index]);
                    }
                    
                } catch (\Exception $e) {
                    $failCount++;
                    Log::channel('carga')->error('Error procesando fila:', [
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            // Actualizar el import record (solo campos que existen)
            $import->update([
                'completed_at' => now()
            ]);
            
            Log::channel('carga')->info('Importación completada:', [
                'successful' => $successCount,
                'failed' => $failCount
            ]);
            
            // Mostrar notificación
            \Filament\Notifications\Notification::make()
                ->title('Importación completada')
                ->body("Se importaron {$successCount} productos exitosamente. {$failCount} filas fallaron.")
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Log::channel('carga')->error('Error general en importación:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            \Filament\Notifications\Notification::make()
                ->title('Error en importación')
                ->body('Ocurrió un error durante la importación: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}