<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use App\Models\PaymentInstallment;

class PaymentInstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentInstallments';

    protected static ?string $recordTitleAttribute = 'installment_number';

    public static function getModelLabel(): string
    {
        return __('Cuota');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Cuotas');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('installment_number')->label(__('N°')),
                TextColumn::make('amount')
                    ->money(fn ($r) => $r->invoice->currency_code)
                    ->label(__('Monto'))
                    ->sortable()
                    ->summarize(Sum::make()->label(__('Total'))),
                TextColumn::make('paid_amount')
                    ->money(fn ($r) => $r->invoice->currency_code)
                    ->label(__('Pagado'))
                    ->sortable()
                    ->summarize(Sum::make()->label(__('Total pagado'))),
                TextColumn::make('pending_amount')
                    ->money(fn ($r) => $r->invoice->currency_code)
                    ->label(__('Pendiente'))
                    ->sortable()
                    ->summarize(Sum::make()->label(__('Total pendiente'))),
                TextColumn::make('due_date')->date()->label(__('Vencimiento'))->sortable(),
                IconColumn::make('status')
                    ->label(__('Estado'))
                    ->icon(fn (string $state) => match ($state) {
                        'paid' => 'heroicon-o-check-circle',
                        'partial_paid' => 'heroicon-o-exclamation-circle',
                        'overdue' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'partial_paid' => 'warning',
                        'overdue' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Action::make('abonar')
                    ->label(__('Abonar'))
                    ->icon('heroicon-o-banknotes')
                    ->visible(fn (PaymentInstallment $record) => (float) $record->pending_amount > 0)
                    ->form([
                        TextInput::make('amount')
                            ->label(__('Monto'))
                            ->numeric()
                            ->minValue(0.01)
                            ->maxValue(fn (PaymentInstallment $record): float => (float) $record->pending_amount)
                            ->required(),
                        DateTimePicker::make('paid_at')
                            ->label(__('Fecha de pago'))
                            ->seconds(false)
                            ->default(now()),
                        TextInput::make('reference')
                            ->label(__('Referencia'))
                            ->maxLength(100),
                    ])
                    ->action(function (PaymentInstallment $record, array $data): void {
                        $record->markAsPaid((float) $data['amount'], $data['reference'] ?? null);
                        if (! empty($data['paid_at'])) {
                            $record->paid_at = $data['paid_at'];
                            $record->saveQuietly();
                        }
                    })
                    ->successNotificationTitle(__('Pago registrado')),
                EditAction::make()
                    ->label(__('Editar pago'))
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading(__('Editar pago'))
                    ->form([
                        TextInput::make('amount')
                            ->label(__('Monto de la cuota'))
                            ->numeric()
                            ->minValue(0.01)
                            ->required()
                            ->reactive(),
                        TextInput::make('paid_amount')
                            ->label(__('Monto pagado'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn (callable $get) => (float) ($get('amount') ?? 0))
                            ->required(),
                        TextInput::make('payment_reference')
                            ->label(__('Referencia'))
                            ->maxLength(100),
                        DateTimePicker::make('due_date')
                            ->label(__('Vencimiento')),
                        DateTimePicker::make('paid_at')
                            ->label(__('Fecha de pago'))
                            ->seconds(false),
                    ])
                    ->successNotificationTitle(__('Pago actualizado')),
                DeleteAction::make()
                    ->label(__('Eliminar pago'))
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading(__('Eliminar pago'))
                    ->modalDescription(__('Esta acción eliminará el registro de pago y actualizará los saldos de la factura. ¿Deseas continuar?'))
                    ->successNotificationTitle(__('Pago eliminado')),
            ]);
    }
}
