<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_movements', function (Blueprint $t) {
            $t->bigIncrements('id');

            // llaves existentes en tu esquema
            $t->foreignId('company_id')->constrained('companies')->cascadeOnDelete(); // tenant
            $t->foreignId('product_id')->constrained('products')->restrictOnDelete(); // no borrar historial

            // tipos mínimos (MVP)
            $t->enum('type', ['OPENING','IN','OUT','TRANSFER','ADJUST']);

            // almacenes implicados (según type)
            $t->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->restrictOnDelete();
            $t->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->restrictOnDelete();

            // cantidad SIEMPRE positiva (la lógica suma/resta la decide type)
            $t->decimal('qty', 12, 4);

            // metadatos útiles
            $t->string('reason', 64)->nullable();  // "purchase","sale","return","count","waste", etc.
            $t->string('ref_type', 64)->nullable(); // p.ej. "INV","PO"
            $t->string('ref_id', 64)->nullable();   // número/uuid externo
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // ya existe

            // idempotencia por empresa (evita duplicados en reintentos)
            $t->string('idempotency_key', 64)->nullable();
            $t->unique(['company_id','idempotency_key'], 'uniq_company_idem');

            // fecha efectiva del movimiento
            $t->timestamp('movement_date')->useCurrent();

            $t->timestamps();

            // índices típicos de kardex
            $t->index(['company_id','movement_date'], 'idx_company_date');
            $t->index(['product_id','movement_date'], 'idx_product_date');
            $t->index(['from_warehouse_id','movement_date'], 'idx_from_date');
            $t->index(['to_warehouse_id','movement_date'], 'idx_to_date');
            $t->index(['type'], 'idx_type');
        });

        // Reglas de coherencia (si tu motor soporta CHECK)
        try {
            DB::statement("ALTER TABLE inventory_movements
                ADD CONSTRAINT chk_mov_qty_pos CHECK (qty > 0)");

            DB::statement("ALTER TABLE inventory_movements
                ADD CONSTRAINT chk_mov_from_to_diff CHECK (
                    from_warehouse_id IS NULL
                    OR to_warehouse_id IS NULL
                    OR from_warehouse_id <> to_warehouse_id
                )");

            // endpoints válidos por tipo:
            // OPENING/IN:   from NULL, to NOT NULL
            // OUT:          from NOT NULL, to NULL
            // TRANSFER:     from NOT NULL, to NOT NULL
            // ADJUST:       exactamente uno (XOR)
            DB::statement("ALTER TABLE inventory_movements
                ADD CONSTRAINT chk_mov_type_endpoints CHECK (
                    (type IN ('OPENING','IN')  AND from_warehouse_id IS NULL AND to_warehouse_id IS NOT NULL) OR
                    (type = 'OUT'              AND from_warehouse_id IS NOT NULL AND to_warehouse_id IS NULL) OR
                    (type = 'TRANSFER'         AND from_warehouse_id IS NOT NULL AND to_warehouse_id IS NOT NULL) OR
                    (type = 'ADJUST'           AND ((from_warehouse_id IS NULL) <> (to_warehouse_id IS NULL)))
                )");
        } catch (\Throwable $e) { /* ignorar si no soporta CHECK */ }
    }

    public function down(): void {
        foreach (['chk_mov_qty_pos','chk_mov_from_to_diff','chk_mov_type_endpoints'] as $c) {
            try { DB::statement("ALTER TABLE inventory_movements DROP CONSTRAINT $c"); } catch (\Throwable $e) {}
        }
        Schema::dropIfExists('inventory_movements');
    }
};
