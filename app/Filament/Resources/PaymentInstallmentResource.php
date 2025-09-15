<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentInstallmentResource\Pages;
use App\Models\PaymentInstallment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class PaymentInstallmentResource extends Resource
{
    protected static ?string $model = PaymentInstallment::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-credit-card';
    
    protected static string|UnitEnum|null $navigationGroup = 'Facturación';
    
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Cuotas de Pago');
    }

    public static function getModelLabel(): string
    {
        return __('Cuota de Pago');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Cuotas de Pago');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Información de la Cuota'))
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                    Select::make('invoice_id')
                        ->relationship('invoice', 'full_number')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->label(__('Factura'))
                        ->columnSpan(1),
                    
                    TextInput::make('installment_number')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->label(__('Número de Cuota'))
                        ->columnSpan(1),
                    
                    TextInput::make('amount')
                        ->numeric()
                        ->required()
                        ->step(0.01)
                        ->minValue(0.01)
                        ->prefix('S/')
                        ->label(__('Monto'))
                        ->columnSpan(1),
                    
                    DatePicker::make('due_date')
                        ->required()
                        ->label(__('Fecha de Vencimiento'))
                        ->columnSpan(1),
                ]),

                Section::make(__('Estado de Pago'))
                    ->icon('heroicon-o-credit-card')
                    ->columns(3)
                    ->schema([
                    
                    TextInput::make('paid_amount')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->default(0)
                        ->prefix('S/')
                        ->label(__('Monto Pagado'))
                        ->columnSpan(1),
                    
                    TextInput::make('pending_amount')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->default(0)
                        ->prefix('S/')
                        ->label(__('Monto Pendiente'))
                        ->columnSpan(1),
                    
                    Select::make('status')
                        ->options([
                            'pending' => __('Pendiente'),
                            'partial_paid' => __('Parcialmente Pagado'),
                            'paid' => __('Pagado'),
                            'overdue' => __('Vencido'),
                        ])
                        ->required()
                        ->native(false)
                        ->default('pending')
                        ->label(__('Estado'))
                        ->columnSpan(1),
                ]),

                Section::make(__('Información de Pago'))
                    ->icon('heroicon-o-banknotes')
                    ->columns(2)
                    ->schema([
                    
                    DateTimePicker::make('paid_at')
                        ->label(__('Fecha de Pago'))
                        ->seconds(false)
                        ->columnSpan(1),
                    
                    TextInput::make('payment_reference')
                        ->maxLength(255)
                        ->placeholder(__('Ej: Transferencia #123456'))
                        ->label(__('Referencia de Pago'))
                        ->columnSpan(1),
                ]),

                Section::make(__('Información de Mora'))
                    ->icon('heroicon-o-exclamation-triangle')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                    
                    TextInput::make('late_fee_rate')
                        ->numeric()
                        ->step(0.0001)
                        ->minValue(0)
                        ->suffix('%')
                        ->label(__('Tasa de Interés por Mora'))
                        ->columnSpan(1),
                    
                    TextInput::make('late_fee_amount')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->prefix('S/')
                        ->label(__('Monto de Interés por Mora'))
                        ->columnSpan(1),
                    
                    TextInput::make('days_overdue')
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->default(0)
                        ->suffix(__('días'))
                        ->label(__('Días de Mora'))
                        ->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'asc')
            ->columns([
                TextColumn::make('invoice.full_number')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->label(__('Factura')),
                    
                TextColumn::make('installment_number')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->label(__('Cuota')),
                    
                TextColumn::make('amount')
                    ->money(function ($record) {
                        return $record->invoice?->currency_code ?? 'PEN';
                    })
                    ->sortable()
                    ->label(__('Monto')),
                    
                TextColumn::make('due_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->status === 'overdue') return 'danger';
                        if ($record->due_date && $record->due_date->isPast() && $record->status !== 'paid') return 'warning';
                        return 'gray';
                    })
                    ->weight(function ($record) {
                        if ($record->status === 'overdue') return 'bold';
                        if ($record->due_date && $record->due_date->isPast() && $record->status !== 'paid') return 'medium';
                        return 'normal';
                    })
                    ->label(__('Vencimiento')),
                    
                TextColumn::make('paid_amount')
                    ->money(function ($record) {
                        return $record->invoice?->currency_code ?? 'PEN';
                    })
                    ->sortable()
                    ->label(__('Pagado')),
                    
                TextColumn::make('pending_amount')
                    ->money(function ($record) {
                        return $record->invoice?->currency_code ?? 'PEN';
                    })
                    ->sortable()
                    ->color(function ($record) {
                        $pending = (float) $record->pending_amount;
                        if ($pending <= 0) return 'success';
                        if ($pending > 0 && (float) $record->paid_amount > 0) return 'warning';
                        return 'danger';
                    })
                    ->weight(function ($record) {
                        return (float) $record->pending_amount > 0 ? 'bold' : 'normal';
                    })
                    ->label(__('Pendiente')),
                    
                TextColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('Pendiente'),
                        'partial_paid' => __('Parcialmente Pagado'),
                        'paid' => __('Pagado'),
                        'overdue' => __('Vencido'),
                        default => $state,
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        return match ($state) {
                            'pending' => 'warning',
                            'partial_paid' => 'info',
                            'paid' => 'success',
                            'overdue' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->sortable()
                    ->label(__('Estado')),
                    
                TextColumn::make('paid_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('No pagado'))
                    ->label(__('Fecha de Pago'))
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('days_overdue')
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        $days = (int) $record->days_overdue;
                        if ($days === 0) return 'success';
                        if ($days <= 30) return 'warning';
                        return 'danger';
                    })
                    ->formatStateUsing(function ($state) {
                        return $state ? $state . ' días' : '0 días';
                    })
                    ->label(__('Días de Mora'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => __('Pendiente'),
                        'partial_paid' => __('Parcialmente Pagado'),
                        'paid' => __('Pagado'),
                        'overdue' => __('Vencido'),
                    ])
                    ->label(__('Estado')),
                    
                SelectFilter::make('invoice_id')
                    ->relationship('invoice', 'full_number')
                    ->searchable()
                    ->preload()
                    ->label(__('Factura')),
                    
                TernaryFilter::make('is_overdue')
                    ->label(__('Vencidas'))
                    ->placeholder(__('Todas'))
                    ->trueLabel(__('Solo vencidas'))
                    ->falseLabel(__('Solo vigentes'))
                    ->queries(
                        true: function (Builder $query) {
                            return $query->where('status', 'overdue');
                        },
                        false: function (Builder $query) {
                            return $query->where('status', '!=', 'overdue');
                        },
                    ),
                    
                Filter::make('due_date')
                    ->form([
                        DatePicker::make('due_date_from')
                            ->label(__('Desde')),
                        DatePicker::make('due_date_to')
                            ->label(__('Hasta')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_date_from'],
                                fn (Builder $query, $date): Builder =>
                                    $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_date_to'],
                                fn (Builder $query, $date): Builder =>
                                    $query->whereDate('due_date', '<=', $date),
                            );
                    })
                    ->label(__('Fecha de Vencimiento')),
                    
                Filter::make('pending_amount')
                    ->form([
                        TextInput::make('pending_min')
                            ->numeric()
                            ->label(__('Monto mínimo pendiente')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['pending_min'],
                                fn (Builder $query, $amount): Builder =>
                                    $query->where('pending_amount', '>=', $amount),
                            );
                    })
                    ->label(__('Monto Pendiente')),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->label(__('Opciones')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('Eliminar seleccionados')),
                ])
                ->label(__('Acciones masivas')),
            ])
            ->emptyStateHeading(__('No hay cuotas de pago'))
            ->emptyStateDescription(__('Comience creando una nueva cuota de pago.'))
            ->emptyStateIcon('heroicon-o-currency-dollar');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentInstallments::route('/'),
            'create' => Pages\CreatePaymentInstallment::route('/create'),
            'edit' => Pages\EditPaymentInstallment::route('/{record}/edit'),
            'view' => Pages\ViewPaymentInstallment::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['invoice', 'invoice.company'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}