<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductTemplateExporter extends Exporter
{
    protected static ?string $model = null; // No necesitamos modelo para plantilla

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('codigo')
                ->label('Código')
                ->state(fn () => 'PROD001'),
            
            ExportColumn::make('nombre')
                ->label('Nombre')
                ->state(fn () => 'Producto de Ejemplo'),
            
            ExportColumn::make('descripcion')
                ->label('Descripción')
                ->state(fn () => 'Descripción detallada del producto'),
            
            ExportColumn::make('unidad')
                ->label('Unidad')
                ->state(fn () => 'UND'),
            
            ExportColumn::make('descripcion_unidad')
                ->label('Descripción Unidad')
                ->state(fn () => 'UNIDAD'),
            
            ExportColumn::make('posee_igv')
                ->label('Posee IGV')
                ->state(fn () => 'SI'),
            
            ExportColumn::make('factor_unidad_1')
                ->label('Factor Unidad 1')
                ->state(fn () => '1'),
            
            ExportColumn::make('precio_unitario')
                ->label('Precio Unitario')
                ->state(fn () => '10.00'),
            
            ExportColumn::make('precio_venta')
                ->label('Precio Venta')
                ->state(fn () => '11.80'),
            
            ExportColumn::make('precio_costo')
                ->label('Precio Costo')
                ->state(fn () => '8.00'),
            
            ExportColumn::make('precio_minimo')
                ->label('Precio Mínimo')
                ->state(fn () => '9.00'),
            
            ExportColumn::make('stock_actual')
                ->label('Stock Actual')
                ->state(fn () => '100'),
            
            ExportColumn::make('stock_minimo')
                ->label('Stock Mínimo')
                ->state(fn () => '10'),
            
            ExportColumn::make('stock_maximo')
                ->label('Stock Máximo')
                ->state(fn () => '500'),
            
            ExportColumn::make('categoria')
                ->label('Categoría')
                ->state(fn () => 'Electrónicos'),
            
            ExportColumn::make('marca')
                ->label('Marca')
                ->state(fn () => 'Samsung'),
            
            ExportColumn::make('modelo')
                ->label('Modelo')
                ->state(fn () => 'Galaxy S23'),
            
            ExportColumn::make('peso')
                ->label('Peso (kg)')
                ->state(fn () => '0.168'),
            
            ExportColumn::make('codigo_barras')
                ->label('Código de Barras')
                ->state(fn () => '1234567890123'),
            
            ExportColumn::make('imagen_url')
                ->label('URL de Imagen')
                ->state(fn () => 'https://ejemplo.com/imagen-producto.jpg'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Plantilla de productos descargada exitosamente.';

        return $body;
    }

    // Método para generar datos de ejemplo
    public function getRecords()
    {
        // Retornamos un array con un registro de ejemplo
        return collect([new \stdClass()]);
    }
}