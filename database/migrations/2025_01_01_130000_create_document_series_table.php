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
        Schema::create('document_series', function (Blueprint $table) {
            $table->id();
            
            // Company relationship
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Document type - Catálogo 01 SUNAT
            $table->enum('document_type', [
                '01', // Factura
                '03', // Boleta de Venta
                '07', // Nota de Crédito
                '08', // Nota de Débito
                '09', // Guía de Remisión del Remitente
                '12', // Ticket de Máquina Registradora
                '13', // Documento emitido por bancos
                '18', // Documentos de las iglesias y entidades religiosas
                '31', // Guía de Remisión del Transportista
                '40', // Comprobante de Percepción
                '41', // Comprobante de Retención
            ])->comment('Tipo de comprobante según catálogo 01 SUNAT');
            
            // Series configuration
            $table->string('series', 4)->comment('Serie del documento (ej: F001, B001)');
            $table->string('description', 200)->comment('Descripción de la serie');
            
            // Numbering control
            $table->unsignedBigInteger('current_number')->default(0)->comment('Número correlativo actual');
            $table->unsignedBigInteger('initial_number')->default(1)->comment('Número inicial');
            $table->unsignedBigInteger('final_number')->default(99999999)->comment('Número final');
            
            // Series configuration
            $table->boolean('is_default')->default(false)->comment('Serie por defecto para este tipo de documento');
            $table->boolean('is_electronic')->default(true)->comment('Serie electrónica (SUNAT)');
            $table->boolean('is_contingency')->default(false)->comment('Serie de contingencia');
            
            // Point of sale configuration
            $table->string('pos_code', 10)->nullable()->comment('Código punto de venta');
            $table->string('pos_description', 200)->nullable()->comment('Descripción punto de venta');
            
            // Validation rules
            $table->json('validation_rules')->nullable()->comment('Reglas de validación específicas JSON');
            
            // Status and configuration
            $table->enum('status', ['active', 'inactive', 'suspended', 'exhausted'])->default('active');
            $table->date('valid_from')->nullable()->comment('Válido desde');
            $table->date('valid_until')->nullable()->comment('Válido hasta');
            
            // Authorization information (for some document types)
            $table->string('authorization_number', 50)->nullable()->comment('Número de autorización');
            $table->date('authorization_date')->nullable()->comment('Fecha de autorización');
            
            // Usage statistics
            $table->unsignedBigInteger('documents_issued')->default(0)->comment('Documentos emitidos');
            $table->timestamp('last_used_at')->nullable()->comment('Última vez usado');
            
            // Additional configuration
            $table->text('notes')->nullable()->comment('Observaciones');
            $table->json('additional_config')->nullable()->comment('Configuración adicional JSON');
            
            // Audit fields
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes and constraints
            $table->unique(['company_id', 'document_type', 'series'], 'unique_company_document_series');
            $table->unique(['company_id', 'document_type', 'is_default'], 'unique_default_series')
                ->where('is_default', true);
            
            $table->index(['company_id', 'document_type', 'status']);
            $table->index(['status']);
            $table->index(['is_electronic']);
            $table->index(['created_at']);
        });
        
        // Add check constraints after table creation
        DB::statement('ALTER TABLE document_series ADD CONSTRAINT check_series_format 
            CHECK (
                (document_type = "01" AND series REGEXP "^F[0-9]{3}$") OR
                (document_type = "03" AND series REGEXP "^B[0-9]{3}$") OR
                (document_type = "07" AND series REGEXP "^[FB]C[0-9]{2}$") OR
                (document_type = "08" AND series REGEXP "^[FB]D[0-9]{2}$") OR
                (document_type NOT IN ("01", "03", "07", "08"))
            )'
        );
        
        DB::statement('ALTER TABLE document_series ADD CONSTRAINT check_number_range 
            CHECK (current_number >= initial_number AND current_number <= final_number)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_series');
    }
};