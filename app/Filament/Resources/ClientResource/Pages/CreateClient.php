<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Company;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

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