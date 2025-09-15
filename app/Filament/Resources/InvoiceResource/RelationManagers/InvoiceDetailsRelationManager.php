<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class InvoiceDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    protected static ?string $recordTitleAttribute = 'description';

    public static function getModelLabel(): string
    {
        return __('Detalle');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Detalles');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('line_number')
                    ->label(__('Línea')),
                    
                TextColumn::make('product.name')
                    ->label(__('Producto')),
                    
                TextColumn::make('description')
                    ->label(__('Descripción')),
                    
                TextColumn::make('quantity')
                    ->numeric(2)
                    ->label(__('Cantidad')),
                    
                TextColumn::make('unit_price')
                    ->money('PEN')
                    ->label(__('Precio Unitario')),
                    
                TextColumn::make('line_discount_amount')
                    ->money('PEN')
                    ->label(__('Descuento')),
                    
                TextColumn::make('net_amount')
                    ->money('PEN')
                    ->label(__('Neto')),
                    
                TextColumn::make('igv_amount')
                    ->money('PEN')
                    ->label(__('IGV')),
                    
                TextColumn::make('line_total')
                    ->money('PEN')
                    ->label(__('Total')),
                    
                IconColumn::make('is_free')
                    ->boolean()
                    ->label(__('Gratuito')),
            ])
            ->defaultSort('line_number', 'asc');
    }
}
