<?php

namespace App\Filament\Resources\InventoryReports;

use App\Filament\Resources\InventoryReports\Pages\InventoryReportsPage;
use App\Models\InventoryReport;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use BackedEnum;

class InventoryReportResource extends Resource
{
    protected static ?string $model = InventoryReport::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'Reportes de Inventario';
    
    protected static UnitEnum|string|null $navigationGroup = 'Inventario';
    
    protected static ?int $navigationSort = 4;

    public static function getPages(): array
    {
        return [
            'index' => InventoryReportsPage::route('/'),
        ];
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Reportes';
    }
}
