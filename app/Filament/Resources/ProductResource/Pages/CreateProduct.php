<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Company;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

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