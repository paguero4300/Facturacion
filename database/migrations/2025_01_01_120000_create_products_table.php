<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Company relationship
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Product identification
            $table->string('code', 50)->comment('Código interno del producto');
            $table->string('name', 500)->comment('Descripción del producto/servicio');
            $table->text('description')->nullable()->comment('Descripción detallada');
            
            // SUNAT classification
            $table->string('sunat_code', 10)->nullable()->comment('Código SUNAT del producto');
            $table->enum('product_type', [
                'product',  // Bien
                'service'   // Servicio
            ])->default('product')->comment('Tipo de ítem');
            
            // Unit of measure - Catálogo 03 SUNAT
            $table->string('unit_code', 5)->default('NIU')->comment('Código unidad de medida según catálogo 03');
            $table->string('unit_description', 100)->default('UNIDAD (BIENES)')->comment('Descripción unidad de medida');
            
            // Pricing information
            $table->decimal('unit_price', 12, 4)->comment('Precio unitario sin IGV');
            $table->decimal('sale_price', 12, 4)->comment('Precio de venta con IGV');
            $table->decimal('cost_price', 12, 4)->nullable()->comment('Precio de costo');
            $table->decimal('minimum_price', 12, 4)->nullable()->comment('Precio mínimo de venta');
            
            // Tax information - Catálogo 07 SUNAT
            $table->enum('tax_type', [
                '10', // Gravado - Operación Onerosa
                '11', // Gravado - Retiro por premio
                '12', // Gravado - Retiro por donación
                '13', // Gravado - Retiro
                '14', // Gravado - Retiro por publicidad
                '15', // Gravado - Bonificaciones
                '16', // Gravado - Retiro por entrega a trabajadores
                '17', // Gravado - IVAP
                '20', // Exonerado - Operación Onerosa
                '21', // Exonerado - Transferencia gratuita
                '30', // Inafecto - Operación Onerosa
                '31', // Inafecto - Retiro por bonificación
                '32', // Inafecto - Retiro
                '33', // Inafecto - Retiro por muestras médicas
                '34', // Inafecto - Retiro por convenio colectivo
                '35', // Inafecto - Retiro por premio
                '36', // Inafecto - Retiro por publicidad
                '37', // Inafecto - Transferencia gratuita
                '40'  // Exportación
            ])->default('10')->comment('Tipo de afectación del IGV según catálogo 07');
            
            $table->decimal('tax_rate', 5, 4)->default(0.1800)->comment('Tasa de impuesto (18% = 0.1800)');
            
            // Inventory information
            $table->decimal('current_stock', 12, 4)->default(0)->comment('Stock actual');
            $table->decimal('minimum_stock', 12, 4)->default(0)->comment('Stock mínimo');
            $table->decimal('maximum_stock', 12, 4)->nullable()->comment('Stock máximo');
            $table->boolean('track_inventory')->default(true)->comment('Controlar inventario');
            
            // Product categorization
            $table->string('category', 100)->nullable()->comment('Categoría del producto');
            $table->string('brand', 100)->nullable()->comment('Marca');
            $table->string('model', 100)->nullable()->comment('Modelo');
            
            // Additional product information
            $table->decimal('weight', 8, 3)->nullable()->comment('Peso en kg');
            $table->string('barcode', 50)->nullable()->comment('Código de barras');
            $table->string('internal_reference', 100)->nullable()->comment('Referencia interna');
            $table->string('supplier_code', 100)->nullable()->comment('Código del proveedor');
            
            // Status and configuration
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->boolean('taxable')->default(true)->comment('Producto gravado con impuestos');
            $table->boolean('for_sale')->default(true)->comment('Producto para venta');
            $table->boolean('for_purchase')->default(true)->comment('Producto para compra');
            
            // Additional information
            $table->text('notes')->nullable()->comment('Observaciones');
            $table->json('additional_attributes')->nullable()->comment('Atributos adicionales JSON');
            
            // Audit fields
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes and constraints
            $table->unique(['company_id', 'code'], 'unique_product_code');
            $table->index(['company_id', 'status']);
            $table->index(['name']);
            $table->index(['category']);
            $table->index(['tax_type']);
            $table->index(['barcode']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};