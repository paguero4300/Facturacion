<?php

namespace App\Filament\Resources\ReporteInventarioResource\Pages;

use App\Filament\Resources\ReporteInventarioResource;
use App\Models\Product;
use App\Models\Stock;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;

class ReporteInventarioIndex extends Page
{
    protected static string $resource = ReporteInventarioResource::class;
    
    protected string $view = 'filament.resources.reporte-inventario.pages.index';

    protected ?string $heading = 'Reporte de Inventario';

    protected ?string $subheading = 'Gestión integral de reportes de inventario';

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getViewData(): array
    {
        return [
            'totalProducts' => Product::where('track_inventory', true)->where('status', 'active')->count(),
            'lowStockProducts' => Stock::lowStock()->count(),
            'totalWarehouses' => \App\Models\Warehouse::count(),
            'totalMovements' => \App\Models\InventoryMovement::whereDate('created_at', today())->count(),
        ];
    }

    public function mount(): void
    {
        // Inicialización si es necesaria
    }
}