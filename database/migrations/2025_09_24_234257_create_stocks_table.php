<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stocks', function (Blueprint $t) {
            // saldo on-hand por empresa + producto + almacén
            $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); // multi-empresa
            $t->foreignId('product_id')->constrained('products')->restrictOnDelete(); // no perder kardex
            $t->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();

            $t->decimal('qty', 12, 4)->default(0);      // existencia actual
            $t->decimal('min_qty', 12, 4)->nullable();  // alerta bajo stock
            $t->timestamps();

            // PK compuesta por tenant
            $t->primary(['company_id','product_id','warehouse_id'], 'pk_company_product_warehouse');

            // búsquedas comunes
            $t->index(['warehouse_id'], 'idx_wh');
            $t->index(['product_id'], 'idx_prod');
        });

        // No negativos (si tu motor soporta CHECK)
        try {
            DB::statement("ALTER TABLE stocks ADD CONSTRAINT chk_stocks_qty_nonneg CHECK (qty >= 0)");
        } catch (\Throwable $e) { /* ignorar si no soporta CHECK */ }
    }

    public function down(): void {
        try { DB::statement("ALTER TABLE stocks DROP CONSTRAINT chk_stocks_qty_nonneg"); } catch (\Throwable $e) {}
        Schema::dropIfExists('stocks');
    }
};
