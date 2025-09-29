<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class GenerateProductBarcodes extends Command
{
    protected $signature = 'products:generate-barcodes {--force : Regenerar códigos existentes}';

    protected $description = 'Genera códigos de barras para productos que no tienen';

    public function handle()
    {
        $force = $this->option('force');

        $query = Product::query();

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('barcode')->orWhere('barcode', '');
            });
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $this->info('No se encontraron productos sin código de barras.');
            return;
        }

        $this->info("Generando códigos de barras para {$products->count()} productos...");

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        foreach ($products as $product) {
            try {
                $product->barcode = $product->generateUniqueBarcode();
                $product->save();

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("\nError generando código para producto {$product->id}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('✅ Códigos de barras generados exitosamente.');
    }
}