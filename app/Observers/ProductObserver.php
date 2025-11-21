<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     * This runs BEFORE the product is saved to the database.
     */
    public function creating(Product $product): void
    {
        // Generar código de barras automáticamente si está vacío
        if (empty($product->barcode)) {
            $product->barcode = $product->generateUniqueBarcode();
        }
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        // Si el producto tiene control de inventario activado
        if ($product->track_inventory) {
            // Obtener los datos del formulario desde la sesión o request
            $warehouseId = request()->input('warehouse_id');
            $initialStock = request()->input('initial_stock', 0);
            $minimumStock = request()->input('minimum_stock', 0);
            
            // Si se proporcionó un almacén, crear el registro de stock
            if ($warehouseId) {
                \App\Models\Stock::create([
                    'company_id' => $product->company_id,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'qty' => $initialStock,
                    'min_qty' => $minimumStock,
                ]);
            }
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Si la imagen cambió, eliminar la imagen anterior
        if ($product->isDirty('image_path')) {
            $originalImagePath = $product->getOriginal('image_path');
            if ($originalImagePath && \Storage::disk('public')->exists($originalImagePath)) {
                \Storage::disk('public')->delete($originalImagePath);
            }
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        // Eliminar la imagen cuando se elimina el producto
        $product->deleteImage();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        // Eliminar la imagen cuando se elimina permanentemente el producto
        $product->deleteImage();
    }
}
