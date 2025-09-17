<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            // Columnas según formato corregido para carga masiva
            ExportColumn::make('code')
                ->label('Código'),
            ExportColumn::make('name')
                ->label('Nombre'),
            ExportColumn::make('description')
                ->label('Descripción'),
            ExportColumn::make('model')
                ->label('Modelo'),
            ExportColumn::make('unit_description')
                ->label('Unidad de medida'),
            ExportColumn::make('taxable')
                ->label('Posee IGV')
                ->formatStateUsing(fn ($state) => $state ? 'SI' : 'NO'),
            ExportColumn::make('category.name')
                ->label('Categoría'),
            ExportColumn::make('brand.name')
                ->label('Marca'),
            ExportColumn::make('sale_price')
                ->label('Precio'),
            ExportColumn::make('created_at')
                ->label('Fecha de vencimiento')
                ->formatStateUsing(fn ($state) => ''), // Campo vacío por defecto
            ExportColumn::make('unit_price')
                ->label('Precio Unidad 1'),
            ExportColumn::make('unit_description')
                ->label('Descripción Unidad 1'),
            ExportColumn::make('created_at')
                ->label('Factor Unidad 1')
                ->formatStateUsing(fn ($state) => '1'), // Factor por defecto
            ExportColumn::make('cost_price')
                ->label('Precio Costo Unidad 1'),
            ExportColumn::make('created_at')
                ->label('Precio Unidad 2')
                ->formatStateUsing(fn ($state) => ''), // Campo vacío
            ExportColumn::make('created_at')
                ->label('Descripción Unidad 2')
                ->formatStateUsing(fn ($state) => ''), // Campo vacío
            ExportColumn::make('created_at')
                ->label('Factor Unidad 2')
                ->formatStateUsing(fn ($state) => ''), // Campo vacío
            ExportColumn::make('created_at')
                ->label('Precio Costo Unidad 2')
                ->formatStateUsing(fn ($state) => ''), // Campo vacío
            ExportColumn::make('current_stock')
                ->label('Stock actual'),
            ExportColumn::make('image_path')
                ->label('imagenes'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
