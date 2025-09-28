<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Stock;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    // Propiedades públicas para capturar datos de campos dehydrated(false)
    public $warehouseId;
    public $initialStock = 0;
    public $minimumStockInput = 0;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $product = $this->record;

        // Cargar datos del stock si existe
        $stock = Stock::where('product_id', $product->id)
                     ->where('company_id', $product->company_id)
                     ->first();

        if ($stock) {
            $data['warehouse_id'] = $stock->warehouse_id;
            $data['initial_stock'] = $stock->qty;
            $data['minimum_stock_input'] = $stock->min_qty;

            // También inicializar las propiedades públicas
            $this->warehouseId = $stock->warehouse_id;
            $this->initialStock = $stock->qty;
            $this->minimumStockInput = $stock->min_qty;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $product = $this->record;

        // Si se especificó almacén, actualizar o crear registro en stocks
        if ($this->warehouseId) {
            // Buscar stock existente
            $stock = Stock::where('product_id', $product->id)
                         ->where('company_id', $product->company_id)
                         ->where('warehouse_id', $this->warehouseId)
                         ->first();

            if ($stock) {
                // Actualizar stock existente
                $stock->update([
                    'qty' => $this->initialStock ?? 0,
                    'min_qty' => $this->minimumStockInput ?? 0,
                ]);
            } else {
                // Crear nuevo stock
                Stock::create([
                    'company_id' => $product->company_id,
                    'product_id' => $product->id,
                    'warehouse_id' => $this->warehouseId,
                    'qty' => $this->initialStock ?? 0,
                    'min_qty' => $this->minimumStockInput ?? 0,
                ]);
            }

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