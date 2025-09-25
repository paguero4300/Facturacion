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
        // Primero, eliminar las restricciones de clave foránea
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['warehouse_id']);
        });
        
        // Luego, eliminar la clave primaria y agregar id
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropPrimary('pk_company_product_warehouse');
            $table->id()->first();
            $table->unique(['company_id', 'product_id', 'warehouse_id'], 'unique_company_product_warehouse');
        });
        
        // Finalmente, recrear las restricciones de clave foránea
        Schema::table('stocks', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primero, eliminar las restricciones de clave foránea
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['warehouse_id']);
        });
        
        // Luego, eliminar el índice único y la columna id
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique('unique_company_product_warehouse');
            $table->dropColumn('id');
            $table->primary(['company_id', 'product_id', 'warehouse_id'], 'pk_company_product_warehouse');
        });
        
        // Finalmente, recrear las restricciones de clave foránea originales
        Schema::table('stocks', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
        });
    }
};
