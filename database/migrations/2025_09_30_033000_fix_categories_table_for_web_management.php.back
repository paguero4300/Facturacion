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
            // Añadir columnas si no existen
            if (!Schema::hasColumn('categories', 'show_on_web')) {
                $table->boolean('show_on_web')->default(true)->after('status');
            }
            
            if (!Schema::hasColumn('categories', 'web_order')) {
                $table->integer('web_order')->default(0)->after('show_on_web');
            }
            
            if (!Schema::hasColumn('categories', 'web_group')) {
                $table->string('web_group')->default('principales')->after('web_order');
            }
            
            if (!Schema::hasColumn('categories', 'is_main_category')) {
                $table->boolean('is_main_category')->default(false)->after('web_group');
            }
            
            if (!Schema::hasColumn('categories', 'main_category_id')) {
                $table->unsignedBigInteger('main_category_id')->nullable()->after('is_main_category');
                $table->foreign('main_category_id')->references('id')->on('categories')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('categories', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('main_category_id');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('categories', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }
        });
        
        // Crear índices para mejorar el rendimiento
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasIndex('categories', 'categories_web_group_index')) {
                $table->index('web_group', 'categories_web_group_index');
            }
            
            if (!Schema::hasIndex('categories', 'categories_web_order_index')) {
                $table->index('web_order', 'categories_web_order_index');
            }
            
            if (!Schema::hasIndex('categories', 'categories_show_on_web_index')) {
                $table->index('show_on_web', 'categories_show_on_web_index');
            }
            
            if (!Schema::hasIndex('categories', 'categories_is_main_category_index')) {
                $table->index('is_main_category', 'categories_is_main_category_index');
            }
            
            if (!Schema::hasIndex('categories', 'categories_main_category_id_index')) {
                $table->index('main_category_id', 'categories_main_category_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex('categories_web_group_index');
            $table->dropIndex('categories_web_order_index');
            $table->dropIndex('categories_show_on_web_index');
            $table->dropIndex('categories_is_main_category_index');
            $table->dropIndex('categories_main_category_id_index');
            
            // Eliminar columnas
            if (Schema::hasColumn('categories', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            
            if (Schema::hasColumn('categories', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            
            if (Schema::hasColumn('categories', 'main_category_id')) {
                $table->dropForeign(['main_category_id']);
                $table->dropColumn('main_category_id');
            }
            
            if (Schema::hasColumn('categories', 'is_main_category')) {
                $table->dropColumn('is_main_category');
            }
            
            if (Schema::hasColumn('categories', 'web_group')) {
                $table->dropColumn('web_group');
            }
            
            if (Schema::hasColumn('categories', 'web_order')) {
                $table->dropColumn('web_order');
            }
            
            if (Schema::hasColumn('categories', 'show_on_web')) {
                $table->dropColumn('show_on_web');
            }
        });
    }
};