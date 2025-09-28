<?php

namespace App\Filament\Resources\ReportesInventarioResource\Pages;

use App\Filament\Resources\ReportesInventarioResource;
use App\Models\InventoryMovement;
use App\Models\Product;
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
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;

class KardexPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ReportesInventarioResource::class;

    public function getView(): string
    {
        return 'filament.resources.reportes-inventario-resource.pages.kardex-page';
    }

    public static function getNavigationLabel(): string
    {
        return '📋 Kardex';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public function getTitle(): string
    {
        return 'Kardex';
    }

    public function getHeading(): string
    {
        return 'Kardex de Inventario';
    }

    public function getSubheading(): ?string
    {
        return 'Historial detallado de movimientos de inventario';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InventoryMovement::query()
                    ->with(['product', 'fromWarehouse', 'toWarehouse', 'user'])
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('movement_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                TextColumn::make('product.code')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                BadgeColumn::make('type')
                    ->label('Tipo')
                    ->getStateUsing(fn (InventoryMovement $record) => $record->getTypeLabel())
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['Apertura', 'Entrada']),
                        'danger' => fn ($state) => $state === 'Salida',
                        'warning' => fn ($state) => $state === 'Transferencia',
                        'info' => fn ($state) => $state === 'Ajuste',
                    ])
                    ->icons([
                        'heroicon-o-plus-circle' => fn ($state) => in_array($state, ['Apertura', 'Entrada']),
                        'heroicon-o-minus-circle' => fn ($state) => $state === 'Salida',
                        'heroicon-o-arrow-right-circle' => fn ($state) => $state === 'Transferencia',
                        'heroicon-o-pencil-square' => fn ($state) => $state === 'Ajuste',
                    ]),

                TextColumn::make('qty')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->weight('bold')
                    ->color(function (InventoryMovement $record) {
                        return match($record->type) {
                            'IN', 'OPENING' => 'success',
                            'OUT' => 'danger',
                            'TRANSFER' => 'warning',
                            'ADJUST' => 'info',
                            default => 'gray'
                        };
                    })
                    ->formatStateUsing(function (InventoryMovement $record) {
                        $prefix = match($record->type) {
                            'IN', 'OPENING' => '+',
                            'OUT' => '-',
                            'TRANSFER' => '→',
                            'ADJUST' => '±',
                            default => ''
                        };
                        return $prefix . number_format($record->qty, 2);
                    }),

                TextColumn::make('warehouse_movement')
                    ->label('Movimiento de Almacén')
                    ->getStateUsing(fn (InventoryMovement $record) => $record->getWarehouseMovementDescription())
                    ->wrap()
                    ->icon('heroicon-o-building-storefront'),

                TextColumn::make('reason')
                    ->label('Descripción/Motivo')
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    })
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('reference')
                    ->label('Referencia')
                    ->getStateUsing(function (InventoryMovement $record) {
                        if (!$record->ref_type || !$record->ref_id) {
                            return 'Manual';
                        }
                        return $record->ref_type . ' #' . $record->ref_id;
                    })
                    ->toggleable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->toggleable()
                    ->default('Sistema')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Producto')
                    ->options(
                        Product::where('track_inventory', true)
                            ->where('status', 'active')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Tipo de Movimiento')
                    ->options(InventoryMovement::getTypes())
                    ->multiple(),

                SelectFilter::make('warehouse')
                    ->label('Almacén')
                    ->options(Warehouse::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $warehouse): Builder =>
                                $query->where(function ($q) use ($warehouse) {
                                    $q->where('from_warehouse_id', $warehouse)
                                      ->orWhere('to_warehouse_id', $warehouse);
                                })
                        );
                    }),

                Filter::make('amount_range')
                    ->label('Rango de Cantidad')
                    ->form([
                        Select::make('range')
                            ->options([
                                'small' => 'Pequeñas (< 10)',
                                'medium' => 'Medianas (10-100)',
                                'large' => 'Grandes (> 100)',
                            ])
                            ->placeholder('Todas las cantidades')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['range'],
                            function (Builder $query, $range) {
                                return match($range) {
                                    'small' => $query->where('qty', '<', 10),
                                    'medium' => $query->whereBetween('qty', [10, 100]),
                                    'large' => $query->where('qty', '>', 100),
                                    default => $query
                                };
                            }
                        );
                    }),

                Filter::make('date_range')
                    ->label('Rango de Fechas')
                    ->form([
                        DatePicker::make('from')
                            ->label('Desde')
                            ->default(now()->subMonths(3)),
                        DatePicker::make('until')
                            ->label('Hasta')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Detalles del Movimiento de Inventario')
                    ->modalWidth('lg')
                    ->modalContent(function (InventoryMovement $record) {
                        return view('filament.resources.kardex.modals.movement-details', [
                            'movement' => $record
                        ]);
                    }),

                Action::make('view_product')
                    ->label('Ver Producto')
                    ->icon('heroicon-o-cube')
                    ->color('success')
                    ->url(fn (InventoryMovement $record): ?string =>
                        $record->product_id ? route('filament.admin.resources.products.view', $record->product_id) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (InventoryMovement $record): bool => $record->product_id !== null),
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

                BulkAction::make('mark_as_reviewed')
                    ->label('Marcar como Revisado')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            $record->update([
                                'reviewed_at' => now(),
                                'reviewed_by' => auth()->id(),
                            ]);
                        });
                    }),
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

                Action::make('quick_filter_today')
                    ->label('Hoy')
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->action(function () {
                        session(['kardex_filter_date_from' => today()]);
                        session(['kardex_filter_date_until' => today()]);
                        return redirect()->request()->getUri();
                    }),

                Action::make('quick_filter_this_week')
                    ->label('Esta Semana')
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->action(function () {
                        session(['kardex_filter_date_from' => now()->startOfWeek()]);
                        session(['kardex_filter_date_until' => now()->endOfWeek()]);
                        return redirect()->request()->getUri();
                    }),

                Action::make('quick_filter_this_month')
                    ->label('Este Mes')
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->action(function () {
                        session(['kardex_filter_date_from' => now()->startOfMonth()]);
                        session(['kardex_filter_date_until' => now()->endOfMonth()]);
                        return redirect()->request()->getUri();
                    }),
            ])
            ->defaultSort('movement_date', 'desc')
            ->striped()
            ->paginated([25, 50, 100, 200])
            ->poll('120s')
            ->deferLoading()
            ->persistFiltersInSession()
            ->emptyStateHeading('No hay movimientos de inventario')
            ->emptyStateDescription('Los movimientos aparecerán aquí cuando se registren transacciones de inventario.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    protected function exportToCsv($records)
    {
        $filename = 'kardex-movimientos-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Fecha',
                'Producto',
                'SKU',
                'Tipo',
                'Cantidad',
                'Almacén Origen',
                'Almacén Destino',
                'Descripción',
                'Referencia',
                'Usuario',
                'Fecha Registro'
            ]);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->movement_date->format('d/m/Y H:i'),
                    $record->product?->name ?? 'N/A',
                    $record->product?->code ?? 'N/A',
                    $record->getTypeLabel(),
                    $record->qty,
                    $record->fromWarehouse?->name ?? 'N/A',
                    $record->toWarehouse?->name ?? 'N/A',
                    $record->reason ?? 'N/A',
                    $record->ref_type ? $record->ref_type . ' #' . $record->ref_id : 'Manual',
                    $record->user?->name ?? 'Sistema',
                    $record->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}