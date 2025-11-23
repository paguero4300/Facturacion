<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebOrderResource\Pages;
use App\Models\Invoice;
use App\Enums\DeliveryStatus;
use App\Enums\DeliveryTimeSlot;
use App\Enums\PaymentValidationStatus;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use BackedEnum;
use UnitEnum;

class WebOrderResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pedidos Web';
    protected static UnitEnum|string|null $navigationGroup = 'Ventas';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::where('series', 'NV02')
            ->with(['details.product', 'client']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('series', 'NV02')
            ->where('payment_validation_status', PaymentValidationStatus::PENDING_VALIDATION)
            ->count();
        
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3) // Grid de 3 columnas
            ->schema([
                // COLUMNA IZQUIERDA (Datos del Pedido) - Ocupa 2/3
                \Filament\Forms\Components\Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Información del Pedido')
                            ->icon('heroicon-o-document-text')
                            ->columns(3)
                            ->schema([
                                TextInput::make('full_number')
                                    ->label('N° Pedido')
                                    ->disabled()
                                    ->columnSpan(1),

                                DatePicker::make('issue_date')
                                    ->label('Fecha')
                                    ->disabled()
                                    ->columnSpan(1),

                                Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'draft' => 'Pendiente',
                                        'paid' => 'Completado',
                                        'cancelled' => 'Cancelado',
                                    ])
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                        Section::make('Datos del Cliente')
                            ->icon('heroicon-o-user')
                            ->collapsed()
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
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Productos del Pedido')
                            ->icon('heroicon-o-shopping-bag')
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

                        Section::make('Observaciones')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->collapsed()
                            ->visible(fn ($record) => !empty($record?->observations))
                            ->schema([
                                Textarea::make('observations')
                                    ->label('')
                                    ->disabled()
                                    ->rows(3),
                            ]),
                    ]),

                // COLUMNA DERECHA (Panel de Gestión) - Ocupa 1/3
                \Filament\Forms\Components\Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Gestión de Pago')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                TextInput::make('payment_method')
                                    ->label('Método')
                                    ->formatStateUsing(function ($state) {
                                        return match ($state) {
                                            'cash' => '💵 Efectivo',
                                            'yape' => '📱 Yape',
                                            'plin' => '💳 Plin',
                                            'card' => '💳 Tarjeta',
                                            'transfer' => '🏦 Transferencia',
                                            default => $state,
                                        };
                                    })
                                    ->disabled(),

                                TextInput::make('total_amount')
                                    ->label('Monto Total')
                                    ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state ?? 0, 2))
                                    ->disabled()
                                    ->extraInputAttributes(['class' => 'text-xl font-bold text-success-600']),

                                Select::make('payment_validation_status')
                                    ->label('Estado Validación')
                                    ->options(PaymentValidationStatus::getOptions())
                                    ->disabled()
                                    ->visible(fn ($record) => $record?->requiresPaymentValidation()),

                                Placeholder::make('payment_evidence_viewer')
                                    ->label('Comprobante')
                                    ->content(function ($record) {
                                        if (!$record?->payment_evidence_path) {
                                            return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500 italic">Sin comprobante</p>');
                                        }

                                        $url = route('payment-evidence.show', $record->id);
                                        $extension = pathinfo($record->payment_evidence_path, PATHINFO_EXTENSION);

                                        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                                            return new \Illuminate\Support\HtmlString(
                                                '<div class="mt-2">
                                                    <a href="' . $url . '" target="_blank">
                                                        <img src="' . $url . '" class="w-full rounded-lg border border-gray-200 hover:opacity-75 transition" alt="Comprobante">
                                                    </a>
                                                </div>'
                                            );
                                        } else {
                                            return new \Illuminate\Support\HtmlString(
                                                '<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-500 mt-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                    Ver Documento
                                                </a>'
                                            );
                                        }
                                    })
                                    ->visible(fn ($record) => !empty($record?->payment_evidence_path)),

                                TextInput::make('payment_operation_number')
                                    ->label('N° Operación')
                                    ->disabled()
                                    ->visible(fn ($record) => !empty($record?->payment_operation_number)),
                            ]),

                        Section::make('Gestión de Entrega')
                            ->icon('heroicon-o-truck')
                            ->visible(fn ($record) => $record?->hasDeliveryScheduled())
                            ->schema([
                                Select::make('delivery_status')
                                    ->label('Estado Actual')
                                    ->options(DeliveryStatus::getOptions())
                                    ->disabled(), // Se cambia con acciones

                                DatePicker::make('delivery_date')
                                    ->label('Fecha Programada')
                                    ->disabled(),

                                Select::make('delivery_time_slot')
                                    ->label('Horario')
                                    ->options(DeliveryTimeSlot::getOptions())
                                    ->disabled(),

                                Textarea::make('delivery_notes')
                                    ->label('Notas de Entrega')
                                    ->disabled()
                                    ->rows(2)
                                    ->visible(fn ($record) => !empty($record?->delivery_notes)),
                            ]),
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
                    ->sortable(),

                TextColumn::make('client_business_name')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('issue_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                BadgeColumn::make('delivery_status')
                    ->label('Estado Entrega')
                    ->colors([
                        'info' => DeliveryStatus::PROGRAMADO,
                        'warning' => DeliveryStatus::EN_RUTA,
                        'success' => DeliveryStatus::ENTREGADO,
                        'danger' => DeliveryStatus::REPROGRAMADO,
                    ])
                    ->icons([
                        'heroicon-o-calendar' => DeliveryStatus::PROGRAMADO,
                        'heroicon-o-truck' => DeliveryStatus::EN_RUTA,
                        'heroicon-o-check-badge' => DeliveryStatus::ENTREGADO,
                        'heroicon-o-arrow-path' => DeliveryStatus::REPROGRAMADO,
                    ])
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),

                BadgeColumn::make('payment_method')
                    ->label('Método Pago')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'cash' => '💵 Efectivo',
                        'yape' => '📱 Yape',
                        'plin' => '💳 Plin',
                        'card' => '💳 Tarjeta',
                        'transfer' => '🏦 Transfer.',
                        default => $state,
                    })
                    ->colors([
                        'warning' => fn ($state) => in_array($state, ['yape', 'plin']),
                        'info' => 'transfer',
                        'success' => 'cash',
                        'primary' => 'card',
                    ]),

                IconColumn::make('payment_evidence_path')
                    ->label('Comprobante')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn ($record) => $record->payment_evidence_path 
                        ? 'Comprobante subido' 
                        : 'Sin comprobante - Requiere seguimiento'),

                BadgeColumn::make('payment_validation_status')
                    ->label('Validación Pago')
                    ->formatStateUsing(fn ($state) => $state?->label() ?? 'N/A')
                    ->colors([
                        'warning' => 'pending_validation',
                        'success' => 'payment_approved',
                        'danger' => 'payment_rejected',
                        'info' => 'cash_on_delivery',
                        'gray' => 'validation_not_required',
                    ])
                    ->icon(fn ($state) => match($state?->value) {
                        'pending_validation' => 'heroicon-o-clock',
                        'payment_approved' => 'heroicon-o-check-circle',
                        'payment_rejected' => 'heroicon-o-x-circle',
                        'cash_on_delivery' => 'heroicon-o-banknotes',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                BadgeColumn::make('status')
                    ->label('Estado Pedido')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'draft' => 'Pendiente',
                        'paid' => 'Completado',
                        'cancelled' => 'Cancelado',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->defaultSort('issue_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado Pedido')
                    ->options([
                        'draft' => 'Pendiente',
                        'paid' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),
                
                SelectFilter::make('payment_validation_status')
                    ->label('Estado Pago')
                    ->options(PaymentValidationStatus::getOptions())
                    ->default('pending_validation'),
                
                SelectFilter::make('payment_method')
                    ->label('Método de Pago')
                    ->options([
                        'cash' => 'Efectivo',
                        'yape' => 'Yape',
                        'plin' => 'Plin',
                        'card' => 'Tarjeta',
                        'transfer' => 'Transferencia',
                    ]),
                
                Filter::make('sin_comprobante')
                    ->label('Sin Comprobante')
                    ->query(fn (Builder $query) => $query->whereNull('payment_evidence_path'))
                    ->toggle(),
                
                SelectFilter::make('delivery_type')
                    ->label('Tipo Entrega')
                    ->options([
                        'pickup' => 'Recojo en Tienda',
                        'delivery' => 'Delivery',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === 'pickup') {
                            return $query->where('client_address', 'Recojo en Tienda');
                        } elseif ($data['value'] === 'delivery') {
                            return $query->where('client_address', '!=', 'Recojo en Tienda');
                        }
                    }),
            ])
            ->actions([
                Action::make('view_evidence')
                    ->label('Ver Comprobante')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->url(fn (Invoice $record): string => route('payment-evidence.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Invoice $record): bool => !empty($record->payment_evidence_path)),
                
                Action::make('approve_payment')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar Pago')
                    ->modalDescription(fn (Invoice $record) => 
                        "¿Aprobar el pago del pedido {$record->full_number} por S/ {$record->total_amount}?"
                    )
                    ->modalSubmitActionLabel('Sí, Aprobar')
                    ->action(function (Invoice $record) {
                        $record->approvePayment(Auth::id(), 'Pago aprobado desde Pedidos Web');
                        
                        Notification::make()
                            ->title('Pago Aprobado')
                            ->success()
                            ->body("El pago del pedido {$record->full_number} ha sido aprobado.")
                            ->send();
                    })
                    ->visible(fn (Invoice $record): bool => 
                        $record->payment_validation_status === PaymentValidationStatus::PENDING_VALIDATION ||
                        $record->payment_validation_status === PaymentValidationStatus::PAYMENT_REJECTED
                    ),
                
                Action::make('reject_payment')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rechazar Pago')
                    ->modalDescription(fn (Invoice $record) => 
                        "¿Rechazar el pago del pedido {$record->full_number}?"
                    )
                    ->form([
                        Textarea::make('rejection_notes')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->placeholder('Explica por qué se rechaza el pago (comprobante falso, monto incorrecto, etc.)')
                            ->rows(3)
                    ])
                    ->action(function (array $data, Invoice $record) {
                        $record->rejectPayment(Auth::id(), $data['rejection_notes']);
                        
                        Notification::make()
                            ->title('Pago Rechazado')
                            ->warning()
                            ->body("El pago del pedido {$record->full_number} ha sido rechazado.")
                            ->send();
                    })
                    ->visible(fn (Invoice $record): bool => 
                        $record->payment_validation_status === PaymentValidationStatus::PENDING_VALIDATION
                    ),
                
                Action::make('mark_en_ruta')
                    ->label('Marcar En Ruta')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->updateDeliveryStatus(DeliveryStatus::EN_RUTA);
                        Notification::make()->title('Pedido en ruta')->success()->send();
                    })
                    ->visible(fn (Invoice $record) => 
                        $record->delivery_status === DeliveryStatus::PROGRAMADO && 
                        $record->payment_validation_status === PaymentValidationStatus::PAYMENT_APPROVED
                    ),

                Action::make('mark_entregado')
                    ->label('Marcar Entregado')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->updateDeliveryStatus(DeliveryStatus::ENTREGADO);
                        Notification::make()->title('Pedido entregado')->success()->send();
                    })
                    ->visible(fn (Invoice $record) => 
                        $record->delivery_status === DeliveryStatus::EN_RUTA
                    ),

                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve_payment')
                        ->label('Aprobar Pagos Seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Aprobar Pagos Masivamente')
                        ->modalDescription('¿Estás seguro de aprobar todos los pagos seleccionados?')
                        ->action(function (Collection $records) {
                            $approved = 0;
                            foreach ($records as $record) {
                                if ($record->payment_validation_status === PaymentValidationStatus::PENDING_VALIDATION ||
                                    $record->payment_validation_status === PaymentValidationStatus::PAYMENT_REJECTED) {
                                    $record->approvePayment(Auth::id(), 'Aprobación masiva desde Pedidos Web');
                                    $approved++;
                                }
                            }
                            
                            Notification::make()
                                ->title('Pagos Aprobados')
                                ->success()
                                ->body("{$approved} pagos han sido aprobados exitosamente.")
                                ->send();
                        }),
                    
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebOrders::route('/'),
            'view' => Pages\ViewWebOrder::route('/{record}'),
        ];
    }
}
