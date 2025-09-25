<?php

namespace App\Filament\Resources\InventoryMovementResource\Pages;

use App\Filament\Resources\InventoryMovementResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateInventoryMovement extends CreateRecord
{
    protected static string $resource = InventoryMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generar clave de idempotencia única
        $data['idempotency_key'] = Str::uuid()->toString();
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}