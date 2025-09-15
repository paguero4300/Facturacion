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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            
            // Company relationship
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Category information
            $table->string('name', 100)->comment('Nombre de la categoría');
            $table->text('description')->nullable()->comment('Descripción de la categoría');
            $table->string('color', 7)->nullable()->comment('Color hexadecimal para la categoría');
            $table->string('icon', 50)->nullable()->comment('Icono de la categoría');
            
            // Status
            $table->boolean('status')->default(true)->comment('Estado activo/inactivo');
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes and constraints
            $table->unique(['company_id', 'name'], 'unique_category_name');
            $table->index(['company_id', 'status']);
            $table->index(['name']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};