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
        Schema::create('document_numbering_control', function (Blueprint $table) {
            $table->id();
            
            // Company and series relationship
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_series_id')->constrained()->onDelete('cascade');
            
            // Document identification
            $table->string('document_type', 2)->comment('Tipo de documento');
            $table->string('series', 4)->comment('Serie');
            
            // Current numbering state
            $table->unsignedBigInteger('last_number')->comment('Último número usado');
            $table->timestamp('last_used_at')->comment('Fecha último uso');
            $table->string('last_document_hash', 64)->nullable()->comment('Hash último documento');
            
            // Sequence control for atomic operations
            $table->unsignedBigInteger('sequence_version')->default(1)->comment('Versión secuencia (optimistic locking)');
            
            // Reserved numbers (for concurrent processing)
            $table->json('reserved_numbers')->nullable()->comment('Números reservados JSON');
            $table->timestamp('reserved_until')->nullable()->comment('Reserva válida hasta');
            
            // Validation control
            $table->boolean('sequence_integrity')->default(true)->comment('Integridad secuencial');
            $table->json('gaps_detected')->nullable()->comment('Saltos detectados JSON');
            $table->timestamp('last_integrity_check')->nullable()->comment('Última verificación integridad');
            
            // Emergency/contingency control
            $table->boolean('emergency_mode')->default(false)->comment('Modo emergencia activo');
            $table->string('emergency_reason', 500)->nullable()->comment('Motivo modo emergencia');
            $table->timestamp('emergency_started_at')->nullable()->comment('Inicio modo emergencia');
            
            // Statistics
            $table->unsignedInteger('documents_issued_today')->default(0)->comment('Documentos emitidos hoy');
            $table->unsignedInteger('documents_issued_month')->default(0)->comment('Documentos emitidos mes actual');
            $table->date('stats_date')->nullable()->comment('Fecha última actualización estadísticas');
            
            // Lock control for high concurrency
            $table->string('lock_token', 36)->nullable()->comment('Token bloqueo exclusivo');
            $table->timestamp('locked_until')->nullable()->comment('Bloqueado hasta');
            $table->string('locked_by_process', 100)->nullable()->comment('Proceso que bloqueó');
            
            // Audit fields
            $table->timestamps();
            
            // Indexes and constraints
            $table->unique(['company_id', 'document_series_id'], 'unique_company_series_control');
            $table->unique(['company_id', 'document_type', 'series'], 'unique_company_document_control');
            
            $table->index(['company_id', 'document_type']);
            $table->index(['last_used_at']);
            $table->index(['emergency_mode']);
            $table->index(['locked_until']);
            $table->index(['sequence_version']);
        });
        
        // Add check constraints after table creation
        DB::statement('ALTER TABLE document_numbering_control ADD CONSTRAINT check_last_number_positive 
            CHECK (last_number >= 0)'
        );
        
        DB::statement('ALTER TABLE document_numbering_control ADD CONSTRAINT check_sequence_version_positive 
            CHECK (sequence_version > 0)'
        );
        
        // Create a procedure for atomic number generation
        DB::unprepared('
            CREATE PROCEDURE GetNextDocumentNumber(
                IN p_company_id BIGINT UNSIGNED,
                IN p_series_id BIGINT UNSIGNED,
                OUT p_next_number BIGINT UNSIGNED,
                OUT p_success BOOLEAN
            )
            BEGIN
                DECLARE v_current_number BIGINT UNSIGNED DEFAULT 0;
                DECLARE v_max_number BIGINT UNSIGNED DEFAULT 0;
                DECLARE v_sequence_version BIGINT UNSIGNED DEFAULT 0;
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET p_success = FALSE;
                    SET p_next_number = 0;
                END;
                
                START TRANSACTION;
                
                -- Get current state with lock
                SELECT last_number, sequence_version
                INTO v_current_number, v_sequence_version
                FROM document_numbering_control 
                WHERE company_id = p_company_id AND document_series_id = p_series_id
                FOR UPDATE;
                
                -- Get max number from series
                SELECT final_number INTO v_max_number
                FROM document_series 
                WHERE id = p_series_id;
                
                -- Calculate next number
                SET p_next_number = v_current_number + 1;
                
                -- Check if we have available numbers
                IF p_next_number <= v_max_number THEN
                    -- Update control table
                    UPDATE document_numbering_control 
                    SET 
                        last_number = p_next_number,
                        last_used_at = NOW(),
                        sequence_version = sequence_version + 1,
                        documents_issued_today = documents_issued_today + 1,
                        documents_issued_month = documents_issued_month + 1
                    WHERE 
                        company_id = p_company_id 
                        AND document_series_id = p_series_id
                        AND sequence_version = v_sequence_version;
                    
                    -- Update series current number
                    UPDATE document_series 
                    SET 
                        current_number = p_next_number,
                        documents_issued = documents_issued + 1,
                        last_used_at = NOW()
                    WHERE id = p_series_id;
                    
                    SET p_success = TRUE;
                ELSE
                    SET p_success = FALSE;
                    SET p_next_number = 0;
                END IF;
                
                COMMIT;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS GetNextDocumentNumber');
        Schema::dropIfExists('document_numbering_control');
    }
};