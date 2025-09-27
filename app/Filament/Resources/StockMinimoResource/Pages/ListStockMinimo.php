<?php

namespace App\Filament\Resources\StockMinimoResource\Pages;

use App\Filament\Resources\StockMinimoResource;
use Filament\Resources\Pages\ListRecords;

class ListStockMinimo extends ListRecords
{

    protected static string $resource = StockMinimoResource::class;

    protected ?string $heading = 'Reporte de Stock Mínimo';

    protected ?string $subheading = 'Productos con stock bajo o crítico que requieren reposición urgente';


    public function getTitle(): string
    {
        return 'Stock Mínimo';
    }
}