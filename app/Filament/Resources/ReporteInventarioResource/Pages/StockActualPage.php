<?php

namespace App\Filament\Resources\ReporteInventarioResource\Pages;

use App\Filament\Resources\ReporteInventarioResource;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\Category;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Support\Enums\Width;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;

class StockActualPage extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static string $resource = ReporteInventarioResource::class;
    
    protected string $view = 'filament.resources.reporte-inventario.pages.stock-actual';

    protected ?string $heading = 'Reporte de Stock Actual';

    protected ?string $subheading = 'Inventario actual de todos los productos';

    // Propiedades para las estadísticas de stock
    public int $stockDisponible = 0;
    public int $stockAgotado = 0;
    public int $stockCritico = 0;
    public int $stockMinimo = 0;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function mount(): void
    {
        // Calcular estadísticas de stock
        $products = Product::query()
            ->where('track_inventory', true)
            ->where('status', 'active')
            ->with(['stocks'])
            ->get();

        $this->stockDisponible = 0;
        $this->stockAgotado = 0;
        $this->stockCritico = 0;
        $this->stockMinimo = 0;

        foreach ($products as $product) {
            $stock = $product->stocks->first();
            if ($stock) {
                $actual = $stock->qty ?? 0;
                $minimo = $stock->min_qty ?? 0;

                if ($actual <= 0) {
                    $this->stockAgotado++;
                } elseif ($actual <= $minimo) {
                    $this->stockCritico++;
                } else {
                    $this->stockDisponible++;
                }

                if ($minimo > 0) {
                    $this->stockMinimo++;
                }
            } else {
                // Sin stock = agotado
                $this->stockAgotado++;
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('track_inventory', true)
                    ->where('status', 'active')
                    ->with(['stocks.warehouse', 'category'])
                    ->select([
                        'products.id',
                        'products.name',
                        'products.code',
                        'products.category_id'
                    ])
                    ->distinct()
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                    
                TextColumn::make('code')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('warehouse_name')
                    ->label('Almacén')
                    ->getStateUsing(function (Product $record) {
                        return $record->stocks->first()?->warehouse?->name ?? 'N/A';
                    })
                    ->sortable(),
                    
                TextColumn::make('stock_actual')
                    ->label('Stock Actual')
                    ->getStateUsing(function (Product $record) {
                        return $record->stocks->first()?->qty ?? 0;
                    })
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'success'),
                    
                TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->getStateUsing(function (Product $record) {
                        return $record->stocks->first()?->min_qty ?? 0;
                    })
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                    
                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->getStateUsing(function (Product $record) {
                        $stock = $record->stocks->first();
                        if (!$stock) return 'Sin Stock';
                        
                        $actual = $stock->qty ?? 0;
                        $minimo = $stock->min_qty ?? 0;
                        
                        if ($actual <= 0) return 'Agotado';
                        if ($actual <= $minimo) return 'Bajo';
                        return 'Normal';
                    })
                    ->colors([
                        'danger' => ['Agotado', 'Bajo'],
                        'warning' => 'Sin Stock',
                        'success' => 'Normal',
                    ])
                    ->icons([
                        'heroicon-o-x-circle' => 'Agotado',
                        'heroicon-o-exclamation-triangle' => 'Bajo',
                        'heroicon-o-question-mark-circle' => 'Sin Stock',
                        'heroicon-o-check-circle' => 'Normal',
                    ])
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Almacén')
                    ->options(Warehouse::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $warehouse): Builder => 
                                $query->whereHas('stocks', fn ($q) => $q->where('warehouse_id', $warehouse))
                        );
                    }),
                    
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->options(Category::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $category): Builder => $query->where('category_id', $category)
                        );
                    }),
                    
                Filter::make('stock_status')
                    ->label('Estado del Stock')
                    ->form([
                        Select::make('status')
                            ->options([
                                'normal' => 'Normal',
                                'bajo' => 'Stock Bajo',
                                'agotado' => 'Agotado',
                            ])
                            ->placeholder('Todos los estados')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['status'],
                            function (Builder $query, $status) {
                                return $query->whereHas('stocks', function ($q) use ($status) {
                                    switch ($status) {
                                        case 'agotado':
                                            return $q->where('qty', '<=', 0);
                                        case 'bajo':
                                            return $q->whereColumn('qty', '<=', 'min_qty')
                                                    ->where('qty', '>', 0);
                                        case 'normal':
                                            return $q->whereColumn('qty', '>', 'min_qty');
                                    }
                                });
                            }
                        );
                    })
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.view', $record))
                    ->openUrlInNewTab()
            ])
            ->bulkActions([
                BulkAction::make('export_selected')
                    ->label('Exportar Seleccionados')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        return $this->exportToCsv($records);
                    })
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Exportar Todo')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function () {
                        return $this->exportToCsv($this->getFilteredTableQuery()->get());
                    })
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function exportToCsv($records)
    {
        $filename = 'stock-actual-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            
            // Headers CSV
            fputcsv($file, [
                'Producto',
                'SKU',
                'Categoría',
                'Almacén',
                'Stock Actual',
                'Stock Mínimo',
                'Estado'
            ]);

            foreach ($records as $record) {
                $stock = $record->stocks->first();
                $actual = $stock?->qty ?? 0;
                $minimo = $stock?->min_qty ?? 0;
                
                $estado = 'Sin Stock';
                if ($stock) {
                    if ($actual <= 0) $estado = 'Agotado';
                    elseif ($actual <= $minimo) $estado = 'Bajo';
                    else $estado = 'Normal';
                }

                fputcsv($file, [
                    $record->name,
                    $record->code,
                    $record->category?->name ?? 'N/A',
                    $stock?->warehouse?->name ?? 'N/A',
                    $actual,
                    $minimo,
                    $estado
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}