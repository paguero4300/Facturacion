<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockActualResource\Pages;
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
use Filament\Actions\ExportAction;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;
use UnitEnum;
use BackedEnum;

class StockActualResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Stock Actual';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes de Inventario';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'stock-actual';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('track_inventory', true)
            ->where('status', 'active')
            ->count();
    }

    public static function table(Table $table): Table
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
                    ->weight('medium')
                    ->copyable(),

                TextColumn::make('code')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable(),

                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->toggleable()
                    ->badge(),

                TextColumn::make('warehouse_name')
                    ->label('Almacén')
                    ->getStateUsing(function (Product $record) {
                        return $record->stocks->first()?->warehouse?->name ?? 'N/A';
                    })
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('stock_actual')
                    ->label('Stock Actual')
                    ->getStateUsing(function (Product $record) {
                        return $record->stocks->first()?->qty ?? 0;
                    })
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'success')
                    ->weight('bold'),

                TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->getStateUsing(function (Product $record) {
                        return $record->stocks->first()?->min_qty ?? 0;
                    })
                    ->numeric(decimalPlaces: 2)
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
                        if ($actual <= $minimo && $minimo > 0) return 'Bajo';
                        return 'Normal';
                    })
                    ->colors([
                        'danger' => ['Agotado'],
                        'warning' => ['Bajo', 'Sin Stock'],
                        'success' => ['Normal'],
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
                                                    ->where('qty', '>', 0)
                                                    ->whereNotNull('min_qty');
                                        case 'normal':
                                            return $q->whereColumn('qty', '>', 'min_qty')
                                                    ->whereNotNull('min_qty');
                                    }
                                });
                            }
                        );
                    })
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver Producto')
                    ->url(fn (Product $record): string =>
                        route('filament.admin.resources.products.view', $record))
                    ->openUrlInNewTab(),
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
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s') // Auto-refresh cada 30 segundos
            ->deferLoading()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockActual::route('/'),
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
                    elseif ($actual <= $minimo && $minimo > 0) $estado = 'Bajo';
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