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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            
            // Emisor identification
            $table->char('ruc', 11)->unique()->comment('RUC - 11 characters as per SUNAT');
            $table->string('business_name', 500)->comment('Razón social');
            $table->string('commercial_name', 300)->nullable()->comment('Nombre comercial');
            
            // Address information
            $table->string('address', 1000)->comment('Dirección fiscal');
            $table->string('district', 100)->comment('Distrito');
            $table->string('province', 100)->comment('Provincia');
            $table->string('department', 100)->comment('Departamento');
            $table->string('country_code', 2)->default('PE')->comment('Código de país ISO');
            $table->string('postal_code', 10)->nullable()->comment('Código postal');
            $table->string('ubigeo', 6)->nullable()->comment('Código UBIGEO SUNAT');
            
            // Contact information
            $table->string('phone', 50)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('website', 300)->nullable();
            
            // Tax information
            $table->enum('tax_regime', [
                'RER',      // Régimen Especial de Renta
                'GENERAL',  // Régimen General
                'MYPE'      // Micro y Pequeña Empresa
            ])->comment('Régimen tributario');
            
            // Digital certificate information for SUNAT
            $table->string('certificate_path', 500)->nullable()->comment('Ruta del certificado digital');
            $table->string('certificate_password', 500)->nullable()->comment('Password del certificado (encriptado)');
            
            // OSE (Operador de Servicios Electrónicos) information
            $table->string('ose_provider', 100)->nullable()->comment('Proveedor OSE');
            $table->string('ose_endpoint', 500)->nullable()->comment('Endpoint OSE');
            $table->string('ose_username', 200)->nullable()->comment('Usuario OSE');
            $table->string('ose_password', 500)->nullable()->comment('Password OSE (encriptado)');
            
            // Status and configuration
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('sunat_production')->default(false)->comment('Ambiente SUNAT: false=beta, true=producción');
            $table->json('additional_config')->nullable()->comment('Configuración adicional JSON');
            
            // Audit fields
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status']);
            $table->index(['tax_regime']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};