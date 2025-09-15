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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Company and series relationships
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_series_id')->constrained()->onDelete('restrict');
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            
            // Document identification
            $table->string('series', 4)->comment('Serie del documento');
            $table->unsignedBigInteger('number')->comment('Número correlativo');
            $table->string('full_number', 15)->comment('Número completo (serie-número)');
            
            // Document type from series
            $table->enum('document_type', [
                '01', // Factura
                '03', // Boleta de Venta
                '07', // Nota de Crédito
                '08', // Nota de Débito
            ])->comment('Tipo de comprobante');
            
            // Document dates
            $table->date('issue_date')->comment('Fecha de emisión');
            $table->time('issue_time')->comment('Hora de emisión');
            $table->date('due_date')->nullable()->comment('Fecha de vencimiento');
            
            // Currency - Catálogo 02 SUNAT
            $table->char('currency_code', 3)->default('PEN')->comment('Código de moneda ISO');
            $table->decimal('exchange_rate', 10, 6)->default(1.000000)->comment('Tipo de cambio');
            
            // Client information (snapshot at invoice time)
            $table->enum('client_document_type', [
                '0', '1', '4', '6', '7', 'A', 'B', 'C'
            ])->comment('Tipo documento cliente');
            $table->string('client_document_number', 15)->comment('Número documento cliente');
            $table->string('client_business_name', 500)->comment('Razón social cliente');
            $table->string('client_address', 1000)->nullable()->comment('Dirección cliente');
            $table->string('client_email', 200)->nullable()->comment('Email cliente');
            
            // Operation type - Catálogo 51 SUNAT
            $table->string('operation_type', 4)->default('0101')->comment('Tipo de operación según catálogo 51');
            
            // Amounts and taxes
            $table->decimal('subtotal', 12, 2)->default(0)->comment('Subtotal (base imponible)');
            $table->decimal('tax_exempt_amount', 12, 2)->default(0)->comment('Monto exonerado');
            $table->decimal('unaffected_amount', 12, 2)->default(0)->comment('Monto inafecto');
            $table->decimal('free_amount', 12, 2)->default(0)->comment('Monto gratuito');
            
            // IGV calculation
            $table->decimal('igv_rate', 5, 4)->default(0.1800)->comment('Tasa IGV aplicada');
            $table->decimal('igv_amount', 12, 2)->default(0)->comment('Monto IGV');
            
            // Other taxes (if applicable)
            $table->decimal('isc_amount', 12, 2)->default(0)->comment('Monto ISC');
            $table->decimal('other_taxes_amount', 12, 2)->default(0)->comment('Otros tributos');
            
            // Charges and discounts
            $table->decimal('total_charges', 12, 2)->default(0)->comment('Total cargos');
            $table->decimal('total_discounts', 12, 2)->default(0)->comment('Total descuentos');
            $table->decimal('global_discount', 12, 2)->default(0)->comment('Descuento global');
            
            // Totals
            $table->decimal('total_amount', 12, 2)->comment('Importe total');
            $table->decimal('paid_amount', 12, 2)->default(0)->comment('Monto pagado');
            $table->decimal('pending_amount', 12, 2)->default(0)->comment('Saldo pendiente');
            
            // Payment information
            $table->enum('payment_method', [
                'cash',           // Efectivo
                'card',          // Tarjeta
                'transfer',      // Transferencia
                'check',         // Cheque
                'credit',        // Crédito
                'deposit',       // Depósito
                'other'          // Otro
            ])->default('cash')->comment('Método de pago');
            
            $table->enum('payment_condition', [
                'immediate',     // Contado
                'credit'         // Crédito
            ])->default('immediate')->comment('Condición de pago');
            
            $table->integer('credit_days')->default(0)->comment('Días de crédito');
            
            // Credit/Debit note references (for document types 07, 08)
            $table->string('reference_document_type', 2)->nullable()->comment('Tipo documento referencia');
            $table->string('reference_series', 4)->nullable()->comment('Serie documento referencia');
            $table->string('reference_number', 8)->nullable()->comment('Número documento referencia');
            $table->date('reference_date')->nullable()->comment('Fecha documento referencia');
            $table->string('modification_reason', 500)->nullable()->comment('Motivo de modificación');
            $table->enum('modification_type', [
                '01', // Anulación de la operación
                '02', // Anulación por error en el RUC
                '03', // Corrección por error en la descripción
                '04', // Descuento global
                '05', // Descuento por ítem
                '06', // Devolución total
                '07', // Devolución por ítem
                '08', // Bonificación
                '09', // Disminución en el valor
                '10', // Otros conceptos
                '11', // Ajustes de operaciones de exportación
                '12', // Ajustes afectos al IVAP
            ])->nullable()->comment('Tipo de modificación para notas');
            
            // SUNAT electronic processing
            $table->enum('sunat_status', [
                'pending',       // Pendiente de envío
                'sent',         // Enviado a SUNAT
                'accepted',     // Aceptado por SUNAT
                'rejected',     // Rechazado por SUNAT
                'observed',     // Observado por SUNAT
                'cancelled',    // Anulado
                'voided'        // Dado de baja
            ])->default('pending')->comment('Estado en SUNAT');
            
            $table->string('sunat_response_code', 10)->nullable()->comment('Código respuesta SUNAT');
            $table->text('sunat_response_description')->nullable()->comment('Descripción respuesta SUNAT');
            $table->timestamp('sunat_sent_at')->nullable()->comment('Fecha envío SUNAT');
            $table->timestamp('sunat_processed_at')->nullable()->comment('Fecha procesado SUNAT');
            
            // SUNAT technical data
            $table->string('cdr_zip_path', 500)->nullable()->comment('Ruta archivo CDR');
            $table->string('xml_signed_path', 500)->nullable()->comment('Ruta XML firmado');
            $table->string('pdf_path', 500)->nullable()->comment('Ruta PDF generado');
            $table->string('hash_sign', 500)->nullable()->comment('Hash de la firma digital');
            
            // QR Code data
            $table->text('qr_code')->nullable()->comment('Datos código QR');
            
            // Status and control
            $table->enum('status', [
                'draft',        // Borrador
                'issued',       // Emitido
                'sent',         // Enviado
                'paid',         // Pagado
                'partial_paid', // Parcialmente pagado
                'overdue',      // Vencido
                'cancelled',    // Anulado
                'voided'        // Dado de baja
            ])->default('draft');
            
            $table->boolean('is_contingency')->default(false)->comment('Documento de contingencia');
            $table->string('contingency_reason', 500)->nullable()->comment('Motivo contingencia');
            
            // User tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Additional information
            $table->text('observations')->nullable()->comment('Observaciones');
            $table->json('additional_data')->nullable()->comment('Datos adicionales JSON');
            
            // Audit fields
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes and constraints
            $table->unique(['company_id', 'series', 'number'], 'unique_invoice_number');
            $table->unique(['full_number', 'company_id'], 'unique_full_number');
            
            $table->index(['company_id', 'document_type', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['issue_date']);
            $table->index(['due_date']);
            $table->index(['sunat_status']);
            $table->index(['status']);
            $table->index(['payment_condition']);
            $table->index(['created_by']);
            $table->index(['created_at']);
            
            // Foreign key for reference documents (self-referencing)
            $table->index(['reference_document_type', 'reference_series', 'reference_number'], 'idx_invoices_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};