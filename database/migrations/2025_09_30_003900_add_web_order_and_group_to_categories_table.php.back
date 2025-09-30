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
        Schema::table('categories', function (Blueprint $table) {
            // Campo para ordenamiento en la web
            $table->integer('web_order')->default(0)->after('status')->comment('Orden de visualización en la web');
            
            // Campo para agrupamiento en la web
            $table->string('web_group')->nullable()->after('web_order')->comment('Grupo de categorías para la web');
            
            // Campo para mostrar u ocultar en la web
            $table->boolean('show_on_web')->default(true)->after('web_group')->comment('Mostrar en la web');
            
            // Índices para optimizar consultas
            $table->index(['company_id', 'web_order']);
            $table->index(['company_id', 'web_group']);
            $table->index(['company_id', 'show_on_web']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'web_order']);
            $table->dropIndex(['company_id', 'web_group']);
            $table->dropIndex(['company_id', 'show_on_web']);
            
            $table->dropColumn(['web_order', 'web_group', 'show_on_web']);
        });
    }
};