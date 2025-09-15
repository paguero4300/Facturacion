<?php

namespace App\Filament\Resources\PaymentInstallmentResource\Pages;

use App\Filament\Resources\PaymentInstallmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentInstallments extends ListRecords
{
    protected static string $resource = PaymentInstallmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}