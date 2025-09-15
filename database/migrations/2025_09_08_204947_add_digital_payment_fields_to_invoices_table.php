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
            $table->string('payment_reference', 100)->nullable()->after('payment_method')->comment('Número de operación para pagos digitales (Yape, Plin, etc.)');
            $table->string('payment_phone', 20)->nullable()->after('payment_reference')->comment('Teléfono asociado al pago digital');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'payment_phone']);
        });
    }
};