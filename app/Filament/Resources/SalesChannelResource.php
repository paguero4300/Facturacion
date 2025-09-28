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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\ExportAction;
use Filament\Actions\BulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;
use UnitEnum;
use BackedEnum;
use Carbon\Carbon;

class SalesChannelResource extends Resource
{
    protected static ?string $model = InvoiceDetail::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-stats-report';

    protected static ?string $navigationLabel = 'Reporte de Ventas';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes de Ventas';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'sales-channel-report';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::join('invoices', 'invoice_details.invoice_id', '=', 'invoices.id')
            ->whereIn('invoices.status', ['pending', 'accepted'])
            ->whereDate('invoices.issue_date', '>=', now()->startOfMonth())
            ->count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                InvoiceDetail::query()
                    ->join('invoices', 'invoice_details.invoice_id', '=', 'invoices.id')
                    ->whereIn('invoices.status', ['pending', 'accepted'])
                    ->with([
                        'invoice.client',
                        'invoice.company',
                        'product.category',
                        'invoice.createdBy'
                    ])
                    ->select([
                        'invoice_details.id',
                        'invoice_details.invoice_id',
                        'invoice_details.product_id',
                        'invoice_details.description',
                        'invoice_details.quantity',
                        'invoice_details.unit_price',
                        'invoice_details.line_total',
                        'invoice_details.tax_type',
                        'invoice_details.igv_amount',
                        'invoice_details.net_amount',
                        'invoices.document_type',
                        'invoices.series',
                        'invoices.number',
                        'invoices.issue_date',
                        'invoices.client_id',
                        'invoices.company_id',
                        'invoices.payment_method',
                        'invoices.currency_code',
                        'invoices.status',
                        'invoices.sunat_status',
                        'invoices.created_by'
                    ])
                    ->orderBy('invoices.issue_date', 'desc')
            )
            ->columns([
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
                    ->getStateUsing(fn (InvoiceDetail $record): string => $record->invoice->series . '-' . $record->invoice->number)
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),

                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 25 ? $state : null;
                    }),

                TextColumn::make('product.code')
                    ->label('Código')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: '.',
                        thousandsSeparator: ','
                    )
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('unit_price')
                    ->label('P. Unitario')
                    ->money('PEN')
                    ->sortable(),

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
                    ->toggleable(),

                TextColumn::make('line_total')
                    ->label('Total Línea')
                    ->money('PEN')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('invoice.issue_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('invoice.client.business_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 25 ? $state : null;
                    })
                    ->default('Sin Cliente'),

                TextColumn::make('product.category.name')
                    ->label('Categoría')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('invoice.createdBy.name')
                    ->label('Vendedor')
                    ->sortable()
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
                    })
                    ->toggleable(),

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
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

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

                SelectFilter::make('document_type')
                    ->label('Tipo Comprobante')
                    ->options([
                        '01' => 'Factura',
                        '03' => 'Boleta',
                        '07' => 'Nota Crédito',
                        '08' => 'Nota Débito',
                        '09' => 'Nota de Venta',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $type): Builder =>
                                $query->whereHas('invoice', fn ($q) => $q->where('document_type', $type))
                        );
                    })
                    ->multiple(),

                SelectFilter::make('tax_type')
                    ->label('Tipo Fiscal')
                    ->options([
                        '10' => 'Gravado',
                        '20' => 'Exonerado',
                        '30' => 'Inafecto',
                    ])
                    ->multiple(),

                SelectFilter::make('created_by')
                    ->label('Vendedor')
                    ->relationship('invoice.createdBy', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_method')
                    ->label('Método Pago')
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
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $method): Builder =>
                                $query->whereHas('invoice', fn ($q) => $q->where('payment_method', $method))
                        );
                    })
                    ->multiple(),

                Filter::make('margen_rentabilidad')
                    ->label('Filtro de Rentabilidad')
                    ->form([
                        Select::make('nivel')
                            ->options([
                                'alto' => 'Alto (>30%)',
                                'medio' => 'Medio (15-30%)',
                                'bajo' => 'Bajo (<15%)',
                            ])
                            ->placeholder('Todos los márgenes')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['nivel'],
                            function (Builder $query, $nivel) {
                                return $query->whereHas('product', function ($q) use ($nivel) {
                                    switch ($nivel) {
                                        case 'alto':
                                            return $q->whereRaw('((unit_price - cost_price) / unit_price) > 0.30');
                                        case 'medio':
                                            return $q->whereRaw('((unit_price - cost_price) / unit_price) BETWEEN 0.15 AND 0.30');
                                        case 'bajo':
                                            return $q->whereRaw('((unit_price - cost_price) / unit_price) < 0.15');
                                    }
                                });
                            }
                        );
                    }),

                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->options(Company::pluck('business_name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $company): Builder => $query->where('company_id', $company)
                        );
                    }),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'accepted' => 'Aceptado',
                        'rejected' => 'Rechazado',
                    ])
                    ->multiple(),

                Filter::make('issue_date')
                    ->label('Rango de Fechas')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->placeholder('Fecha de inicio'),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->placeholder('Fecha de fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder =>
                                    $query->whereHas('invoice', fn ($q) => $q->whereDate('issue_date', '>=', $date)),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder =>
                                    $query->whereHas('invoice', fn ($q) => $q->whereDate('issue_date', '<=', $date)),
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
                    ->label('Rango de Montos por Línea')
                    ->form([
                        Select::make('range')
                            ->options([
                                '0-50' => 'S/ 0 - S/ 50',
                                '50-200' => 'S/ 50 - S/ 200',
                                '200-500' => 'S/ 200 - S/ 500',
                                '500-1000' => 'S/ 500 - S/ 1,000',
                                '1000+' => 'Más de S/ 1,000',
                            ])
                            ->placeholder('Todos los montos')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['range'],
                            function (Builder $query, $range) {
                                switch ($range) {
                                    case '0-50':
                                        return $query->whereBetween('line_total', [0, 50]);
                                    case '50-200':
                                        return $query->whereBetween('line_total', [50, 200]);
                                    case '200-500':
                                        return $query->whereBetween('line_total', [200, 500]);
                                    case '500-1000':
                                        return $query->whereBetween('line_total', [500, 1000]);
                                    case '1000+':
                                        return $query->where('line_total', '>', 1000);
                                }
                            }
                        );
                    })
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver Comprobante')
                    ->url(fn (InvoiceDetail $record): string =>
                        route('filament.admin.resources.invoices.view', $record->invoice))
                    ->openUrlInNewTab(),

                Action::make('view_product')
                    ->label('Ver Producto')
                    ->icon('heroicon-o-cube')
                    ->color('info')
                    ->url(fn (InvoiceDetail $record): ?string =>
                        $record->product_id ? route('filament.admin.resources.products.view', $record->product_id) : null)
                    ->openUrlInNewTab()
                    ->visible(fn (InvoiceDetail $record): bool => $record->product_id !== null),
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
                        $query = $livewire->getFilteredTableQuery();
                        return static::exportToCsv($query->get());
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s') // Auto-refresh cada minuto
            ->deferLoading()
            ->persistFiltersInSession();
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
        $filename = 'reporte-ventas-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');

            // Headers CSV
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
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}