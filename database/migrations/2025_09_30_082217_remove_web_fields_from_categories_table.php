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
        Schema::table('categories', function (Blueprint $table) {
            // Eliminar índices existentes
            if (Schema::hasColumn('categories', 'web_order')) {
                $table->dropIndex('categories_company_id_web_order_index');
            }
            if (Schema::hasColumn('categories', 'show_on_web')) {
                $table->dropIndex('categories_company_id_show_on_web_index');
            }
            if (Schema::hasColumn('categories', 'main_category_id')) {
                $table->dropIndex('categories_main_category_id_index');
            }

            // Eliminar columnas si existen
            $columnsToRemove = [];
            if (Schema::hasColumn('categories', 'updated_by')) {
                $columnsToRemove[] = 'updated_by';
            }
            if (Schema::hasColumn('categories', 'main_category_id')) {
                $columnsToRemove[] = 'main_category_id';
            }
            if (Schema::hasColumn('categories', 'is_main_category')) {
                $columnsToRemove[] = 'is_main_category';
            }
            if (Schema::hasColumn('categories', 'web_group')) {
                $columnsToRemove[] = 'web_group';
            }
            if (Schema::hasColumn('categories', 'web_order')) {
                $columnsToRemove[] = 'web_order';
            }
            if (Schema::hasColumn('categories', 'show_on_web')) {
                $columnsToRemove[] = 'show_on_web';
            }

            if (!empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Restaurar campos en caso de rollback
            $table->boolean('show_on_web')->default(true)->after('status');
            $table->integer('web_order')->default(0)->after('show_on_web');
            $table->string('web_group', 50)->nullable()->after('web_order');
            $table->boolean('is_main_category')->default(false)->after('web_group');
            $table->foreignId('main_category_id')->nullable()->after('is_main_category')->constrained('categories')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();

            // Recrear índices
            $table->index('show_on_web');
            $table->index('web_order');
            $table->index('web_group');
            $table->index('is_main_category');
            $table->index('main_category_id');
            $table->index(['company_id', 'show_on_web', 'web_order']);
            $table->index(['company_id', 'web_group']);
            $table->index(['company_id', 'is_main_category']);
        });
    }
};
