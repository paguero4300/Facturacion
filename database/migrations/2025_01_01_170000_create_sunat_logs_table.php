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
        Schema::create('sunat_logs', function (Blueprint $table) {
            $table->id();
            
            // Related entities
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('cascade');
            
            // Document reference
            $table->string('document_type', 2)->comment('Tipo de documento');
            $table->string('series', 4)->comment('Serie');
            $table->string('number', 8)->comment('Número');
            $table->string('full_number', 15)->comment('Número completo');
            
            // Operation type
            $table->enum('operation_type', [
                'send_document',    // Envío de comprobante
                'send_summary',     // Envío de resumen
                'send_voiding',     // Envío de comunicación de baja
                'query_status',     // Consulta estado
                'download_cdr'      // Descarga CDR
            ])->comment('Tipo de operación');
            
            // Request information
            $table->string('request_method', 10)->comment('Método HTTP');
            $table->text('request_url')->comment('URL del servicio');
            $table->text('request_headers')->nullable()->comment('Headers de la petición JSON');
            $table->longText('request_body')->nullable()->comment('Cuerpo de la petición');
            $table->timestamp('request_sent_at')->comment('Fecha envío petición');
            
            // Response information
            $table->integer('response_status_code')->nullable()->comment('Código estado HTTP');
            $table->text('response_headers')->nullable()->comment('Headers de la respuesta JSON');
            $table->longText('response_body')->nullable()->comment('Cuerpo de la respuesta');
            $table->timestamp('response_received_at')->nullable()->comment('Fecha recepción respuesta');
            
            // SUNAT specific response
            $table->string('sunat_response_code', 10)->nullable()->comment('Código respuesta SUNAT');
            $table->text('sunat_response_description')->nullable()->comment('Descripción respuesta SUNAT');
            $table->string('ticket_number', 50)->nullable()->comment('Número de ticket SUNAT');
            
            // Processing status
            $table->enum('status', [
                'pending',      // Pendiente
                'processing',   // Procesando
                'success',      // Exitoso
                'error',        // Error
                'timeout'       // Timeout
            ])->default('pending');
            
            // Error information
            $table->string('error_code', 20)->nullable()->comment('Código de error');
            $table->text('error_message')->nullable()->comment('Mensaje de error');
            $table->longText('error_trace')->nullable()->comment('Trace del error');
            
            // Timing information
            $table->integer('response_time_ms')->nullable()->comment('Tiempo respuesta en ms');
            $table->integer('retry_count')->default(0)->comment('Número de reintentos');
            $table->timestamp('next_retry_at')->nullable()->comment('Próximo reintento');
            
            // File references
            $table->string('xml_file_path', 500)->nullable()->comment('Ruta archivo XML enviado');
            $table->string('cdr_file_path', 500)->nullable()->comment('Ruta archivo CDR recibido');
            
            // Environment and configuration
            $table->enum('environment', ['beta', 'production'])->comment('Ambiente SUNAT');
            $table->string('service_version', 20)->nullable()->comment('Versión del servicio');
            
            // Additional context
            $table->json('additional_data')->nullable()->comment('Datos adicionales JSON');
            
            // Audit fields
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'created_at']);
            $table->index(['invoice_id']);
            $table->index(['document_type', 'series', 'number']);
            $table->index(['operation_type']);
            $table->index(['status']);
            $table->index(['sunat_response_code']);
            $table->index(['request_sent_at']);
            $table->index(['next_retry_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sunat_logs');
    }
};