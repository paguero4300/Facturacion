<?php

namespace App\Filament\Resources\CategoryTreeResource\Pages;

use App\Filament\Resources\CategoryTreeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryTree extends CreateRecord
{
    protected static string $resource = CategoryTreeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id ?? 1;
        $data['created_by'] = auth()->id();
        $data['parent_id'] = null; // Forzar que siempre sea categoría principal

        return $data;
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('Categoría principal creada exitosamente');
    }
}
