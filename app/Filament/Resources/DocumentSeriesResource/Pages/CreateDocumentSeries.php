<?php

namespace App\Filament\Resources\DocumentSeriesResource\Pages;

use App\Filament\Resources\DocumentSeriesResource;
use App\Models\Company;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentSeries extends CreateRecord
{
    protected static string $resource = DocumentSeriesResource::class;

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