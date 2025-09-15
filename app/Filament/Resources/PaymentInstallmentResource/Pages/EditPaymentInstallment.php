<?php

namespace App\Filament\Resources\PaymentInstallmentResource\Pages;

use App\Filament\Resources\PaymentInstallmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentInstallment extends EditRecord
{
    protected static string $resource = PaymentInstallmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}