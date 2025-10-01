<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebOrderResource\Pages;
use App\Models\Invoice;
use App\Enums\DeliveryStatus;
use App\Enums\DeliveryTimeSlot;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;
use Filament\Notifications\Notification;
use BackedEnum;
use UnitEnum;

class WebOrderResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static UnitEnum|string|null $navigationGroup = 'E-Commerce';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_number';

    public static function getNavigationLabel(): string
    {
        return 'Pedidos Web';
    }

    public static function getModelLabel(): string
    {
        return 'Pedido Web';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pedidos Web';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('series', 'NV02')
            ->where('status', 'draft')
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('series', 'NV02')
            ->orderBy('created_at', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información del Pedido')
                ->icon('heroicon-o-document-text')
                ->columns(3)
                ->schema([
                    TextInput::make('full_number')
                        ->label('Número de Pedido')
                        ->disabled(),

                    TextInput::make('issue_date')
                        ->label('Fecha')
                        ->disabled(),

                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'draft' => 'Pendiente',
                            'paid' => 'Completado',
                            'cancelled' => 'Cancelado',
                        ])
                        ->required(),
                ]),

            Section::make('Información del Cliente')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    TextInput::make('client_business_name')
                        ->label('Nombre')
                        ->disabled(),

                    TextInput::make('client_email')
                        ->label('Email')
                        ->disabled(),

                    Textarea::make('client_address')
                        ->label('Dirección')
                        ->disabled()
                        ->columnSpanFull(),
                ]),

            Section::make('Información de Pago')
                ->icon('heroicon-o-credit-card')
                ->columns(2)
                ->schema([
                    TextInput::make('payment_method')
                        ->label('Método de Pago')
                        ->formatStateUsing(function ($state) {
                            return match ($state) {
                                'cash' => 'Efectivo',
                                'yape' => 'Yape',
                                'plin' => 'Plin',
                                'card' => 'Tarjeta',
                                'transfer' => 'Transferencia',
                                default => $state,
                            };
                        })
                        ->disabled(),

                    TextInput::make('payment_reference')
                        ->label('Referencia de Pago')
                        ->disabled(),

                    TextInput::make('total_amount')
                        ->label('Total')
                        ->prefix('S/')
                        ->disabled()
                        ->numeric(),

                    Select::make('payment_condition')
                        ->label('Condición de Pago')
                        ->options([
                            'immediate' => 'Inmediato',
                            'credit' => 'Crédito',
                        ])
                        ->disabled(),
                ]),

            Section::make('Observaciones')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    Textarea::make('observations')
                        ->label('Observaciones del Cliente')
                        ->disabled()
                        ->rows(3),
                ]),

            Section::make('Productos del Pedido')
                ->icon('heroicon-o-shopping-bag')
                ->description('Productos incluidos en este pedido')
                ->schema([
                    Repeater::make('details')
                        ->relationship('details')
                        ->label('')
                        ->disabled()
                        ->columns(12)
                        ->schema([
                            TextInput::make('description')
                                ->label('Producto')
                                ->disabled()
                                ->columnSpan(5),

                            TextInput::make('product_code')
                                ->label('Código')
                                ->disabled()
                                ->columnSpan(2),

                            TextInput::make('quantity')
                                ->label('Cant.')
                                ->disabled()
                                ->columnSpan(1)
                                ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2)),

                            TextInput::make('unit_price')
                                ->label('P. Unit.')
                                ->disabled()
                                ->columnSpan(2)
                                ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state ?? 0, 2)),

                            TextInput::make('line_total')
                                ->label('Subtotal')
                                ->disabled()
                                ->columnSpan(2)
                                ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state ?? 0, 2)),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => $state['description'] ?? 'Producto'),
                ]),

            Section::make('Información de Entrega')
                ->icon('heroicon-o-truck')
                ->description('Programación y estado de la entrega')
                ->columns(2)
                ->schema([
                    DatePicker::make('delivery_date')
                        ->label('Fecha de Entrega')
                        ->disabled(fn ($record) => !$record?->hasDeliveryScheduled())
                        ->placeholder('No programada'),
                    
                    Select::make('delivery_time_slot')
                        ->label('Horario de Entrega')
                        ->options(DeliveryTimeSlot::getOptions())
                        ->disabled(fn ($record) => !$record?->hasDeliveryScheduled())
                        ->placeholder('No programado'),
                    
                    Select::make('delivery_status')
                        ->label('Estado de Entrega')
                        ->options(DeliveryStatus::getOptions())
                        ->visible(fn ($record) => $record?->hasDeliveryScheduled()),
                    
                    TimePicker::make('delivery_confirmed_at')
                        ->label('Entregado en')
                        ->disabled()
                        ->visible(fn ($record) => $record?->delivery_status === DeliveryStatus::ENTREGADO),
                    
                    Textarea::make('delivery_notes')
                        ->label('Notas de Entrega')
                        ->disabled(fn ($record) => !$record?->hasDeliveryScheduled())
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_number')
                    ->label('N° Pedido')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('primary'),

                TextColumn::make('issue_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('client_business_name')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('client_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment_method')
                    ->label('Método')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'cash' => 'Efectivo',
                            'yape' => 'Yape',
                            'plin' => 'Plin',
                            'card' => 'Tarjeta',
                            'transfer' => 'Transferencia',
                            default => $state,
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'yape', 'plin' => 'warning',
                        'card', 'transfer' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'draft' => 'Pendiente',
                            'paid' => 'Completado',
                            'cancelled' => 'Cancelado',
                            default => $state,
                        };
                    })
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ]),

                TextColumn::make('delivery_date')
                    ->label('Entrega')
                    ->date('d/m/Y')
                    ->placeholder('No programada')
                    ->toggleable(),

                BadgeColumn::make('delivery_status')
                    ->label('Estado Entrega')
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->colors([
                        'info' => DeliveryStatus::PROGRAMADO,
                        'warning' => DeliveryStatus::EN_RUTA,
                        'success' => DeliveryStatus::ENTREGADO,
                        'danger' => DeliveryStatus::REPROGRAMADO,
                    ])
                    ->placeholder('Sin programar')
                    ->toggleable(),

                TextColumn::make('createdBy.name')
                    ->label('Usuario Web')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Invitado'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Pendiente',
                        'paid' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ])
                    ->default('draft'),

                SelectFilter::make('payment_method')
                    ->label('Método de Pago')
                    ->options([
                        'cash' => 'Efectivo',
                        'yape' => 'Yape',
                        'plin' => 'Plin',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Desde'),
                        DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Filter::make('guest_orders')
                    ->label('Solo Invitados')
                    ->query(fn (Builder $query): Builder => $query->whereNull('created_by'))
                    ->toggle(),

                SelectFilter::make('delivery_status')
                    ->label('Estado de Entrega')
                    ->options(DeliveryStatus::getOptions())
                    ->placeholder('Todos los estados'),

                SelectFilter::make('delivery_time_slot')
                    ->label('Horario de Entrega')
                    ->options(DeliveryTimeSlot::getOptions())
                    ->placeholder('Todos los horarios'),

                Filter::make('delivery_date')
                    ->form([
                        DatePicker::make('delivery_from')
                            ->label('Entrega desde'),
                        DatePicker::make('delivery_until')
                            ->label('Entrega hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['delivery_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '>=', $date),
                            )
                            ->when(
                                $data['delivery_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '<=', $date),
                            );
                    }),

                Filter::make('with_delivery')
                    ->label('Solo con entrega programada')
                    ->query(fn (Builder $query): Builder => $query->withDeliveryScheduled())
                    ->toggle(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('complete')
                        ->label('Completar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Invoice $record) {
                            $record->update(['status' => 'paid']);
                            Notification::make()
                                ->success()
                                ->title('Pedido completado')
                                ->body("El pedido {$record->full_number} ha sido marcado como completado.")
                                ->send();
                        })
                        ->visible(fn (Invoice $record): bool => $record->status === 'draft'),

                    Action::make('cancel')
                        ->label('Cancelar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cancelar Pedido')
                        ->modalDescription('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')
                        ->action(function (Invoice $record) {
                            $record->update(['status' => 'cancelled']);
                            Notification::make()
                                ->warning()
                                ->title('Pedido cancelado')
                                ->body("El pedido {$record->full_number} ha sido cancelado.")
                                ->send();
                        })
                        ->visible(fn (Invoice $record): bool => $record->status === 'draft'),

                    Action::make('mark_in_route')
                        ->label('Marcar en Ruta')
                        ->icon('heroicon-o-truck')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Invoice $record) {
                            $record->updateDeliveryStatus(DeliveryStatus::EN_RUTA);
                            Notification::make()
                                ->success()
                                ->title('Estado actualizado')
                                ->body("El pedido {$record->full_number} está ahora en ruta.")
                                ->send();
                        })
                        ->visible(fn (Invoice $record): bool => 
                            $record->delivery_status === DeliveryStatus::PROGRAMADO
                        ),

                    Action::make('mark_delivered')
                        ->label('Marcar Entregado')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Invoice $record) {
                            $record->updateDeliveryStatus(DeliveryStatus::ENTREGADO);
                            Notification::make()
                                ->success()
                                ->title('Entrega confirmada')
                                ->body("El pedido {$record->full_number} ha sido entregado.")
                                ->send();
                        })
                        ->visible(fn (Invoice $record): bool => 
                            $record->delivery_status === DeliveryStatus::EN_RUTA
                        ),
                ])->label(__('Opciones')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('complete_selected')
                        ->label('Completar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['status' => 'paid']);
                            Notification::make()
                                ->success()
                                ->title('Pedidos completados')
                                ->body("Se han completado {$records->count()} pedidos.")
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListWebOrders::route('/'),
            'view' => Pages\ViewWebOrder::route('/{record}'),
            'edit' => Pages\EditWebOrder::route('/{record}/edit'),
        ];
    }
}
