<?php

namespace App\Filament\Resources\WebConfigurationResource\Pages;

use App\Filament\Resources\WebConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebConfigurations extends ListRecords
{
    protected static string $resource = WebConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}