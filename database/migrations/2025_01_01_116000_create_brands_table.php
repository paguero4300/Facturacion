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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            
            // Company relationship
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Brand information
            $table->string('name', 100)->comment('Nombre de la marca');
            $table->text('description')->nullable()->comment('Descripción de la marca');
            $table->string('logo_url', 255)->nullable()->comment('URL del logo de la marca');
            $table->string('website', 255)->nullable()->comment('Sitio web de la marca');
            
            // Status
            $table->boolean('status')->default(true)->comment('Estado activo/inactivo');
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes and constraints
            $table->unique(['company_id', 'name'], 'unique_brand_name');
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
        Schema::dropIfExists('brands');
    }
};