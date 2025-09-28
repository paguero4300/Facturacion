<?php

namespace App\Filament\Resources\SalesChannelResource\Pages;

use App\Filament\Resources\SalesChannelResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;

class ListSalesChannel extends ListRecords
{
    protected static string $resource = SalesChannelResource::class;

    protected ?string $heading = 'Reporte de Ventas';

    protected ?string $subheading = 'Análisis detallado de comprobantes de venta con filtros avanzados y sumatorias';

    public function getTitle(): string
    {
        return 'Reporte de Ventas';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getFilteredTableQuery(): Builder
    {
        return $this->getTableQuery();
    }
}