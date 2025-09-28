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
        return [
            Actions\Action::make('resumen')
                ->label('Ver Resumen')
                ->icon('heroicon-o-chart-pie')
                ->color('info')
                ->modalHeading('Resumen de Ventas')
                ->modalContent(function () {
                    $query = $this->getFilteredTableQuery();
                    
                    $totalVentas = $query->sum('total_amount');
                    $cantidadComprobantes = $query->count();
                    
                    $ventasPorTipo = $query->selectRaw('document_type, COUNT(*) as cantidad, SUM(total_amount) as total')
                        ->groupBy('document_type')
                        ->get();
                    
                    $ventasPorMetodo = $query->selectRaw('payment_method, COUNT(*) as cantidad, SUM(total_amount) as total')
                        ->groupBy('payment_method')
                        ->get();

                    return view('filament.pages.sales-summary', [
                        'totalVentas' => $totalVentas,
                        'cantidadComprobantes' => $cantidadComprobantes,
                        'ventasPorTipo' => $ventasPorTipo,
                        'ventasPorMetodo' => $ventasPorMetodo,
                    ]);
                })
                ->modalWidth('4xl'),
        ];
    }

    public function getFilteredTableQuery(): Builder
    {
        return $this->getTableQuery();
    }
}