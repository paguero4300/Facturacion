<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Company;
use App\Models\Client;
use App\Models\DocumentSeries;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar automáticamente la empresa activa
        $activeCompany = Company::where('is_active', true)->first();
        
        if ($activeCompany) {
            $data['company_id'] = $activeCompany->id;
        }

        // Snapshot client data
        if (!empty($data['client_id'])) {
            $client = Client::find($data['client_id']);
            if ($client) {
                $data['client_document_type'] = $client->document_type;
                $data['client_document_number'] = $client->document_number;
                $data['client_business_name'] = $client->business_name;
                $data['client_address'] = $client->address;
            }
        }

        // Default operation type
        $data['operation_type'] = $data['operation_type'] ?? '0101';

        // Assign series and correlativo atomically based on selected DocumentSeries
        if (!empty($data['document_series_id'])) {
            DB::transaction(function () use (&$data) {
                $ds = DocumentSeries::query()->lockForUpdate()->findOrFail($data['document_series_id']);
                $next = $ds->getNextNumber(); // increments current_number and touches last_used_at

                $data['series'] = $ds->series;
                $data['number'] = str_pad($next, 8, '0', STR_PAD_LEFT);
                $data['full_number'] = $ds->series . '-' . $data['number'];
            });
        }

        return $data;
    }
}
