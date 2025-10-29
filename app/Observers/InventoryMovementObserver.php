<?php

namespace App\Observers;

use App\Models\InventoryMovement;
use App\Models\Stock;

class InventoryMovementObserver
{
    /**
     * Handle the InventoryMovement "created" event.
     */
    public function created(InventoryMovement $movement): void
    {
        $this->updateStock($movement);
    }

    /**
     * Handle the InventoryMovement "updated" event.
     */
    public function updated(InventoryMovement $movement): void
    {
        // Si cambió la cantidad, tipo o almacenes, recalcular stock
        if ($movement->isDirty(['qty', 'type', 'from_warehouse_id', 'to_warehouse_id'])) {
            // Revertir el movimiento anterior
            $this->revertStock($movement);
            // Aplicar el nuevo movimiento
            $this->updateStock($movement);
        }
    }

    /**
     * Handle the InventoryMovement "deleted" event.
     */
    public function deleted(InventoryMovement $movement): void
    {
        $this->revertStock($movement);
    }

    /**
     * Actualizar stock basado en el movimiento de inventario
     */
    private function updateStock(InventoryMovement $movement): void
    {
        switch ($movement->type) {
            case 'OPENING':
            case 'IN':
                // Entrada: agregar al almacén destino
                if ($movement->to_warehouse_id) {
                    $this->adjustStock(
                        $movement->product_id,
                        $movement->to_warehouse_id,
                        $movement->qty
                    );
                }
                break;

            case 'OUT':
                // Salida: restar del almacén origen
                if ($movement->from_warehouse_id) {
                    $this->adjustStock(
                        $movement->product_id,
                        $movement->from_warehouse_id,
                        -$movement->qty
                    );
                }
                break;

            case 'TRANSFER':
                // Transferencia: restar del origen y agregar al destino
                if ($movement->from_warehouse_id) {
                    $this->adjustStock(
                        $movement->product_id,
                        $movement->from_warehouse_id,
                        -$movement->qty
                    );
                }
                if ($movement->to_warehouse_id) {
                    $this->adjustStock(
                        $movement->product_id,
                        $movement->to_warehouse_id,
                        $movement->qty
                    );
                }
                break;

            case 'ADJUST':
                // Ajuste: sumar o restar cantidad del stock actual
                if ($movement->to_warehouse_id) {
                    // Ajuste positivo: agregar al almacén destino
                    $this->adjustStock(
                        $movement->product_id,
                        $movement->to_warehouse_id,
                        $movement->qty
                    );
                } elseif ($movement->from_warehouse_id) {
                    // Ajuste negativo: restar del almacén origen
                    $this->adjustStock(
                        $movement->product_id,
                        $movement->from_warehouse_id,
                        -$movement->qty
                    );
                }
                break;
        }
    }

    /**
     * Revertir stock basado en el movimiento de inventario
     */
    private function revertStock(InventoryMovement $movement): void
    {
        $originalMovement = $movement->getOriginal();
        
        switch ($originalMovement['type']) {
            case 'OPENING':
            case 'IN':
                // Revertir entrada: restar del almacén destino
                if ($originalMovement['to_warehouse_id']) {
                    $this->adjustStock(
                        $movement->product_id,
                        $originalMovement['to_warehouse_id'],
                        -$originalMovement['qty']
                    );
                }
                break;

            case 'OUT':
                // Revertir salida: agregar al almacén origen
                if ($originalMovement['from_warehouse_id']) {
                    $this->adjustStock(
                        $movement->product_id,
                        $originalMovement['from_warehouse_id'],
                        $originalMovement['qty']
                    );
                }
                break;

            case 'TRANSFER':
                // Revertir transferencia: agregar al origen y restar del destino
                if ($originalMovement['from_warehouse_id']) {
                    $this->adjustStock(
                        $movement->product_id,
                        $originalMovement['from_warehouse_id'],
                        $originalMovement['qty']
                    );
                }
                if ($originalMovement['to_warehouse_id']) {
                    $this->adjustStock(
                        $movement->product_id,
                        $originalMovement['to_warehouse_id'],
                        -$originalMovement['qty']
                    );
                }
                break;

            case 'ADJUST':
                // Para ajustes, revertir el cambio anterior
                if ($originalMovement['to_warehouse_id']) {
                    // Revertir ajuste positivo: restar la cantidad
                    $this->adjustStock(
                        $movement->product_id,
                        $originalMovement['to_warehouse_id'],
                        -$originalMovement['qty']
                    );
                } elseif ($originalMovement['from_warehouse_id']) {
                    // Revertir ajuste negativo: agregar la cantidad
                    $this->adjustStock(
                        $movement->product_id,
                        $originalMovement['from_warehouse_id'],
                        $originalMovement['qty']
                    );
                }
                break;
        }
    }

    /**
     * Ajustar stock sumando/restando cantidad
     */
    private function adjustStock(int $productId, int $warehouseId, float $qtyChange): void
    {
        // Obtener el company_id del warehouse
        $warehouse = \App\Models\Warehouse::find($warehouseId);
        if (!$warehouse) {
            throw new \Exception("Warehouse with ID {$warehouseId} not found");
        }

        $stock = Stock::firstOrCreate(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'company_id' => $warehouse->company_id,
                'qty' => 0,
                'min_qty' => 0,
            ]
        );

        $stock->qty = max(0, $stock->qty + $qtyChange);
        $stock->save();

        // Sincronizar current_stock del producto con la suma de todos los almacenes
        $this->syncProductCurrentStock($productId);
    }

    /**
     * Establecer stock a cantidad exacta
     */
    private function setStock(int $productId, int $warehouseId, float $qty): void
    {
        // Obtener el company_id del warehouse
        $warehouse = \App\Models\Warehouse::find($warehouseId);
        if (!$warehouse) {
            throw new \Exception("Warehouse with ID {$warehouseId} not found");
        }

        $stock = Stock::firstOrCreate(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'company_id' => $warehouse->company_id,
                'qty' => 0,
                'min_qty' => 0,
            ]
        );

        $stock->qty = max(0, $qty);
        $stock->save();

        // Sincronizar current_stock del producto con la suma de todos los almacenes
        $this->syncProductCurrentStock($productId);
    }

    /**
     * Sincronizar current_stock del producto con la suma de stock de todos los almacenes
     */
    private function syncProductCurrentStock(int $productId): void
    {
        $totalStock = Stock::where('product_id', $productId)->sum('qty');
        
        \App\Models\Product::where('id', $productId)
            ->update(['current_stock' => $totalStock]);
    }
}