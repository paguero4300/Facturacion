<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
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
