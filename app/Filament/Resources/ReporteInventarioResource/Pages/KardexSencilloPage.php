<?php

namespace App\Filament\Resources\ReporteInventarioResource\Pages;

use App\Filament\Resources\ReporteInventarioResource;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\Warehouse;
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
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class KardexSencilloPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;
    
    protected static string $resource = ReporteInventarioResource::class;
    
    protected string $view = 'filament.resources.reporte-inventario.pages.kardex-sencillo';

    protected ?string $heading = 'Kardex Sencillo';

    protected ?string $subheading = 'Historial de movimientos de inventario por producto';

    public ?int $selectedProductId = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function mount(): void
    {
        // Obtener la fecha del movimiento más antiguo para establecer un rango adecuado
        $oldestMovement = InventoryMovement::orderBy('movement_date', 'asc')->first();
        
        if ($oldestMovement) {
            // Si hay movimientos antiguos, usar 3 meses hacia atrás desde hoy
            $this->dateFrom = now()->subMonths(3)->format('Y-m-d');
        } else {
            // Si no hay movimientos, usar 1 mes hacia atrás
            $this->dateFrom = now()->subMonth()->format('Y-m-d');
        }
        
        $this->dateTo = now()->format('Y-m-d');
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('selectedProductId')
                ->label('Seleccionar Producto')
                ->options(
                    Product::where('track_inventory', true)
                        ->where('status', 'active')
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->placeholder('Buscar producto...')
                ->required()
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
                
            DatePicker::make('dateFrom')
                ->label('Fecha Desde')
                ->default(now()->subMonth())
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
                
            DatePicker::make('dateTo')
                ->label('Fecha Hasta')
                ->default(now())
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
        ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InventoryMovement::query()
                    ->when($this->selectedProductId, function ($query) {
                        return $query->where('product_id', $this->selectedProductId);
                    })
                    ->when($this->dateFrom, function ($query) {
                        return $query->whereDate('movement_date', '>=', $this->dateFrom);
                    })
                    ->when($this->dateTo, function ($query) {
                        return $query->whereDate('movement_date', '<=', $this->dateTo);
                    })
                    ->with(['product', 'fromWarehouse', 'toWarehouse', 'user'])
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('movement_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->weight('medium'),
                    
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
                    ->wrap(),
                    
                TextColumn::make('reason')
                    ->label('Descripción/Motivo')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->wrap(),
                    
                TextColumn::make('ref_type')
                    ->label('Referencia')
                    ->getStateUsing(function (InventoryMovement $record) {
                        if (!$record->ref_type || !$record->ref_id) {
                            return 'Manual';
                        }
                        return $record->ref_type . ' #' . $record->ref_id;
                    })
                    ->toggleable(),
                    
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->toggleable()
                    ->default('Sistema'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo de Movimiento')
                    ->options(InventoryMovement::getTypes()),
                    
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
                    })
            ])
            ->actions([
                Action::make('view_details')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(function (InventoryMovement $record) {
                        return view('filament.resources.reporte-inventario.modals.movement-details', [
                            'movement' => $record
                        ]);
                    })
                    ->modalHeading('Detalles del Movimiento')
                    ->modalWidth('lg')
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
                    ->disabled(fn () => !$this->selectedProductId)
            ])
            ->emptyStateHeading('Selecciona un producto')
            ->emptyStateDescription('Para ver el kardex, primero selecciona un producto del formulario superior.')
            ->emptyStateIcon('heroicon-o-cube')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public function getSelectedProduct(): ?Product
    {
        return $this->selectedProductId ? Product::find($this->selectedProductId) : null;
    }

    public function getProductSummary(): array
    {
        if (!$this->selectedProductId) {
            return [];
        }

        $product = $this->getSelectedProduct();
        $movements = $this->getFilteredTableQuery()->get();
        
        $totalIn = $movements->whereIn('type', ['IN', 'OPENING'])->sum('qty');
        $totalOut = $movements->where('type', 'OUT')->sum('qty');
        $totalTransfers = $movements->where('type', 'TRANSFER')->count();
        $totalAdjustments = $movements->where('type', 'ADJUST')->count();

        return [
            'product' => $product,
            'totalMovements' => $movements->count(),
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'totalTransfers' => $totalTransfers,
            'totalAdjustments' => $totalAdjustments,
            'netMovement' => $totalIn - $totalOut,
        ];
    }

    protected function exportToCsv($records)
    {
        $product = $this->getSelectedProduct();
        $productName = $product ? str_replace(' ', '-', $product->name) : 'kardex';
        $filename = "kardex-{$productName}-" . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            
            // Headers CSV
            fputcsv($file, [
                'Fecha',
                'Tipo',
                'Cantidad',
                'Almacén Origen',
                'Almacén Destino',
                'Descripción',
                'Referencia',
                'Usuario'
            ]);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->movement_date->format('d/m/Y H:i'),
                    $record->getTypeLabel(),
                    $record->qty,
                    $record->fromWarehouse?->name ?? 'N/A',
                    $record->toWarehouse?->name ?? 'N/A',
                    $record->reason ?? 'N/A',
                    $record->ref_type ? $record->ref_type . ' #' . $record->ref_id : 'Manual',
                    $record->user?->name ?? 'Sistema'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}