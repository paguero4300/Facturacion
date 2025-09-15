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
        // Para MySQL, necesitamos modificar el ENUM para incluir '09'
        DB::statement("ALTER TABLE invoices MODIFY COLUMN document_type ENUM('01', '03', '07', '08', '09') COMMENT 'Tipo de comprobante'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el ENUM a su estado original
        DB::statement("ALTER TABLE invoices MODIFY COLUMN document_type ENUM('01', '03', '07', '08') COMMENT 'Tipo de comprobante'");
    }
};