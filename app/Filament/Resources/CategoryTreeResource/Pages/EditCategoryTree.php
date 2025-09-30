<?php

namespace App\Filament\Resources\CategoryTreeResource\Pages;

use App\Filament\Resources\CategoryTreeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoryTree extends EditRecord
{
    protected static string $resource = CategoryTreeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
