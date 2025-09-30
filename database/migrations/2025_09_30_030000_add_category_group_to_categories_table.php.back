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
            // Campo para identificar si es una categoría principal o no
            $table->boolean('is_main_category')->default(false)->after('show_on_web')->comment('Indica si es una categoría principal contenedora');
            
            // Campo para relacionar categorías con su categoría principal
            $table->unsignedBigInteger('main_category_id')->nullable()->after('is_main_category')->comment('ID de la categoría principal a la que pertenece');
            
            // Índice para optimizar consultas
            $table->index(['is_main_category']);
            $table->index(['main_category_id']);
            
            // Clave foránea
            $table->foreign('main_category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['main_category_id']);
            $table->dropIndex(['is_main_category']);
            $table->dropIndex(['main_category_id']);
            $table->dropColumn(['is_main_category', 'main_category_id']);
        });
    }
};