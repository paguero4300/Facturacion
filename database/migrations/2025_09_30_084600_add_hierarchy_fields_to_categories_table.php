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
            // Campo para jerarquía (categoría padre)
            $table->foreignId('parent_id')->nullable()->after('company_id')->constrained('categories')->onDelete('cascade');
            
            // Slug para URLs amigables
            $table->string('slug', 150)->nullable()->after('name');
            
            // Orden de aparición en el menú
            $table->integer('order')->default(0)->after('icon')->comment('Orden de aparición en el menú');
            
            // Índices para optimizar consultas
            $table->index(['parent_id', 'status', 'order']);
            $table->index(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id', 'status', 'order']);
            $table->dropIndex(['slug']);
            $table->dropColumn(['parent_id', 'slug', 'order']);
        });
    }
};
