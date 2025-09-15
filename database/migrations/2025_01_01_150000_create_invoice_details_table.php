<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            
            // Invoice relationship
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('restrict');
            
            // Line sequence
            $table->unsignedSmallInteger('line_number')->comment('Número de línea en el documento');
            
            // Product information (snapshot at invoice time)
            $table->string('product_code', 50)->nullable()->comment('Código del producto');
            $table->text('description')->comment('Descripción del producto/servicio');
            $table->text('additional_description')->nullable()->comment('Descripción adicional');
            
            // Unit of measure - Catálogo 03 SUNAT
            $table->string('unit_code', 5)->default('NIU')->comment('Código unidad medida');
            $table->string('unit_description', 100)->default('UNIDAD (BIENES)')->comment('Descripción unidad medida');
            
            // Quantities
            $table->decimal('quantity', 12, 4)->comment('Cantidad');
            $table->decimal('unit_price', 12, 4)->comment('Precio unitario sin impuestos');
            $table->decimal('unit_value', 12, 4)->comment('Valor unitario sin impuestos');
            
            // Discounts and charges at line level
            $table->decimal('line_discount_percentage', 5, 4)->default(0)->comment('Porcentaje descuento línea');
            $table->decimal('line_discount_amount', 12, 2)->default(0)->comment('Monto descuento línea');
            $table->decimal('line_charge_amount', 12, 2)->default(0)->comment('Monto cargo línea');
            
            // Price calculations
            $table->decimal('gross_amount', 12, 2)->comment('Importe bruto (cantidad × precio)');
            $table->decimal('net_amount', 12, 2)->comment('Importe neto (después desc/cargos)');
            
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
            ])->default('10')->comment('Tipo afectación IGV');
            
            // IGV calculation
            $table->decimal('igv_rate', 5, 4)->default(0.1800)->comment('Tasa IGV');
            $table->decimal('igv_base_amount', 12, 2)->default(0)->comment('Base imponible IGV');
            $table->decimal('igv_amount', 12, 2)->default(0)->comment('Monto IGV');
            
            // ISC (Impuesto Selectivo al Consumo) - if applicable
            $table->enum('isc_type', [
                '01', // Sistema al valor (aplicación del porcentaje)
                '02', // Aplicación del monto fijo
                '03'  // Sistema de precios de venta al público
            ])->nullable()->comment('Tipo de ISC');
            $table->decimal('isc_rate', 8, 6)->default(0)->comment('Tasa o monto fijo ISC');
            $table->decimal('isc_base_amount', 12, 2)->default(0)->comment('Base imponible ISC');
            $table->decimal('isc_amount', 12, 2)->default(0)->comment('Monto ISC');
            
            // Other taxes
            $table->decimal('other_taxes_amount', 12, 2)->default(0)->comment('Otros tributos');
            
            // Total amounts for this line
            $table->decimal('total_taxes', 12, 2)->default(0)->comment('Total impuestos línea');
            $table->decimal('line_total', 12, 2)->comment('Total línea (neto + impuestos)');
            
            // Product attributes for inventory control
            $table->string('batch_number', 50)->nullable()->comment('Número de lote');
            $table->date('expiry_date')->nullable()->comment('Fecha vencimiento');
            $table->string('serial_number', 100)->nullable()->comment('Número de serie');
            
            // Free items indication
            $table->boolean('is_free')->default(false)->comment('Ítem gratuito');
            $table->string('free_reason', 200)->nullable()->comment('Motivo ítem gratuito');
            
            // Reference to original quote/order line (if applicable)
            $table->unsignedBigInteger('quote_detail_id')->nullable()->comment('ID detalle cotización origen');
            $table->unsignedBigInteger('order_detail_id')->nullable()->comment('ID detalle pedido origen');
            
            // Additional line information
            $table->text('line_notes')->nullable()->comment('Observaciones de línea');
            $table->json('additional_attributes')->nullable()->comment('Atributos adicionales JSON');
            
            // Audit fields
            $table->timestamps();
            
            // Indexes and constraints
            $table->unique(['invoice_id', 'line_number'], 'unique_invoice_line');
            
            $table->index(['invoice_id']);
            $table->index(['product_id']);
            $table->index(['product_code']);
            $table->index(['tax_type']);
            $table->index(['is_free']);
            $table->index(['created_at']);
        });
        
        // Add check constraints after table creation
        DB::statement('ALTER TABLE invoice_details ADD CONSTRAINT check_positive_quantity 
            CHECK (quantity > 0)'
        );
        
        DB::statement('ALTER TABLE invoice_details ADD CONSTRAINT check_positive_prices 
            CHECK (unit_price >= 0 AND unit_value >= 0)'
        );
        
        DB::statement('ALTER TABLE invoice_details ADD CONSTRAINT check_tax_rate_range 
            CHECK (igv_rate >= 0 AND igv_rate <= 1)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};