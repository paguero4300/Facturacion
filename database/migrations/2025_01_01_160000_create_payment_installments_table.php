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
        Schema::create('payment_installments', function (Blueprint $table) {
            $table->id();
            
            // Invoice relationship
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            
            // Installment information
            $table->unsignedSmallInteger('installment_number')->comment('Número de cuota');
            $table->decimal('amount', 12, 2)->comment('Monto de la cuota');
            $table->date('due_date')->comment('Fecha de vencimiento');
            $table->decimal('paid_amount', 12, 2)->default(0)->comment('Monto pagado');
            $table->decimal('pending_amount', 12, 2)->comment('Saldo pendiente');
            
            // Payment status
            $table->enum('status', [
                'pending',      // Pendiente
                'paid',         // Pagado
                'partial_paid', // Parcialmente pagado
                'overdue',      // Vencido
                'cancelled'     // Cancelado
            ])->default('pending');
            
            // Payment tracking
            $table->timestamp('paid_at')->nullable()->comment('Fecha de pago');
            $table->string('payment_reference', 100)->nullable()->comment('Referencia de pago');
            
            // Late payment calculation
            $table->decimal('late_fee_rate', 5, 4)->default(0)->comment('Tasa mora diaria');
            $table->decimal('late_fee_amount', 12, 2)->default(0)->comment('Monto mora acumulada');
            $table->integer('days_overdue')->default(0)->comment('Días de atraso');
            
            // Additional information
            $table->text('notes')->nullable()->comment('Observaciones');
            
            // Audit fields
            $table->timestamps();
            
            // Indexes and constraints
            $table->unique(['invoice_id', 'installment_number'], 'unique_invoice_installment');
            $table->index(['invoice_id', 'status']);
            $table->index(['due_date']);
            $table->index(['status']);
        });
        
        // Add check constraints after table creation
        DB::statement('ALTER TABLE payment_installments ADD CONSTRAINT check_positive_amount 
            CHECK (amount > 0)'
        );
        
        DB::statement('ALTER TABLE payment_installments ADD CONSTRAINT check_paid_amount_limit 
            CHECK (paid_amount >= 0 AND paid_amount <= amount)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_installments');
    }
};