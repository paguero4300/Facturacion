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

        // Evitar redirigir peticiones POST (como logout o formularios)
        if ($request->isMethod('post')) {
            // Si tenemos cookie pero no parámetro, inyectamos el valor en el request
            // para que los controladores lo reciban sin necesidad de redirección
            if (!$request->has('warehouse') && $request->cookie('warehouse_id')) {
                $request->merge(['warehouse' => $request->cookie('warehouse_id')]);
            }
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
            // Mantenemos TODOS los parámetros: de ruta (ej: categorySlug) y de query string
            $params = array_merge(
                $request->route()->parameters(), // Parámetros de ruta como {categorySlug}
                $request->query()                // Parámetros de query string como ?search=..
            );
            $params['warehouse'] = $cookieWarehouseId;
            
            return redirect()->route($request->route()->getName(), $params);
        }

        // 3. Si no hay parámetro ni cookie, dejamos pasar
        // La vista detectará que no hay warehouse seleccionado y mostrará el Modal de Bienvenida
        return $next($request);
    }
}
