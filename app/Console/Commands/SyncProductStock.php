<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Console\Command;

class SyncProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-stock {--product-id= : ID específico del producto a sincronizar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza el current_stock de productos con la suma de stock de todos los almacenes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando sincronización de stock de productos...');

        $productId = $this->option('product-id');
        
        if ($productId) {
            // Sincronizar un producto específico
            $product = Product::find($productId);
            if (!$product) {
                $this->error("❌ Producto con ID {$productId} no encontrado.");
                return 1;
            }
            
            $this->syncSingleProduct($product);
            $this->info("✅ Producto '{$product->name}' sincronizado correctamente.");
        } else {
            // Sincronizar todos los productos
            $products = Product::where('track_inventory', true)->get();
            $bar = $this->output->createProgressBar($products->count());
            $bar->start();

            $syncedCount = 0;
            $errorCount = 0;

            foreach ($products as $product) {
                try {
                    $this->syncSingleProduct($product);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("❌ Error sincronizando producto {$product->id}: " . $e->getMessage());
                    $errorCount++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info("✅ Sincronización completada:");
            $this->line("   - Productos sincronizados: {$syncedCount}");
            if ($errorCount > 0) {
                $this->line("   - Errores: {$errorCount}");
            }
        }

        return 0;
    }

    /**
     * Sincronizar un producto individual
     */
    private function syncSingleProduct(Product $product): void
    {
        $oldStock = $product->current_stock;
        $totalStock = Stock::where('product_id', $product->id)->sum('qty');
        
        $product->update(['current_stock' => $totalStock]);

        if ($this->option('verbose')) {
            $this->line("Producto {$product->id} '{$product->name}': {$oldStock} → {$totalStock}");
        }
    }
}