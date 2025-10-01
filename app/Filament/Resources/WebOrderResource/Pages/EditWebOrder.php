<?php

namespace App\Filament\Resources\WebOrderResource\Pages;

use App\Filament\Resources\WebOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditWebOrder extends EditRecord
{
    protected static string $resource = WebOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),

            Actions\Action::make('complete')
                ->label('Completar Pedido')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'paid']);
                    Notification::make()
                        ->success()
                        ->title('Pedido completado')
                        ->body("El pedido {$this->record->full_number} ha sido marcado como completado.")
                        ->send();

                    return redirect()->to(WebOrderResource::getUrl('view', ['record' => $this->record]));
                })
                ->visible(fn () => $this->record->status === 'draft'),

            Actions\Action::make('cancel')
                ->label('Cancelar Pedido')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar Pedido')
                ->modalDescription('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);
                    Notification::make()
                        ->warning()
                        ->title('Pedido cancelado')
                        ->body("El pedido {$this->record->full_number} ha sido cancelado.")
                        ->send();

                    return redirect()->to(WebOrderResource::getUrl('view', ['record' => $this->record]));
                })
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Pedido actualizado exitosamente';
    }
}
