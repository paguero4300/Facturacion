<?php

namespace App\Filament\Resources\DocumentSeriesResource\Pages;

use App\Filament\Resources\DocumentSeriesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentSeries extends ListRecords
{
    protected static string $resource = DocumentSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}