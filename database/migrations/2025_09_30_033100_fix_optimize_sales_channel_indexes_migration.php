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
        // Check if the index already exists before creating it
        if (!Schema::hasIndex('invoices', 'idx_invoices_status_date')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(['status', 'issue_date'], 'idx_invoices_status_date');
            });
        }
        
        // Add any other indexes that might be missing
        if (!Schema::hasIndex('invoices', 'idx_invoices_company_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index('company_id', 'idx_invoices_company_id');
            });
        }
        
        if (!Schema::hasIndex('invoices', 'idx_invoices_client_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index('client_id', 'idx_invoices_client_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_status_date');
            $table->dropIndex('idx_invoices_company_id');
            $table->dropIndex('idx_invoices_client_id');
        });
    }
};