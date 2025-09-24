<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Company;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar automáticamente la empresa activa
        $activeCompany = Company::where('is_active', true)->first();
        
        if ($activeCompany) {
            $data['company_id'] = $activeCompany->id;
        }
        
        return $data;
    }
}