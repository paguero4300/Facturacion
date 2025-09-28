<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportesInventarioResource\Pages;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Filament\Pages\Enums\SubNavigationPosition;
use UnitEnum;
use BackedEnum;

class ReportesInventarioResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reportes';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'reportes-inventario';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\StockActualPage::class,
            Pages\StockMinimoPage::class,
            Pages\KardexPage::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\StockActualPage::route('/'),
            'stock-actual' => Pages\StockActualPage::route('/stock-actual'),
            'stock-minimo' => Pages\StockMinimoPage::route('/stock-minimo'),
            'kardex' => Pages\KardexPage::route('/kardex'),
        ];
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('track_inventory', true)
            ->where('status', 'active')
            ->count();
    }
}