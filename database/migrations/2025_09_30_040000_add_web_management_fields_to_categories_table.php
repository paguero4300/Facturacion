<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Migración consolidada que agrega todos los campos necesarios para la gestión web de categorías:
     * - Campos de visualización web (show_on_web, web_order, web_group)
     * - Campos de jerarquía (is_main_category, main_category_id)
     * - Campo de auditoría (updated_by)
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Verificar y agregar campos de gestión web
            if (!Schema::hasColumn('categories', 'show_on_web')) {
                $table->boolean('show_on_web')
                    ->default(true)
                    ->after('status')
                    ->comment('Mostrar categoría en la web');
            }

            if (!Schema::hasColumn('categories', 'web_order')) {
                $table->integer('web_order')
                    ->default(0)
                    ->after('show_on_web')
                    ->comment('Orden de visualización en la web');
            }

            if (!Schema::hasColumn('categories', 'web_group')) {
                $table->string('web_group', 50)
                    ->nullable()
                    ->after('web_order')
                    ->comment('Grupo de categorías (principales, secundarias, especiales)');
            }

            // Verificar y agregar campos de jerarquía
            if (!Schema::hasColumn('categories', 'is_main_category')) {
                $table->boolean('is_main_category')
                    ->default(false)
                    ->after('web_group')
                    ->comment('Indica si es una categoría principal contenedora');
            }

            if (!Schema::hasColumn('categories', 'main_category_id')) {
                $table->foreignId('main_category_id')
                    ->nullable()
                    ->after('is_main_category')
                    ->comment('ID de la categoría principal a la que pertenece')
                    ->constrained('categories')
                    ->nullOnDelete();
            }

            // Verificar y agregar campo de auditoría
            if (!Schema::hasColumn('categories', 'updated_by')) {
                $table->foreignId('updated_by')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        // Crear índices para optimizar consultas (con manejo de errores para duplicados)
        try {
            Schema::table('categories', function (Blueprint $table) {
                // Índices simples
                $table->index('show_on_web', 'categories_show_on_web_index');
            });
        } catch (\Exception $e) {
            // Índice ya existe, ignorar
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('web_order', 'categories_web_order_index');
            });
        } catch (\Exception $e) {
            // Índice ya existe, ignorar
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('web_group', 'categories_web_group_index');
            });
        } catch (\Exception $e) {
            // Índice ya existe, ignorar
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('is_main_category', 'categories_is_main_category_index');
            });
        } catch (\Exception $e) {
            // Índice ya existe, ignorar
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('main_category_id', 'categories_main_category_id_index');
            });
        } catch (\Exception $e) {
            // Índice ya existe, ignorar
        }

        // Índices compuestos para consultas frecuentes
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->index(['company_id', 'show_on_web', 'web_order'], 'categories_company_id_show_on_web_web_order_index');
            });
        } catch (\Exception $e) {
            // Índice ya existe, ignorar
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->index(['company_id', 'web_group'], 'categories_company_id_web_group_index');
            });
        } catch (\Exception $e) {
            // Índice ya existe, ignorar
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->index(['company_id', 'is_main_category'], 'categories_company_id_is_main_category_index');
            });
        } catch (\Exception $e) {
            // Índice ya existe, ignorar
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Eliminar índices compuestos
            $this->dropIndexIfExists($table, 'categories_company_id_show_on_web_web_order_index');
            $this->dropIndexIfExists($table, 'categories_company_id_web_group_index');
            $this->dropIndexIfExists($table, 'categories_company_id_is_main_category_index');

            // Eliminar índices simples
            $this->dropIndexIfExists($table, 'categories_show_on_web_index');
            $this->dropIndexIfExists($table, 'categories_web_order_index');
            $this->dropIndexIfExists($table, 'categories_web_group_index');
            $this->dropIndexIfExists($table, 'categories_is_main_category_index');
            $this->dropIndexIfExists($table, 'categories_main_category_id_index');

            // Eliminar foreign keys y columnas
            if (Schema::hasColumn('categories', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
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

    /**
     * Verifica si un índice existe en la tabla
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $doctrineSchemaManager = $connection->getDoctrineSchemaManager();
        $doctrineTable = $doctrineSchemaManager->introspectTable($table);

        return $doctrineTable->hasIndex($indexName);
    }

    /**
     * Elimina un índice si existe
     */
    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        if ($this->indexExists('categories', $indexName)) {
            $table->dropIndex($indexName);
        }
    }
};