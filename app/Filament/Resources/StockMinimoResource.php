<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMinimoResource\Pages;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;
use UnitEnum;
use BackedEnum;

class StockMinimoResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Stock Mínimo';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes de Inventario';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'stock-minimo';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::lowStock()
            ->whereHas('product', function ($query) {
                $query->where('track_inventory', true)
                      ->where('status', 'active');
            })
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getNavigationBadge();
        if ($count > 10) return 'danger';
        if ($count > 5) return 'warning';
        return 'success';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Stock::query()
                    ->lowStock()
                    ->whereHas('product', function ($query) {
                        $query->where('track_inventory', true)
                              ->where('status', 'active');
                    })
                    ->with(['product.category', 'warehouse'])
                    ->orderBy('qty', 'asc') // Los más críticos primero
                    ->orderBy('warehouse_id')
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),

                TextColumn::make('product.code')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable(),

                TextColumn::make('product.category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->toggleable()
                    ->badge(),

                TextColumn::make('warehouse.name')
                    ->label('Almacén')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('qty')
                    ->label('Stock Actual')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color('danger')
                    ->weight('bold'),

                TextColumn::make('min_qty')
                    ->label('Stock Mínimo')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->getStateUsing(function (Stock $record) {
                        return $record->qty - $record->min_qty;
                    })
                    ->numeric(decimalPlaces: 2)
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => $state < 0 ? $state : '0')
                    ->icon('heroicon-o-arrow-down'),

                TextColumn::make('cantidad_requerida')
                    ->label('Cantidad Requerida')
                    ->getStateUsing(function (Stock $record) {
                        return max(0, $record->min_qty - $record->qty);
                    })
                    ->numeric(decimalPlaces: 2)
                    ->color('warning')
                    ->weight('bold')
                    ->icon('heroicon-o-shopping-cart'),

                BadgeColumn::make('prioridad')
                    ->label('Prioridad')
                    ->getStateUsing(function (Stock $record) {
                        if ($record->qty <= 0) return 'Crítica';
                        if ($record->qty <= ($record->min_qty * 0.5)) return 'Alta';
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
                            fn (Builder $query, $warehouse): Builder => $query->where('warehouse_id', $warehouse)
                        );
                    }),

                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->options(Category::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $category): Builder =>
                                $query->whereHas('product', fn ($q) => $q->where('category_id', $category))
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
                                switch ($level) {
                                    case 'critica':
                                        return $query->where('qty', '<=', 0);
                                    case 'alta':
                                        return $query->whereRaw('qty <= (min_qty * 0.5)')
                                                    ->where('qty', '>', 0);
                                    case 'media':
                                        return $query->whereRaw('qty > (min_qty * 0.5)')
                                                    ->whereColumn('qty', '<=', 'min_qty');
                                }
                            }
                        );
                    })
            ])
            ->recordActions([
                Action::make('view_product')
                    ->label('Ver Producto')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Stock $record): string =>
                        route('filament.admin.resources.products.view', $record->product))
                    ->openUrlInNewTab(),

                Action::make('create_purchase_order')
                    ->label('Crear Orden de Compra')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success')
                    ->visible(fn () => class_exists('App\Filament\Resources\PurchaseOrderResource'))
                    ->action(function (Stock $record) {
                        $required = max(0, $record->min_qty - $record->qty);

                        return redirect()->route('filament.admin.resources.purchase-orders.create', [
                            'product_id' => $record->product_id,
                            'warehouse_id' => $record->warehouse_id,
                            'quantity' => $required
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('export_csv')
                    ->label('Exportar CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        return static::exportToCsv($records);
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('create_bulk_purchase_order')
                    ->label('Crear Orden de Compra Masiva')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Crear Orden de Compra para Stocks Seleccionados')
                    ->modalDescription('Se creará una orden de compra con las cantidades requeridas para cada stock seleccionado.')
                    ->action(function (Collection $records) {
                        $purchaseOrderData = [];
                        foreach ($records as $record) {
                            $required = max(0, $record->min_qty - $record->qty);
                            if ($required > 0) {
                                $purchaseOrderData[] = [
                                    'product_id' => $record->product_id,
                                    'warehouse_id' => $record->warehouse_id,
                                    'quantity' => $required
                                ];
                            }
                        }

                        session(['bulk_purchase_order_data' => $purchaseOrderData]);
                        return redirect()->route('filament.admin.resources.purchase-orders.create', [
                            'bulk' => true
                        ]);
                    }),
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Exportar Todo')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($livewire) {
                        // Obtener los mismos datos que se muestran en la tabla con filtros aplicados
                        $query = $livewire->getFilteredTableQuery();
                        return static::exportToCsv($query->get());
                    }),
            ])
            ->defaultSort('qty', 'asc') // Los más críticos primero
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s') // Refresh cada minuto para stock crítico
            ->deferLoading()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMinimo::route('/'),
        ];
    }


    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    protected static function exportToCsv($records)
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
                $diferencia = $record->qty - $record->min_qty;
                $requerido = max(0, $record->min_qty - $record->qty);

                if ($record->qty <= 0) $prioridad = 'Crítica';
                elseif ($record->qty <= ($record->min_qty * 0.5)) $prioridad = 'Alta';
                else $prioridad = 'Media';

                fputcsv($file, [
                    $record->product?->name ?? 'N/A',
                    $record->product?->code ?? 'N/A',
                    $record->product?->category?->name ?? 'N/A',
                    $record->warehouse?->name ?? 'N/A',
                    $record->qty,
                    $record->min_qty,
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