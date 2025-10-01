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
            // Primero eliminar la clave foránea existente
            $table->dropForeign(['client_id']);

            // Modificar la columna para que sea nullable
            $table->foreignId('client_id')->nullable()->change();

            // Volver a crear la clave foránea con nullable
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Revertir: hacer la columna obligatoria nuevamente
            $table->dropForeign(['client_id']);
            $table->foreignId('client_id')->nullable(false)->change();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('restrict');
        });
    }
};
