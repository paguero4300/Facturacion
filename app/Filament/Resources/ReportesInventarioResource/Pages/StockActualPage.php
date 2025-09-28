<?php

namespace App\Filament\Resources\ReportesInventarioResource\Pages;

use App\Filament\Resources\ReportesInventarioResource;
use App\Models\Product;
use App\Models\Category;
use App\Models\Warehouse;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;

class StockActualPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ReportesInventarioResource::class;

    public function getView(): string
    {
        return 'filament.resources.reportes-inventario-resource.pages.stock-actual-page';
    }

    public static function getNavigationLabel(): string
    {
        return '📦 Stock Actual';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-cube';
    }

    public function getTitle(): string
    {
        return 'Stock Actual';
    }

    public function getHeading(): string
    {
        return 'Reporte de Stock Actual';
    }

    public function getSubheading(): ?string
    {
        return 'Visualiza el inventario actual de todos los productos';
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
            ->actions([
                ViewAction::make()
                    ->label('Ver Producto')
                    ->url(fn (Product $record): string =>
                        route('filament.admin.resources.products.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkAction::make('export_csv')
                    ->label('Exportar CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        return $this->exportToCsv($records);
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Exportar Todo')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function () {
                        $query = $this->getFilteredTableQuery();
                        return $this->exportToCsv($query->get());
                    }),
            ])
            ->defaultSort('name')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->deferLoading()
            ->persistFiltersInSession();
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