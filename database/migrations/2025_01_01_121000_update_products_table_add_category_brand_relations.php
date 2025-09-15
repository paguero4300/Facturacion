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
        Schema::table('products', function (Blueprint $table) {
            // Add foreign key columns
            $table->foreignId('category_id')->nullable()->after('track_inventory')->constrained()->onDelete('set null');
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
            
            // Add audit field
            $table->foreignId('created_by')->nullable()->after('additional_attributes')->constrained('users')->onDelete('set null');
            
            // Add indexes for the new foreign keys
            $table->index(['category_id']);
            $table->index(['brand_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign key constraints and columns
            $table->dropForeign(['category_id']);
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['created_by']);
            
            $table->dropColumn(['category_id', 'brand_id', 'created_by']);
        });
    }
};