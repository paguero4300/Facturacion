<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesChannelResource\Pages;
use App\Models\Invoice;
use App\Models\Company;
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
    protected static ?string $model = Invoice::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-stats-report';

    protected static ?string $navigationLabel = 'Reporte de Ventas';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes de Ventas';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'sales-channel-report';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('status', ['pending', 'accepted'])
            ->whereDate('issue_date', '>=', now()->startOfMonth())
            ->count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->whereIn('status', ['pending', 'accepted'])
                    ->with(['client', 'company'])
                    ->select([
                        'invoices.id',
                        'invoices.document_type',
                        'invoices.series',
                        'invoices.number',
                        'invoices.issue_date',
                        'invoices.client_id',
                        'invoices.company_id',
                        'invoices.payment_method',
                        'invoices.total_amount',
                        'invoices.currency_code',
                        'invoices.status',
                        'invoices.sunat_status'
                    ])
            )
            ->columns([
                TextColumn::make('document_type')
                    ->label('Tipo de Comprobante')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'factura' => 'success',
                        'boleta' => 'info',
                        'nota_credito' => 'warning',
                        'nota_debito' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('series_number')
                    ->label('Número')
                    ->getStateUsing(fn (Invoice $record): string => $record->series . '-' . $record->number)
                    ->searchable(['series', 'number'])
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),

                TextColumn::make('issue_date')
                    ->label('Fecha de Emisión')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('company.business_name')
                    ->label('Empresa')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('payment_method')
                    ->label('Método de Pago')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'efectivo' => 'success',
                        'tarjeta' => 'info',
                        'transferencia' => 'warning',
                        'credito' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('total_amount')
                    ->label('Monto Total')
                    ->money('PEN')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('currency_code')
                    ->label('Moneda')
                    ->sortable()
                    ->toggleable()
                    ->badge(),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'accepted',
                        'heroicon-o-x-circle' => 'rejected',
                    ]),

                BadgeColumn::make('sunat_status')
                    ->label('Estado SUNAT')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                        'gray' => 'not_sent',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'accepted',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-minus-circle' => 'not_sent',
                    ])
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('Tipo de Comprobante')
                    ->options([
                        'factura' => 'Factura',
                        'boleta' => 'Boleta',
                        'nota_credito' => 'Nota de Crédito',
                        'nota_debito' => 'Nota de Débito',
                    ])
                    ->multiple(),

                SelectFilter::make('payment_method')
                    ->label('Método de Pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'tarjeta' => 'Tarjeta',
                        'transferencia' => 'Transferencia',
                        'credito' => 'Crédito',
                    ])
                    ->multiple(),

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
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '<=', $date),
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
                        Select::make('range')
                            ->options([
                                '0-100' => 'S/ 0 - S/ 100',
                                '100-500' => 'S/ 100 - S/ 500',
                                '500-1000' => 'S/ 500 - S/ 1,000',
                                '1000-5000' => 'S/ 1,000 - S/ 5,000',
                                '5000+' => 'Más de S/ 5,000',
                            ])
                            ->placeholder('Todos los montos')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['range'],
                            function (Builder $query, $range) {
                                switch ($range) {
                                    case '0-100':
                                        return $query->whereBetween('total_amount', [0, 100]);
                                    case '100-500':
                                        return $query->whereBetween('total_amount', [100, 500]);
                                    case '500-1000':
                                        return $query->whereBetween('total_amount', [500, 1000]);
                                    case '1000-5000':
                                        return $query->whereBetween('total_amount', [1000, 5000]);
                                    case '5000+':
                                        return $query->where('total_amount', '>', 5000);
                                }
                            }
                        );
                    })
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Ver Comprobante')
                    ->url(fn (Invoice $record): string =>
                        route('filament.admin.resources.invoices.view', $record))
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
            ->defaultSort('issue_date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s') // Auto-refresh cada 30 segundos
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
                'Tipo de Comprobante',
                'Número',
                'Fecha de Emisión',
                'Cliente',
                'Empresa',
                'Método de Pago',
                'Monto Total',
                'Moneda',
                'Estado',
                'Estado SUNAT'
            ]);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->document_type,
                    $record->series . '-' . $record->number,
                    $record->issue_date?->format('d/m/Y'),
                    $record->client?->name ?? 'N/A',
                    $record->company?->name ?? 'N/A',
                    $record->payment_method,
                    $record->total_amount,
                    $record->currency_code,
                    $record->status,
                    $record->sunat_status
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}