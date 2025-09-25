<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListStocks extends ListRecords
{
    use ExposesTableToWidgets;
    
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No hay acciones de creación ya que es solo lectura
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Aquí se pueden agregar widgets de estadísticas
        ];
    }
}