<?php

namespace App\Filament\Resources\CategoryTreeResource\Pages;

use App\Filament\Resources\CategoryTreeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategoryTree extends ViewRecord
{
    protected static string $resource = CategoryTreeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
