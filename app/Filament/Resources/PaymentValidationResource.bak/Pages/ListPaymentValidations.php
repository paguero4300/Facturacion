<?php

namespace App\Filament\Resources\PaymentValidationResource\Pages;

use App\Filament\Resources\PaymentValidationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListPaymentValidations extends ListRecords
{
    protected static string $resource = PaymentValidationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for payment validations
        ];
    }

    public function getTitle(): string
    {
        return 'Validación de Pagos';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Could add widgets here for payment validation statistics
        ];
    }
}