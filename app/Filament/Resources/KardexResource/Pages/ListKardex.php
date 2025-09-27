<?php

namespace App\Filament\Resources\KardexResource\Pages;

use App\Filament\Resources\KardexResource;
use Filament\Resources\Pages\ListRecords;

class ListKardex extends ListRecords
{

    protected static string $resource = KardexResource::class;

    protected ?string $heading = 'Kardex de Inventario';

    protected ?string $subheading = 'Historial completo de movimientos de inventario del sistema';


    public function getTitle(): string
    {
        return 'Kardex';
    }
}