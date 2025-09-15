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
        // No necesitamos modificar la tabla invoices ya que el campo document_type
        // ya acepta strings, solo necesitamos agregar el seeder para la serie NV
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay cambios que revertir
    }
};