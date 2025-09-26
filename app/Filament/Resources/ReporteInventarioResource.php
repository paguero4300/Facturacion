<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReporteInventarioResource\Pages;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use BackedEnum;
use UnitEnum;

class ReporteInventarioResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';
    
    protected static ?string $navigationLabel = 'Reporte Inventario';
    
    protected static UnitEnum|string|null $navigationGroup = 'Inventario';
    
    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'reporte-inventario';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ReporteInventarioIndex::route('/'),
            'stock-actual' => Pages\StockActualPage::route('/stock-actual'),
            'stock-minimo' => Pages\StockMinimoPage::route('/stock-minimo'),
            'kardex-sencillo' => Pages\KardexSencilloPage::route('/kardex-sencillo'),
        ];
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Reporte Inventario';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}