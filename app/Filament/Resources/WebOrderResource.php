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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
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

                Section::make('Información de Pago')
                    ->icon('heroicon-o-credit-card')
                    ->collapsed()
                    ->columns(3)
                    ->schema([
                        TextInput::make('payment_method')
                            ->label('Método de Pago')
                            ->formatStateUsing(function ($state) {
                                return match ($state) {
                                    'cash' => 'Efectivo contra entrega',
                                    'yape' => 'Yape',
                                    'plin' => 'Plin',
                                    'card' => 'Tarjeta',
                                    'transfer' => 'Transferencia Bancaria',
                                    default => $state,
                                };
                            })
                            ->disabled()
                            ->columnSpan(1),

                        TextInput::make('total_amount')
                            ->label('Monto Total')
                            ->formatStateUsing(fn ($state) => 'S/ ' . number_format($state ?? 0, 2))
                            ->disabled()
                            ->columnSpan(1),

                        Select::make('payment_validation_status')
                            ->label('Estado de Validación')
                            ->options(PaymentValidationStatus::getOptions())
                            ->disabled()
                            ->visible(fn ($record) => $record?->requiresPaymentValidation())
                            ->columnSpan(1),

                        TextInput::make('payment_operation_number')
                            ->label('N° de Operación')
                            ->disabled()
                            ->visible(fn ($record) => !empty($record?->payment_operation_number))
                            ->columnSpan(1),

                        TextInput::make('client_payment_phone')
                            ->label('Teléfono Yape/Plin')
                            ->disabled()
                            ->visible(fn ($record) => !empty($record?->client_payment_phone))
                            ->columnSpan(1),

                        TextInput::make('payment_reference')
                            ->label('Referencia')
                            ->disabled()
                            ->visible(fn ($record) => !empty($record?->payment_reference))
                            ->columnSpan(1),

                        Placeholder::make('payment_evidence_viewer')
                            ->label('Comprobante de Pago')
                            ->content(function ($record) {
                                if (!$record?->payment_evidence_path) {
                                    return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500">No se subió comprobante</p>');
                                }

                                $url = route('payment-evidence.show', $record->id);
                                $extension = pathinfo($record->payment_evidence_path, PATHINFO_EXTENSION);

                                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="space-y-2">
                                            <a href="' . $url . '" target="_blank" class="inline-block">
                                                <img src="' . $url . '" class="w-48 rounded-lg border-2 border-gray-300 hover:border-blue-500 hover:scale-105 transition cursor-pointer shadow-sm" alt="Comprobante de pago">
                                            </a>
                                            <p class="text-xs text-gray-500">Clic para ver en tamaño completo</p>
                                        </div>'
                                    );
                                } else {
                                    return new \Illuminate\Support\HtmlString(
                                        '<a href="' . $url . '" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Descargar Comprobante PDF
                                        </a>'
                                    );
                                }
                            })
                            ->visible(fn ($record) => !empty($record?->payment_evidence_path))
                            ->columnSpanFull(),

                        Textarea::make('payment_validation_notes')
                            ->label('Notas de Validación')
                            ->disabled()
                            ->rows(2)
                            ->visible(fn ($record) => !empty($record?->payment_validation_notes))
                            ->columnSpanFull(),

                        TextInput::make('paymentValidatedBy.name')
                            ->label('Validado por')
                            ->disabled()
                            ->visible(fn ($record) => $record?->payment_validated_by)
                            ->columnSpan(1),

                        TextInput::make('payment_validated_at')
                            ->label('Fecha de Validación')
                            ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : '-')
                            ->disabled()
                            ->visible(fn ($record) => $record?->payment_validated_at)
                            ->columnSpan(1),
                    ]),

                Section::make('Entrega Programada')
                    ->icon('heroicon-o-truck')
                    ->collapsed()
                    ->visible(fn ($record) => $record?->hasDeliveryScheduled())
                    ->columns(2)
                    ->schema([
                        DatePicker::make('delivery_date')
                            ->label('Fecha de Entrega')
                            ->disabled(),

                        Select::make('delivery_time_slot')
                            ->label('Horario')
                            ->options(DeliveryTimeSlot::getOptions())
                            ->disabled(),

                        Select::make('delivery_status')
                            ->label('Estado de Entrega')
                            ->options(DeliveryStatus::getOptions()),

                        TimePicker::make('delivery_confirmed_at')
                            ->label('Entregado en')
                            ->disabled()
                            ->visible(fn ($record) => $record?->delivery_status === DeliveryStatus::ENTREGADO),

                        Textarea::make('delivery_notes')
                            ->label('Notas de Entrega')
                            ->disabled()
                            ->rows(2)
                            ->columnSpanFull(),
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

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),

                BadgeColumn::make('payment_validation_status')
                    ->label('Estado Pago')
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '-')
                    ->colors([
                        'warning' => 'pending_validation',
                        'success' => 'payment_approved',
                        'danger' => 'payment_rejected',
                        'info' => 'cash_on_delivery',
                        'gray' => 'validation_not_required',
                    ]),

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
                    ->label('Estado')
                    ->options([
                        'draft' => 'Pendiente',
                        'paid' => 'Completado',
                        'cancelled' => 'Cancelado',
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
