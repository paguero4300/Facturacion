<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índices para optimizar las consultas del SalesChannelResource

        // Tabla invoices - Índices principales para filtros comunes
        Schema::table('invoices', function (Blueprint $table) {
            // Índice compuesto para el filtro de estados más común
            $table->index(['status', 'issue_date'], 'idx_invoices_status_date');

            // Índice para filtros de fecha
            $table->index('issue_date', 'idx_invoices_issue_date');

            // Índice para filtros de tipo de documento
            $table->index('document_type', 'idx_invoices_document_type');

            // Índice para filtros de método de pago
            $table->index('payment_method', 'idx_invoices_payment_method');

            // Índice para filtros de SUNAT status
            $table->index('sunat_status', 'idx_invoices_sunat_status');

            // Índice compuesto para búsqueda por cliente
            $table->index(['client_id', 'status'], 'idx_invoices_client_status');

            // Índice para navegación badge (status + issue_date del mes actual)
            $table->index(['status', 'issue_date'], 'idx_invoices_badge_query');
        });

        // Tabla invoice_details - Índices para mejorar rendimiento de joins
        Schema::table('invoice_details', function (Blueprint $table) {
            // Índice para el join principal con invoices
            $table->index('invoice_id', 'idx_invoice_details_invoice_id');

            // Índice para filtros por producto
            $table->index('product_id', 'idx_invoice_details_product_id');

            // Índice compuesto para optimizar consultas con joins
            $table->index(['invoice_id', 'product_id'], 'idx_invoice_details_invoice_product');

            // Índice para filtros de monto
            $table->index('line_total', 'idx_invoice_details_line_total');

            // Índice para tipo fiscal
            $table->index('tax_type', 'idx_invoice_details_tax_type');
        });

        // Tabla products - Índices para búsquedas y joins
        Schema::table('products', function (Blueprint $table) {
            // Índice para búsqueda de productos
            $table->index(['name', 'code'], 'idx_products_name_code');

            // Índice para categoría
            $table->index('category_id', 'idx_products_category_id');

            // Índice para costo (usado en cálculos de margen)
            $table->index('cost_price', 'idx_products_cost_price');

            // Índice compuesto para productos con costo
            $table->index(['category_id', 'cost_price'], 'idx_products_category_cost');
        });

        // Tabla categories - Índices para joins
        Schema::table('categories', function (Blueprint $table) {
            // Índice para nombre de categoría
            $table->index('name', 'idx_categories_name');
        });

        // Crear índices FULLTEXT para búsquedas avanzadas (MySQL/MariaDB)
        \DB::statement('ALTER TABLE products ADD FULLTEXT idx_products_fulltext (name, code, description)');
        \DB::statement('ALTER TABLE invoices ADD FULLTEXT idx_invoices_fulltext (series, number)');

        // Logging de la creación de índices
        \Log::info('SalesChannelResource: Database indexes optimized', [
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true)
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices FULLTEXT
        try {
            DB::statement('DROP INDEX IF EXISTS idx_products_fulltext ON products');
            DB::statement('DROP INDEX IF EXISTS idx_invoices_fulltext ON invoices');
        } catch (\Exception $e) {
            Log::warning('SalesChannelResource: Error dropping FULLTEXT indexes', [
                'error' => $e->getMessage()
            ]);
        }

        // Eliminar índices de tabla invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_invoices_status_date');
            $table->dropIndexIfExists('idx_invoices_issue_date');
            $table->dropIndexIfExists('idx_invoices_document_type');
            $table->dropIndexIfExists('idx_invoices_payment_method');
            $table->dropIndexIfExists('idx_invoices_sunat_status');
            $table->dropIndexIfExists('idx_invoices_client_status');
            $table->dropIndexIfExists('idx_invoices_badge_query');
        });

        // Eliminar índices de tabla invoice_details
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_invoice_details_invoice_id');
            $table->dropIndexIfExists('idx_invoice_details_product_id');
            $table->dropIndexIfExists('idx_invoice_details_invoice_product');
            $table->dropIndexIfExists('idx_invoice_details_line_total');
            $table->dropIndexIfExists('idx_invoice_details_tax_type');
        });

        // Eliminar índices de tabla products
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_products_name_code');
            $table->dropIndexIfExists('idx_products_category_id');
            $table->dropIndexIfExists('idx_products_cost_price');
            $table->dropIndexIfExists('idx_products_category_cost');
        });

        // Eliminar índices de tabla categories
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_categories_name');
        });

        // Logging de la eliminación de índices
        Log::info('SalesChannelResource: Database indexes dropped', [
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true)
        ]);
    }
};
