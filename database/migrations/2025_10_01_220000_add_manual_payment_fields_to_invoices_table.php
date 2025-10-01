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
        Schema::table('invoices', function (Blueprint $table) {
            // Campos para evidencia de pago
            $table->string('payment_evidence_path')->nullable()
                ->comment('Ruta del archivo de comprobante de pago subido');
            
            // Estado de validación de pago
            $table->enum('payment_validation_status', [
                'pending_validation',   // Pago reportado, esperando validación
                'payment_approved',     // Pago verificado y aprobado
                'payment_rejected',     // Pago rechazado, requiere nuevo comprobante
                'cash_on_delivery',     // Efectivo contra entrega programado
                'validation_not_required' // No requiere validación (efectivo, etc.)
            ])->nullable()->comment('Estado de validación del pago manual');
            
            // Metadatos de validación
            $table->timestamp('payment_validated_at')->nullable()
                ->comment('Fecha y hora de validación del pago');
            
            $table->foreignId('payment_validated_by')->nullable()
                ->constrained('users')->nullOnDelete()
                ->comment('Usuario administrador que validó el pago');
            
            // Información adicional del pago reportado por el cliente
            $table->string('payment_operation_number')->nullable()
                ->comment('Número de operación reportado por el cliente');
            
            $table->string('client_payment_phone', 15)->nullable()
                ->comment('Teléfono usado para Yape/Plin reportado por cliente');
            
            // Notas administrativas para la validación
            $table->text('payment_validation_notes')->nullable()
                ->comment('Notas del administrador sobre la validación del pago');
            
            // Índices para mejorar consultas
            $table->index('payment_validation_status');
            $table->index('payment_validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['payment_validation_status']);
            $table->dropIndex(['payment_validated_at']);
            
            $table->dropForeign(['payment_validated_by']);
            
            $table->dropColumn([
                'payment_evidence_path',
                'payment_validation_status',
                'payment_validated_at', 
                'payment_validated_by',
                'payment_operation_number',
                'client_payment_phone',
                'payment_validation_notes'
            ]);
        });
    }
};