<?php

namespace App\Filament\Resources\WebOrderResource\Pages;

use App\Filament\Resources\WebOrderResource;
use App\Enums\PaymentValidationStatus;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewWebOrder extends ViewRecord
{
    protected static string $resource = WebOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve_payment')
                ->label('Aprobar Pago')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aprobar Pago')
                ->modalDescription(fn () => 
                    "¿Aprobar el pago del pedido {$this->record->full_number} por S/ {$this->record->total_amount}?"
                )
                ->modalSubmitActionLabel('Sí, Aprobar')
                ->action(function () {
                    $this->record->approvePayment(Auth::id(), 'Aprobado desde vista detallada');
                    
                    Notification::make()
                        ->title('Pago Aprobado')
                        ->success()
                        ->body("El pago del pedido {$this->record->full_number} ha sido aprobado.")
                        ->send();
                        
                    $this->redirect(static::getResource()::getUrl('index'));
                })
                ->visible(fn () => 
                    $this->record->payment_validation_status === PaymentValidationStatus::PENDING_VALIDATION ||
                    $this->record->payment_validation_status === PaymentValidationStatus::PAYMENT_REJECTED
                ),
            
            Actions\Action::make('reject_payment')
                ->label('Rechazar Pago')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rechazar Pago')
                ->modalDescription(fn () => 
                    "¿Rechazar el pago del pedido {$this->record->full_number}?"
                )
                ->form([
                    Textarea::make('rejection_notes')
                        ->label('Motivo del rechazo')
                        ->required()
                        ->placeholder('Explica por qué se rechaza el pago...')
                        ->rows(3)
                ])
                ->action(function (array $data) {
                    $this->record->rejectPayment(Auth::id(), $data['rejection_notes']);
                    
                    Notification::make()
                        ->title('Pago Rechazado')
                        ->warning()
                        ->body("El pago del pedido {$this->record->full_number} ha sido rechazado.")
                        ->send();
                        
                    $this->redirect(static::getResource()::getUrl('index'));
                })
                ->visible(fn () => 
                    $this->record->payment_validation_status === PaymentValidationStatus::PENDING_VALIDATION
                ),
            
            Actions\EditAction::make(),
        ];
    }
}
