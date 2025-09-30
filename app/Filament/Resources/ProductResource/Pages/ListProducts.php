<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Imports\ProductImporter;
use App\Models\Warehouse;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Importar Productos')
                ->icon('heroicon-o-arrow-up-tray')
                ->importer(ProductImporter::class)
                ->options(function (array $data): array {
                    return [
                        'warehouse_id' => $data['warehouse_id'] ?? 1,
                    ];
                })
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo CSV/Excel')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required()
                        ->helperText('Formato: CSV o Excel (XLS, XLSX).
                            **Requeridas:** code, name, price, stock
                            **Opcionales:** category, brand, barcode, description, unit_code, tax_type, cost_price, sale_price'),

                    Select::make('warehouse_id')
                        ->label('Almacén de Destino')
                        ->options(function () {
                            // Primero intentar con la empresa del usuario
                            $userCompanyId = auth()->user()->company_id ?? 2;

                            $options = Warehouse::where('company_id', $userCompanyId)
                                ->where('is_active', true)
                                ->pluck('name', 'id')
                                ->toArray();

                            // Si no hay almacenes activos para esa empresa, incluir todos los activos
                            if (empty($options)) {
                                $options = Warehouse::where('is_active', true)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }

                            // Si aún no hay almacenes activos, incluir TODOS los almacenes
                            if (empty($options)) {
                                $options = Warehouse::pluck('name', 'id')->toArray();
                            }

                            return $options;
                        })
                        ->default(1)
                        ->required()
                        ->helperText('Los productos se ingresarán a este almacén'),
                ])
                ->color('success'),
            Actions\CreateAction::make()
                ->label('Crear Producto')
                ->icon('heroicon-o-plus'),
        ];
    }
}