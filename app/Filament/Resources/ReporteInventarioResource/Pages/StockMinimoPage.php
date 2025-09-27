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

class StockMinimoPage extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static string $resource = ReporteInventarioResource::class;
    
    protected string $view = 'filament.resources.reporte-inventario.pages.stock-minimo';

    protected ?string $heading = 'Reporte de Stock Mínimo';

    protected ?string $subheading = 'Productos con stock bajo o crítico que requieren reposición';

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
        // Calcular estadísticas de stock (igual que en StockActualPage)
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
                    ->whereHas('stocks', function ($query) {
                        $query->whereColumn('qty', '<=', 'min_qty')
                              ->whereNotNull('min_qty');
                    })
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
                    ->color('danger'),
                    
                TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->getStateUsing(function (Product $record) {
                        return $record->stocks->first()?->min_qty ?? 0;
                    })
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->getStateUsing(function (Product $record) {
                        $stock = $record->stocks->first();
                        if (!$stock) return 0;
                        
                        $actual = $stock->qty ?? 0;
                        $minimo = $stock->min_qty ?? 0;
                        
                        return $actual - $minimo;
                    })
                    ->numeric()
                    ->sortable()
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => $state < 0 ? $state : '0'),
                    
                TextColumn::make('requerido')
                    ->label('Cantidad Requerida')
                    ->getStateUsing(function (Product $record) {
                        $stock = $record->stocks->first();
                        if (!$stock) return 0;
                        
                        $actual = $stock->qty ?? 0;
                        $minimo = $stock->min_qty ?? 0;
                        
                        return max(0, $minimo - $actual);
                    })
                    ->numeric()
                    ->sortable()
                    ->color('warning')
                    ->weight('bold'),
                    
                BadgeColumn::make('prioridad')
                    ->label('Prioridad')
                    ->getStateUsing(function (Product $record) {
                        $stock = $record->stocks->first();
                        if (!$stock) return 'Sin Stock';
                        
                        $actual = $stock->qty ?? 0;
                        $minimo = $stock->min_qty ?? 0;
                        
                        if ($actual <= 0) return 'Crítica';
                        if ($actual <= ($minimo * 0.5)) return 'Alta';
                        return 'Media';
                    })
                    ->colors([
                        'danger' => 'Crítica',
                        'warning' => 'Alta',
                        'primary' => 'Media',
                        'gray' => 'Sin Stock',
                    ])
                    ->icons([
                        'heroicon-o-exclamation-circle' => 'Crítica',
                        'heroicon-o-exclamation-triangle' => 'Alta',
                        'heroicon-o-information-circle' => 'Media',
                        'heroicon-o-question-mark-circle' => 'Sin Stock',
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
                    
                Filter::make('priority')
                    ->label('Prioridad')
                    ->form([
                        Select::make('level')
                            ->options([
                                'critica' => 'Crítica',
                                'alta' => 'Alta',
                                'media' => 'Media',
                            ])
                            ->placeholder('Todas las prioridades')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['level'],
                            function (Builder $query, $level) {
                                return $query->whereHas('stocks', function ($q) use ($level) {
                                    switch ($level) {
                                        case 'critica':
                                            return $q->where('qty', '<=', 0);
                                        case 'alta':
                                            return $q->whereRaw('qty <= (min_qty * 0.5)')
                                                    ->where('qty', '>', 0);
                                        case 'media':
                                            return $q->whereRaw('qty > (min_qty * 0.5)')
                                                    ->whereColumn('qty', '<=', 'min_qty');
                                    }
                                });
                            }
                        );
                    })
            ])
            ->actions([
                Action::make('view_product')
                    ->label('Ver Producto')
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
            ->defaultSort('name', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    protected function exportToCsv($records)
    {
        $filename = 'stock-minimo-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
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
                'Diferencia',
                'Cantidad Requerida',
                'Prioridad'
            ]);

            foreach ($records as $record) {
                $stock = $record->stocks->first();
                $actual = $stock?->qty ?? 0;
                $minimo = $stock?->min_qty ?? 0;
                $diferencia = $actual - $minimo;
                $requerido = max(0, $minimo - $actual);
                
                $prioridad = 'Sin Stock';
                if ($stock) {
                    if ($actual <= 0) $prioridad = 'Crítica';
                    elseif ($actual <= ($minimo * 0.5)) $prioridad = 'Alta';
                    else $prioridad = 'Media';
                }

                fputcsv($file, [
                    $record->name,
                    $record->code,
                    $record->category?->name ?? 'N/A',
                    $stock?->warehouse?->name ?? 'N/A',
                    $actual,
                    $minimo,
                    $diferencia,
                    $requerido,
                    $prioridad
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}