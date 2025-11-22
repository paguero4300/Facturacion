<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;

class CheckWarehouseSelection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Excluir rutas de Livewire y Admin para evitar conflictos y redirecciones incorrectas
        if ($request->is('livewire/*') || $request->is('admin/*')) {
            return $next($request);
        }

        // 1. Si la URL ya tiene el parámetro warehouse, guardamos la cookie y seguimos
        if ($request->has('warehouse')) {
            $warehouseId = $request->input('warehouse');
            
            // Guardar cookie por 30 días
            Cookie::queue('warehouse_id', $warehouseId, 60 * 24 * 30);
            
            return $next($request);
        }

        // 2. Si NO tiene parámetro, verificamos si existe la cookie
        $cookieWarehouseId = $request->cookie('warehouse_id');

        if ($cookieWarehouseId) {
            // Si existe cookie, redirigimos agregando el parámetro
            // Mantenemos otros parámetros de la query string si existen
            $params = $request->query();
            $params['warehouse'] = $cookieWarehouseId;
            
            return redirect()->route($request->route()->getName(), $params);
        }

        // 3. Si no hay parámetro ni cookie, dejamos pasar
        // La vista detectará que no hay warehouse seleccionado y mostrará el Modal de Bienvenida
        return $next($request);
    }
}
