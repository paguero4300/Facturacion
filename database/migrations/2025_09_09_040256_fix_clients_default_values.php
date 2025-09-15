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
        Schema::table('clients', function (Blueprint $table) {
            // Asegurar que credit_limit y payment_days tengan valores por defecto
            $table->decimal('credit_limit', 12, 2)->default(0)->nullable(false)->change();
            $table->integer('payment_days')->default(0)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('credit_limit', 12, 2)->nullable()->change();
            $table->integer('payment_days')->nullable()->change();
        });
    }
};