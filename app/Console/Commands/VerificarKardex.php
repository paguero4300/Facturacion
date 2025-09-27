<?php

namespace App\Console\Commands;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Console\Command;

class VerificarKardex extends Command
{
    protected $signature = 'kardex:verificar {producto_id?}';
    protected $description = 'Verificar si el kardex tiene datos para mostrar';

    public function handle()
    {
        $this->info('🔍 VERIFICACIÓN DE KARDEX SENCILLO');
        $this->info('=====================================');
        
        // Estadísticas generales
        $totalMovimientos = InventoryMovement::count();
        $productosConMovimientos = InventoryMovement::distinct('product_id')->count('product_id');
        $fechaMasAntigua = InventoryMovement::orderBy('movement_date', 'asc')->value('movement_date');
        $fechaMasReciente = InventoryMovement::orderBy('movement_date', 'desc')->value('movement_date');
        
        $this->info("📊 Total de movimientos: {$totalMovimientos}");
        $this->info("📦 Productos con movimientos: {$productosConMovimientos}");
        $this->info("📅 Rango de fechas: {$fechaMasAntigua} hasta {$fechaMasReciente}");
        
        // Si se proporciona un ID de producto específico
        if ($productoId = $this->argument('producto_id')) {
            $this->verificarProducto($productoId);
        } else {
            // Mostrar algunos productos de ejemplo
            $this->mostrarProductosEjemplo();
        }
        
        $this->info('');
        $this->info('✅ Verificación completada!');
        $this->info('El kardex debería mostrar datos ahora con el rango de fechas ajustado.');
    }
    
    private function verificarProducto($productoId)
    {
        $producto = Product::find($productoId);
        if (!$producto) {
            $this->error("❌ Producto {$productoId} no encontrado");
            return;
        }
        
        $movimientos = InventoryMovement::where('product_id', $productoId)->count();
        $primerMovimiento = InventoryMovement::where('product_id', $productoId)
            ->orderBy('movement_date', 'asc')
            ->first();
        $ultimoMovimiento = InventoryMovement::where('product_id', $productoId)
            ->orderBy('movement_date', 'desc')
            ->first();
            
        $this->info('');
        $this->info("📋 Producto: {$producto->name}");
        $this->info("   SKU: {$producto->code}");
        $this->info("   Total movimientos: {$movimientos}");
        
        if ($primerMovimiento) {
            $this->info("   Primer movimiento: {$primerMovimiento->movement_date}");
            $this->info("   Último movimiento: {$ultimoMovimiento->movement_date}");
            $this->info("   Tipo primer movimiento: {$primerMovimiento->type}");
            $this->info("   Cantidad primer movimiento: {$primerMovimiento->qty}");
        } else {
            $this->warn("   ⚠️  Sin movimientos registrados");
        }
    }
    
    private function mostrarProductosEjemplo()
    {
        $this->info('');
        $this->info('📋 PRODUCTOS DE EJEMPLO:');
        
        // Obtener productos que tienen movimientos
        $productosConMovimientos = InventoryMovement::distinct('product_id')
            ->pluck('product_id')
            ->take(5);
            
        $productos = Product::where('track_inventory', true)
            ->whereIn('id', $productosConMovimientos)
            ->get();
            
        foreach ($productos as $producto) {
            $movimientos = InventoryMovement::where('product_id', $producto->id)->count();
            $this->info("   ID: {$producto->id} - {$producto->name} ({$movimientos} movimientos)");
        }
        
        $this->info('');
        $this->info('💡 Puedes verificar un producto específico con:');
        $this->info('   php artisan kardex:verificar [ID_DEL_PRODUCTO]');
    }
}