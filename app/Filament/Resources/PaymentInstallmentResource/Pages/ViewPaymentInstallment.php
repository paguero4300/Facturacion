<?php

namespace App\Filament\Resources\PaymentInstallmentResource\Pages;

use App\Filament\Resources\PaymentInstallmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPaymentInstallment extends ViewRecord
{
    protected static string $resource = PaymentInstallmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}