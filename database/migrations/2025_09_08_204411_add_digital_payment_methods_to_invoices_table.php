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
            // Modificar el enum para agregar yape y plin
            DB::statement("ALTER TABLE invoices MODIFY COLUMN payment_method ENUM('cash','card','transfer','check','credit','deposit','yape','plin','other') NOT NULL DEFAULT 'cash'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Revertir al enum original
            DB::statement("ALTER TABLE invoices MODIFY COLUMN payment_method ENUM('cash','card','transfer','check','credit','deposit','other') NOT NULL DEFAULT 'cash'");
        });
    }
};