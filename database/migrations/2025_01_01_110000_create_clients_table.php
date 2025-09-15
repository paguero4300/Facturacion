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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            
            // Company relationship
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Document identification
            $table->enum('document_type', [
                '0',  // Sin documento
                '1',  // DNI
                '4',  // Carnet de extranjería
                '6',  // RUC
                '7',  // Pasaporte
                'A',  // Cédula diplomática
                'B',  // DOC.TRIB.NO.DOM.SIN.RUC
                'C'   // TAM
            ])->comment('Tipo de documento según catálogo 06 SUNAT');
            
            $table->string('document_number', 15)->comment('Número de documento');
            
            // Client information
            $table->string('business_name', 500)->comment('Razón social / Nombres y apellidos');
            $table->string('commercial_name', 300)->nullable()->comment('Nombre comercial');
            
            // Address information
            $table->string('address', 1000)->nullable()->comment('Dirección');
            $table->string('district', 100)->nullable()->comment('Distrito');
            $table->string('province', 100)->nullable()->comment('Provincia');
            $table->string('department', 100)->nullable()->comment('Departamento');
            $table->string('country_code', 2)->default('PE')->comment('Código de país ISO');
            $table->string('postal_code', 10)->nullable()->comment('Código postal');
            $table->string('ubigeo', 6)->nullable()->comment('Código UBIGEO SUNAT');
            
            // Contact information
            $table->string('phone', 50)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('contact_person', 300)->nullable()->comment('Persona de contacto');
            
            // Commercial information
            $table->decimal('credit_limit', 12, 2)->default(0)->comment('Límite de crédito');
            $table->integer('payment_days')->default(0)->comment('Días de crédito');
            $table->enum('client_type', ['regular', 'vip', 'wholesale', 'retail'])->default('regular');
            
            // Status
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            
            // Additional information
            $table->text('notes')->nullable()->comment('Observaciones');
            $table->json('additional_data')->nullable()->comment('Datos adicionales JSON');
            
            // Audit fields
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes and constraints
            $table->unique(['company_id', 'document_type', 'document_number'], 'unique_client_document');
            $table->index(['company_id', 'status']);
            $table->index(['document_type', 'document_number']);
            $table->index(['business_name']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};