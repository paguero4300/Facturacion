<?php

namespace App\Filament\Resources\StockActualResource\Pages;

use App\Filament\Resources\StockActualResource;
use Filament\Resources\Pages\ListRecords;

class ListStockActual extends ListRecords
{

    protected static string $resource = StockActualResource::class;

    protected ?string $heading = 'Reporte de Stock Actual';

    protected ?string $subheading = 'Inventario actual de todos los productos con seguimiento habilitado';


    public function getTitle(): string
    {
        return 'Stock Actual';
    }
}