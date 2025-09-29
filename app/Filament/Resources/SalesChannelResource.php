<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesChannelResource\Pages;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use App\Models\Category;
use App\Models\Company;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Actions\ExportAction;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use UnitEnum;
use BackedEnum;
use Carbon\Carbon;

class SalesChannelResource extends Resource
{
    protected static ?string $model = InvoiceDetail::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-stats-report';

    protected static ?string $navigationLabel = 'Reporte de Ventas';

    protected static string|UnitEnum|null $navigationGroup = 'Facturación';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'sales-channel-report';

    public static function getNavigationBadge(): ?string
    {
        try {
            $cacheKey = 'sales_channel_badge_' . now()->format('Y-m');

            return Cache::remember($cacheKey, now()->addMinutes(5), function () {
                $count = static::getModel()::join('invoices', 'invoice_details.invoice_id', '=', 'invoices.id')
                    ->whereIn('invoices.status', ['pending', 'accepted', 'paid', 'issued'])
                    ->whereDate('invoices.issue_date', '>=', now()->startOfMonth())
                    ->count();

                Log::info("SalesChannelResource: Navigation badge calculated", [
                    'count' => $count,
                    'month' => now()->format('Y-m')
                ]);

                return $count > 0 ? number_format($count) : null;
            });
        } catch (\Exception $e) {
            Log::error("SalesChannelResource: Error calculating navigation badge", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Logging para debugging
                Log::info("SalesChannelResource: Building query", [
                    'memory_usage' => memory_get_usage(true),
                    'timestamp' => now()->toISOString()
                ]);

                return $query
                    ->whereHas('invoice', function (Builder $q) {
                        $q->whereIn('status', ['pending', 'accepted', 'paid', 'issued'])
                          ->when(
                              request()->filled(['desde', 'hasta']),
                              fn (Builder $dateQuery) =>
                                  $dateQuery->whereBetween('issue_date', [
                                      request('desde'),
                                      request('hasta')
                                  ])
                          );
                    })
                    ->with([
                        'invoice' => function ($query) {
                            $query->select(['id', 'series', 'number', 'issue_date', 'document_type',
                                         'payment_method', 'status', 'sunat_status', 'client_id', 'company_id', 'created_by'])
                                  ->with([
                                      'client:id,business_name',
                                      'company:id,name',
                                      'createdBy:id,name'
                                  ]);
                        },
                        'product' => function ($query) {
                            $query->select(['id', 'name', 'code', 'cost_price', 'category_id'])
                                  ->with(['category:id,name']);
                        }
                    ])
                    ->select(['id', 'invoice_id', 'product_id', 'quantity', 'unit_price',
                             'line_total', 'igv_amount', 'tax_type', 'description'])
                    ->when(
                        request()->filled('product_id'),
                        fn (Builder $q, $productId) =>
                            $q->where('product_id', $productId)
                    )
                    ->when(
                        request()->filled('category_id'),
                        fn (Builder $q, $categoryId) =>
                            $q->whereHas('product', fn ($productQuery) =>
                                $productQuery->where('category_id', $categoryId)
                            )
                    )
                    ->when(
                        request()->filled('document_type'),
                        fn (Builder $q, $docType) =>
                            $q->whereHas('invoice', fn ($invoiceQuery) =>
                                $invoiceQuery->where('document_type', $docType)
                            )
                    )
                    ->when(
                        request()->filled('payment_method'),
                        fn (Builder $q, $paymentMethod) =>
                            $q->whereHas('invoice', fn ($invoiceQuery) =>
                                $invoiceQuery->where('payment_method', $paymentMethod)
                            )
                    )
                    ->when(
                        request()->filled('status'),
                        fn (Builder $q, $status) =>
                            $q->whereHas('invoice', fn ($invoiceQuery) =>
                                $invoiceQuery->where('status', $status)
                            )
                    );
            })
            ->columns([
                TextColumn::make('invoice.issue_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->summarize(Sum::make()->label('Total Registros')),

                TextColumn::make('invoice.document_type')
                    ->label('Tipo Comprobante')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '01' => 'success', // Factura
                        '03' => 'info',    // Boleta
                        '07' => 'warning', // Nota Crédito
                        '08' => 'danger',  // Nota Débito
                        '09' => 'primary', // Nota de Venta
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '01' => 'Factura',
                        '03' => 'Boleta',
                        '07' => 'Nota Crédito',
                        '08' => 'Nota Débito',
                        '09' => 'Nota de Venta',
                        default => $state,
                    }),

                TextColumn::make('invoice.series_number')
                    ->label('Número')
                    ->getStateUsing(fn (InvoiceDetail $record): string =>
                        $record->invoice->series . '-' . $record->invoice->number)
                    ->searchable(['invoices.series', 'invoices.number'])
                    ->sortable(['invoices.series', 'invoices.number'])
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Número copiado al portapapeles')
                    ->copyMessageDuration(1500),

                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->placeholder('Sin producto'),

                TextColumn::make('product.code')
                    ->label('Código')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->placeholder('N/A'),

                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ','
                    )
                    ->sortable()
                    ->weight('bold')
                    ->summarize(Sum::make()->label('Total Cantidad')->numeric(2)),

                TextColumn::make('unit_price')
                    ->label('P. Unitario')
                    ->money('PEN')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Precio')),

                TextColumn::make('rentabilidad')
                    ->label('Margen %')
                    ->getStateUsing(function (InvoiceDetail $record) {
                        $costPrice = $record->product?->cost_price ?? null;
                        $salePrice = $record->unit_price;

                        // Casos específicos más informativos
                        if ($costPrice === null || $costPrice === 0) {
                            if ($salePrice > 0) {
                                return $costPrice === null ? 'Sin Costo' : '100%';
                            }
                            return 'Gratis';
                        }

                        if ($salePrice <= 0) {
                            return 'Gratis';
                        }

                        // Calcular margen normal
                        $margin = (($salePrice - $costPrice) / $salePrice) * 100;
                        return number_format($margin, 1) . '%';
                    })
                    ->color(function (InvoiceDetail $record) {
                        $costPrice = $record->product?->cost_price ?? null;
                        $salePrice = $record->unit_price;

                        if ($costPrice === null) return 'gray';  // Sin costo configurado
                        if ($costPrice === 0 && $salePrice > 0) return 'success'; // 100% margen
                        if ($salePrice <= 0) return 'info'; // Gratis

                        $margin = (($salePrice - $costPrice) / $salePrice) * 100;
                        if ($margin >= 30) return 'success';
                        if ($margin >= 15) return 'warning';
                        if ($margin >= 0) return 'primary';
                        return 'danger'; // Pérdida
                    })
                    ->weight('bold')
                    ->toggleable(),

                TextColumn::make('tipo_operacion_fiscal')
                    ->label('Tipo Fiscal')
                    ->getStateUsing(function (InvoiceDetail $record) {
                        return match($record->tax_type) {
                            '10' => 'Gravado',
                            '20' => 'Exonerado',
                            '30' => 'Inafecto',
                            default => 'Otro'
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Gravado' => 'success',
                        'Exonerado' => 'warning',
                        'Inafecto' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('igv_amount')
                    ->label('IGV')
                    ->money('PEN')
                    ->sortable()
                    ->toggleable()
                    ->summarize(Sum::make()->label('Total IGV')),

                TextColumn::make('line_total')
                    ->label('Total Línea')
                    ->money('PEN')
                    ->sortable()
                    ->weight('bold')
                    ->color('success')
                    ->summarize(Sum::make()->label('Total General')),

                TextColumn::make('invoice.client.business_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable('invoices.client_id')
                    ->limit(25)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 25 ? $state : null;
                    })
                    ->default('Sin Cliente'),

                TextColumn::make('product.category.name')
                    ->label('Categoría')
                    ->sortable('categories.name')
                    ->toggleable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('invoice.createdBy.name')
                    ->label('Vendedor')
                    ->sortable('users.name')
                    ->toggleable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('invoice.payment_method')
                    ->label('Método Pago')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'card' => 'info',
                        'transfer' => 'warning',
                        'credit' => 'danger',
                        'check' => 'primary',
                        'deposit' => 'secondary',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                        'credit' => 'Crédito',
                        'check' => 'Cheque',
                        'deposit' => 'Depósito',
                        'other' => 'Otro',
                        default => $state,
                    }),

                BadgeColumn::make('invoice.status')
                    ->label('Estado')
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'issued',
                        'warning' => 'sent',
                        'success' => 'paid',
                        'primary' => 'partial_paid',
                        'danger' => 'overdue',
                        'secondary' => 'cancelled',
                        'dark' => 'voided',
                    ])
                    ->icons([
                        'heroicon-o-document-text' => 'draft',
                        'heroicon-o-paper-airplane' => 'issued',
                        'heroicon-o-clock' => 'sent',
                        'heroicon-o-check-circle' => 'paid',
                        'heroicon-o-currency-dollar' => 'partial_paid',
                        'heroicon-o-exclamation-triangle' => 'overdue',
                        'heroicon-o-x-circle' => 'cancelled',
                        'heroicon-o-trash' => 'voided',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Borrador',
                        'issued' => 'Emitido',
                        'sent' => 'Enviado',
                        'paid' => 'Pagado',
                        'partial_paid' => 'Pago Parcial',
                        'overdue' => 'Vencido',
                        'cancelled' => 'Anulado',
                        'voided' => 'Dado de Baja',
                        default => $state,
                    }),

                BadgeColumn::make('invoice.sunat_status')
                    ->label('SUNAT')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'sent',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                        'primary' => 'observed',
                        'secondary' => 'cancelled',
                        'dark' => 'voided',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-paper-airplane' => 'sent',
                        'heroicon-o-check-circle' => 'accepted',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-eye' => 'observed',
                        'heroicon-o-ban' => 'cancelled',
                        'heroicon-o-trash' => 'voided',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'sent' => 'Enviado',
                        'accepted' => 'Aceptado',
                        'rejected' => 'Rechazado',
                        'observed' => 'Observado',
                        'cancelled' => 'Anulado',
                        'voided' => 'Dado de Baja',
                        default => $state,
                    })
                    
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable(['name', 'code'])
                    ->preload()
                    ->multiple()
                    ->optionsLimit(50)
                     ->query(function (Builder $query, array $data): Builder {
                         $value = $data['value'] ?? null;

                         return $query->when(
                             filled($value),
                             fn (Builder $query) =>
                                 $query->where('product_id', $value)
                         );
                     }),

                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->options(Category::query()
                        ->whereHas('products.invoiceDetails.invoice', function ($query) {
                            $query->whereIn('status', ['pending', 'accepted', 'paid', 'issued']);
                        })
                        ->pluck('name', 'id')
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return $query->when(
                            filled($value),
                            fn (Builder $query) =>
                                $query->whereHas('product', fn ($q) => $q->where('category_id', $value))
                        );
                    })
                    ->multiple(),

                SelectFilter::make('document_type')
                    ->label('Tipo Comprobante')
                    ->options([
                        '01' => 'Factura',
                        '03' => 'Boleta',
                        '07' => 'Nota Crédito',
                        '08' => 'Nota Débito',
                        '09' => 'Nota de Venta',
                    ])
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        $values = array_filter(
                            Arr::wrap($data['values'] ?? $data['value'] ?? []),
                            fn ($value) => ! blank($value)
                        );

                        return $query->when(
                            ! empty($values),
                            fn (Builder $query) => $query->whereHas('invoice', fn (Builder $q) =>
                                $q->whereIn('document_type', $values)
                            )
                        );
                    }),

                SelectFilter::make('payment_method')
                    ->label('Método de Pago')
                    ->options([
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                        'credit' => 'Crédito',
                        'check' => 'Cheque',
                        'deposit' => 'Depósito',
                        'other' => 'Otro',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return $query->when(
                            filled($value),
                            fn (Builder $query) =>
                                $query->whereHas('invoice', fn ($q) => $q->where('payment_method', $value))
                        );
                    })
                    ->multiple(),

                SelectFilter::make('status')
                    ->label('Estado Factura')
                    ->options([
                        'draft' => 'Borrador',
                        'issued' => 'Emitido',
                        'sent' => 'Enviado',
                        'paid' => 'Pagado',
                        'partial_paid' => 'Pago Parcial',
                        'overdue' => 'Vencido',
                        'cancelled' => 'Anulado',
                        'voided' => 'Dado de Baja',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return $query->when(
                            filled($value),
                            fn (Builder $query) =>
                                $query->whereHas('invoice', fn ($q) => $q->where('status', $value))
                        );
                    })
                    ->multiple(),

                SelectFilter::make('sunat_status')
                    ->label('Estado SUNAT')
                    ->options([
                        'pending' => 'Pendiente',
                        'sent' => 'Enviado',
                        'accepted' => 'Aceptado',
                        'rejected' => 'Rechazado',
                        'observed' => 'Observado',
                        'cancelled' => 'Anulado',
                        'voided' => 'Dado de Baja',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return $query->when(
                            filled($value),
                            fn (Builder $query) =>
                                $query->whereHas('invoice', fn ($q) => $q->where('sunat_status', $value))
                        );
                    })
                    ->multiple(),

                Filter::make('issue_date')
                    ->label('Rango de Fechas')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->placeholder('Fecha de inicio')
                            ->default(now()->startOfMonth())
                            ->columnSpan(1),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->placeholder('Fecha de fin')
                            ->default(now()->endOfMonth())
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                ($data['desde'] ?? null),
                                fn (Builder $query, $date): Builder =>
                                    $query->whereHas('invoice', fn (Builder $q) =>
                                        $q->whereDate('issue_date', '>=', $date)
                                    ),
                            )
                            ->when(
                                ($data['hasta'] ?? null),
                                fn (Builder $query, $date): Builder =>
                                    $query->whereHas('invoice', fn (Builder $q) =>
                                        $q->whereDate('issue_date', '<=', $date)
                                    ),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['desde'] ?? null) {
                            $indicators['desde'] = 'Desde: ' . Carbon::parse($data['desde'])->format('d/m/Y');
                        }
                        if ($data['hasta'] ?? null) {
                            $indicators['hasta'] = 'Hasta: ' . Carbon::parse($data['hasta'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),

                Filter::make('amount_range')
                    ->label('Rango de Montos')
                    ->form([
                        TextInput::make('monto_minimo')
                            ->label('Monto Mínimo')
                            ->numeric()
                            ->prefix('S/')
                            ->placeholder('0.00'),
                        TextInput::make('monto_maximo')
                            ->label('Monto Máximo')
                            ->numeric()
                            ->prefix('S/')
                            ->placeholder('999999.99'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                ($data['monto_minimo'] ?? null),
                                fn (Builder $query, $amount): Builder =>
                                    $query->where('line_total', '>=', $amount)
                            )
                            ->when(
                                ($data['monto_maximo'] ?? null),
                                fn (Builder $query, $amount): Builder =>
                                    $query->where('line_total', '<=', $amount)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['monto_minimo'] ?? null) {
                            $indicators['monto_minimo'] = 'Min: S/' . number_format($data['monto_minimo'], 2);
                        }
                        if ($data['monto_maximo'] ?? null) {
                            $indicators['monto_maximo'] = 'Max: S/' . number_format($data['monto_maximo'], 2);
                        }
                        return $indicators;
                    }),

                TernaryFilter::make('con_margen')
                    ->label('Con Márgen Calculable')
                    ->placeholder('Todos los productos')
                    ->trueLabel('Solo productos con costo configurado')
                    ->falseLabel('Solo productos sin costo')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('product', fn ($q) => $q->whereNotNull('cost_price')->where('cost_price', '>', 0)),
                        false: fn (Builder $query) => $query->whereHas('product', fn ($q) => $q->where(function ($subQ) {
                            $subQ->whereNull('cost_price')->orWhere('cost_price', '=', 0);
                        })),
                        blank: fn (Builder $query) => $query
                    )
                    

            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Ver Comprobante')
                        ->url(fn (InvoiceDetail $record): string =>
                            route('filament.admin.resources.invoices.view', $record->invoice))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-eye'),

                    Action::make('view_product')
                        ->label('Ver Producto')
                        ->icon('heroicon-o-cube')
                        ->color('info')
                        ->url(fn (InvoiceDetail $record): ?string =>
                            $record->product_id ? route('filament.admin.resources.products.view', $record->product_id) : null)
                        ->openUrlInNewTab()
                        ->visible(fn (InvoiceDetail $record): bool => $record->product_id !== null),

                    Action::make('duplicate')
                        ->label('Duplicar Registro')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function (InvoiceDetail $record) {
                            Log::info("SalesChannelResource: Duplicating record", [
                                'original_id' => $record->id,
                                'user_id' => auth()->id()
                            ]);
                            // Implementar lógica de duplicación si es necesaria
                        })
                        ->visible(false), // Oculto por defecto hasta implementar lógica
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkAction::make('export_csv')
                    ->label('Exportar CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        Log::info("SalesChannelResource: Exporting CSV", [
                            'record_count' => $records->count(),
                            'user_id' => auth()->id()
                        ]);
                        return static::exportToCsv($records);
                    })
                    ->deselectRecordsAfterCompletion(),

                Action::make('clear_filters')
                    ->label('Limpiar Filtros')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->action(function () {
                        // Los filtros se limpiarán automáticamente
                        Log::info("SalesChannelResource: Filters cleared by user", [
                            'user_id' => auth()->id()
                        ]);
                    }),
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Exportar Todo')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $count = $query->count();

                        Log::info("SalesChannelResource: Exporting all records", [
                            'record_count' => $count,
                            'user_id' => auth()->id()
                        ]);

                        if ($count > 5000) {
                            Log::warning("SalesChannelResource: Large export attempted", [
                                'record_count' => $count,
                                'user_id' => auth()->id()
                            ]);
                        }

                        return static::exportToCsv($query->get());
                    }),

                Action::make('performance_stats')
                    ->label('Estadísticas')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->action(function () {
                        $stats = Cache::remember('sales_channel_performance_stats', now()->addMinutes(10), function () {
                            return [
                                'total_records' => static::getModel()::count(),
                                'filtered_records' => static::getModel()::whereHas('invoice', fn ($q) =>
                                    $q->whereIn('status', ['pending', 'accepted', 'paid', 'issued'])
                                )->count(),
                                'last_updated' => now()->toISOString()
                            ];
                        });

                        Log::info("SalesChannelResource: Performance stats viewed", [
                            'stats' => $stats,
                            'user_id' => auth()->id()
                        ]);

                        // Mostrar notificación con estadísticas
                        return redirect()->back();
                    })
                    ->visible(fn () => auth()->check()),
            ])
            ->defaultSort('invoice.issue_date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100, 250])
            ->poll(120) // Auto-refresh cada 2 minutos
            ->searchable()
            ->searchPlaceholder('Buscar productos, clientes, números de comprobante...')
            ->emptyStateHeading('No se encontraron registros')
            ->emptyStateDescription('No hay detalles de facturas que coincidan con los filtros aplicados.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesChannel::route('/'),
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
        $startTime = microtime(true);
        $filename = 'reporte-ventas-' . now()->format('Y-m-d-H-i-s') . '.csv';

        Log::info("SalesChannelResource: Starting CSV export", [
            'record_count' => $records->count(),
            'filename' => $filename,
            'user_id' => auth()->id()
        ]);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');

            // Headers CSV con BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'Tipo Comprobante',
                'Número',
                'Fecha',
                'Producto',
                'Código Producto',
                'Cantidad',
                'Precio Unitario',
                'Total Línea',
                'Tipo Fiscal',
                'IGV',
                'Margen %',
                'Cliente',
                'Categoría',
                'Vendedor',
                'Método Pago',
                'Estado',
                'Estado SUNAT'
            ]);

            $processed = 0;
            foreach ($records as $record) {
                $costPrice = $record->product?->cost_price ?? 0;
                $salePrice = $record->unit_price;
                $margin = 'N/A';
                if ($costPrice > 0 && $salePrice > 0) {
                    $margin = number_format((($salePrice - $costPrice) / $salePrice) * 100, 1) . '%';
                }

                $tipoFiscal = match($record->tax_type) {
                    '10' => 'Gravado',
                    '20' => 'Exonerado',
                    '30' => 'Inafecto',
                    default => 'Otro'
                };

                $documentType = match($record->invoice?->document_type) {
                    '01' => 'Factura',
                    '03' => 'Boleta',
                    '07' => 'Nota Crédito',
                    '08' => 'Nota Débito',
                    '09' => 'Nota de Venta',
                    default => $record->invoice?->document_type ?? 'N/A'
                };

                $paymentMethod = match($record->invoice?->payment_method) {
                    'cash' => 'Efectivo',
                    'card' => 'Tarjeta',
                    'transfer' => 'Transferencia',
                    'credit' => 'Crédito',
                    'check' => 'Cheque',
                    'deposit' => 'Depósito',
                    'other' => 'Otro',
                    default => $record->invoice?->payment_method ?? 'N/A'
                };

                fputcsv($file, [
                    $documentType,
                    ($record->invoice?->series ?? '') . '-' . ($record->invoice?->number ?? ''),
                    $record->invoice?->issue_date?->format('d/m/Y') ?? 'N/A',
                    $record->product?->name ?? $record->description ?? 'N/A',
                    $record->product?->code ?? 'N/A',
                    $record->quantity,
                    $record->unit_price,
                    $record->line_total,
                    $tipoFiscal,
                    $record->igv_amount,
                    $margin,
                    $record->invoice?->client?->business_name ?? 'N/A',
                    $record->product?->category?->name ?? 'N/A',
                    $record->invoice?->createdBy?->name ?? 'N/A',
                    $paymentMethod,
                    $record->invoice?->status ?? 'N/A',
                    $record->invoice?->sunat_status ?? 'N/A'
                ]);

                $processed++;
                if ($processed % 1000 === 0) {
                    Log::info("SalesChannelResource: Processed $processed records in CSV export");
                }
            }

            fclose($file);
        };

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        Log::info("SalesChannelResource: CSV export completed", [
            'duration_seconds' => $duration,
            'record_count' => $records->count(),
            'filename' => $filename,
            'user_id' => auth()->id()
        ]);

        return Response::stream($callback, 200, $headers);
    }
}
