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
            // Delivery scheduling fields
            $table->date('delivery_date')->nullable()->comment('Fecha programada de entrega');
            $table->enum('delivery_time_slot', ['morning', 'afternoon', 'evening'])
                  ->nullable()
                  ->comment('Horario de entrega: morning (9-12), afternoon (14-17), evening (18-20)');
            $table->text('delivery_notes')->nullable()->comment('Notas especiales de entrega');
            $table->enum('delivery_status', ['programado', 'en_ruta', 'entregado', 'reprogramado'])
                  ->nullable()
                  ->comment('Estado de la entrega programada');
            $table->timestamp('delivery_confirmed_at')->nullable()->comment('Fecha y hora de confirmación de entrega');
            
            // Indexes for delivery fields
            $table->index(['delivery_date']);
            $table->index(['delivery_time_slot']);
            $table->index(['delivery_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['delivery_date']);
            $table->dropIndex(['delivery_time_slot']);
            $table->dropIndex(['delivery_status']);
            
            $table->dropColumn([
                'delivery_date',
                'delivery_time_slot', 
                'delivery_notes',
                'delivery_status',
                'delivery_confirmed_at'
            ]);
        });
    }
};
