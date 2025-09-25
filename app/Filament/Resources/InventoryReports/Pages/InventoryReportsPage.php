<?php

namespace App\Filament\Resources\InventoryReports\Pages;

use App\Filament\Resources\InventoryReports\InventoryReportResource;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class InventoryReportsPage extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static string $resource = InventoryReportResource::class;
    
    protected string $view = 'filament.resources.inventory-reports.pages.inventory-reports-page';
    
    public string $activeTab = 'existencias';
    
    public function mount(): void
    {
        $this->activeTab = request()->get('tab', 'existencias');
    }
    
    public function getTitle(): string
    {
        return 'Reportes de Inventario';
    }
    
    // Tabla para Existencias Actuales
    public function getCurrentStockTable(): Table
    {
        return Table::make()
            ->query(
                Stock::query()
                    ->join('products', 'stocks.product_id', '=', 'products.id')
                    ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
                    ->select(
                        'products.name as product_name',
                        'warehouses.name as warehouse_name', 
                        'stocks.quantity as stock_actual',
                        'products.minimum_stock as stock_minimo',
                        'stocks.id'
                    )
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse_name')
                    ->label('Almacén')
                    ->sortable(),
                TextColumn::make('stock_actual')
                    ->label('Stock Actual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->sortable(),
                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->getStateUsing(fn ($record) => 
                        $record->stock_actual <= $record->stock_minimo ? 'Bajo' : 'Normal'
                    )
                    ->colors([
                        'danger' => 'Bajo',
                        'success' => 'Normal',
                    ])
            ])
            ->filters([
                Filter::make('warehouse')
                    ->form([
                        Select::make('warehouse_id')
                            ->label('Almacén')
                            ->options(Warehouse::pluck('name', 'id'))
                            ->placeholder('Todos los almacenes')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['warehouse_id'],
                            fn (Builder $query, $warehouse): Builder => $query->where('stocks.warehouse_id', $warehouse)
                        );
                    })
            ])
            ->defaultSort('product_name');
    }
    
    // Tabla para Bajo Stock
    public function getLowStockTable(): Table
    {
        return Table::make()
            ->query(
                Stock::query()
                    ->join('products', 'stocks.product_id', '=', 'products.id')
                    ->join('warehouses', 'stocks.warehouse_id', '=', 'warehouses.id')
                    ->whereRaw('stocks.quantity <= products.minimum_stock')
                    ->select(
                        'products.name as product_name',
                        'warehouses.name as warehouse_name',
                        'stocks.quantity as stock_actual',
                        'products.minimum_stock as stock_minimo',
                        'stocks.id'
                    )
            )
            ->columns([
                TextColumn::make('product_name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse_name')
                    ->label('Almacén')
                    ->sortable(),
                TextColumn::make('stock_actual')
                    ->label('Stock Actual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->getStateUsing(fn ($record) => $record->stock_actual - $record->stock_minimo)
                    ->numeric()
                    ->color('danger')
            ])
            ->defaultSort('stock_actual');
    }
    
    public function table(Table $table): Table
    {
        return match($this->activeTab) {
            'existencias' => $this->getCurrentStockTable(),
            'bajo-stock' => $this->getLowStockTable(),
            default => $this->getCurrentStockTable()
        };
    }
}