<?php

namespace App\Filament\Resources\DetallesResource\Pages;

use App\Filament\Resources\DetallesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetalles extends ListRecords
{
    protected static string $resource = DetallesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}