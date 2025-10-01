<?php

namespace App\Filament\Resources\PaymentValidationResource\Pages;

use App\Filament\Resources\PaymentValidationResource;
use App\Enums\PaymentValidationStatus;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewPaymentValidation extends ViewRecord
{
    protected static string $resource = PaymentValidationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_evidence')
                ->label('Ver Comprobante')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(fn () => route('payment.evidence.view', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->hasPaymentEvidence()),
            
            Action::make('download_evidence')
                ->label('Descargar Comprobante')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('payment.evidence.download', $this->record))
                ->visible(fn () => $this->record->hasPaymentEvidence()),
            
            Action::make('approve')
                ->label('Aprobar Pago')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Aprobar Pago')
                ->modalDescription(fn () => "¿Estás seguro de que quieres aprobar el pago del pedido {$this->record->full_number} por S/ {$this->record->total_amount}?")
                ->action(function () {
                    $this->record->approvePayment(Auth::id(), 'Pago aprobado desde vista detallada');
                    
                    Notification::make()
                        ->title('Pago Aprobado')
                        ->success()
                        ->body("El pago del pedido {$this->record->full_number} ha sido aprobado.")
                        ->send();
                    
                    $this->redirect(PaymentValidationResource::getUrl('index'));
                })
                ->visible(fn () => 
                    $this->record->payment_validation_status === PaymentValidationStatus::PENDING_VALIDATION ||
                    $this->record->payment_validation_status === PaymentValidationStatus::PAYMENT_REJECTED
                ),
            
            Action::make('reject')
                ->label('Rechazar Pago')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Rechazar Pago')
                ->modalDescription(fn () => "¿Estás seguro de que quieres rechazar el pago del pedido {$this->record->full_number}?")
                ->form([
                    Textarea::make('rejection_notes')
                        ->label('Motivo del rechazo')
                        ->required()
                        ->placeholder('Explica por qué se rechaza el pago...')
                ])
                ->action(function (array $data) {
                    $this->record->rejectPayment(Auth::id(), $data['rejection_notes']);
                    
                    Notification::make()
                        ->title('Pago Rechazado')
                        ->warning()
                        ->body("El pago del pedido {$this->record->full_number} ha sido rechazado.")
                        ->send();
                    
                    $this->redirect(PaymentValidationResource::getUrl('index'));
                })
                ->visible(fn () => 
                    $this->record->payment_validation_status === PaymentValidationStatus::PENDING_VALIDATION
                ),
        ];
    }

    public function getTitle(): string
    {
        return "Validación de Pago - {$this->record->full_number}";
    }
}