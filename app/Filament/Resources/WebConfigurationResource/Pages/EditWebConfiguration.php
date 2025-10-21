<?php

namespace App\Filament\Resources\WebConfigurationResource\Pages;

use App\Filament\Resources\WebConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebConfiguration extends EditRecord
{
    protected static string $resource = WebConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}