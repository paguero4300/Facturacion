<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Company;
use App\Models\Stock;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    // Propiedades públicas para capturar datos de campos dehydrated(false)
    public $warehouseId;
    public $initialStock = 0;
    public $minimumStockInput = 0;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar automáticamente la empresa activa
        $activeCompany = Company::where('is_active', true)->first();

        if ($activeCompany) {
            $data['company_id'] = $activeCompany->id;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $product = $this->record;

        // Generar código de barras automáticamente si no existe
        if (empty($product->barcode)) {
            $product->barcode = $product->generateUniqueBarcode();
            $product->save();
        }

        // Si se especificó almacén, crear registro en stocks
        if ($this->warehouseId) {
            // Crear registro en tabla stocks
            Stock::create([
                'company_id' => $product->company_id,
                'product_id' => $product->id,
                'warehouse_id' => $this->warehouseId,
                'qty' => $this->initialStock ?? 0,
                'min_qty' => $this->minimumStockInput ?? 0,
            ]);

            // Actualizar current_stock en producto si tiene control de inventario
            if ($product->track_inventory) {
                $product->update([
                    'current_stock' => $this->initialStock ?? 0,
                    'minimum_stock' => $this->minimumStockInput ?? 0,
                ]);
            }
        }
    }
}