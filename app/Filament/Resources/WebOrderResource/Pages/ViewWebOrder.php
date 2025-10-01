<?php

namespace App\Filament\Resources\WebOrderResource\Pages;

use App\Filament\Resources\WebOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWebOrder extends ViewRecord
{
    protected static string $resource = WebOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
