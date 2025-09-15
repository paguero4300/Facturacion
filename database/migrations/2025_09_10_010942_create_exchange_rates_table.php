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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->comment('Fecha del tipo de cambio');
            $table->decimal('buy_rate', 10, 6)->comment('Tipo de cambio compra');
            $table->decimal('sell_rate', 10, 6)->comment('Tipo de cambio venta');
            $table->string('source', 50)->default('factiliza')->comment('Fuente del tipo de cambio');
            $table->json('raw_data')->nullable()->comment('Datos originales de la API');
            $table->timestamp('fetched_at')->comment('Fecha y hora de consulta');
            $table->timestamps();
            
            // Índices
            $table->index(['date', 'source']);
            $table->index('fetched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};