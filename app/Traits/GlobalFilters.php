<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait GlobalFilters
{
    /**
     * Aplicar filtros globales a una consulta de productos
     */
    public function applyGlobalFilters(Builder $query, Request $request): Builder
    {
        // Filtro por categoría
        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filtro por almacén
        // Si no hay warehouse seleccionado, usar el warehouse principal por defecto
        $warehouseId = $request->warehouse;
        if (!$warehouseId) {
            $defaultWarehouse = Warehouse::where('is_default', true)->first();
            $warehouseId = $defaultWarehouse?->id;
        }

        if ($warehouseId) {
            $query->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                  ->where('qty', '>', 0);
            });
        }

        return $query;
    }

    /**
     * Construir consulta filtrada base para productos
     */
    public function buildFilteredQuery(Request $request): Builder
    {
        $query = Product::where('status', 'active')
            ->where('for_sale', true);

        return $this->applyGlobalFilters($query, $request);
    }

    /**
     * Obtener datos para filtros (categorías y almacenes)
     */
    public function getFilterData(): array
    {
        $categories = Category::where('status', true)
            ->parents()
            ->with('activeChildren')
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return [
            'categories' => $categories,
            'warehouses' => $warehouses,
        ];
    }

    /**
     * Obtener filtros activos desde la request
     */
    public function getActiveFilters(Request $request): array
    {
        $filters = [];

        if ($request->has('category') && $request->category) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $filters['category'] = [
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'url_param' => 'category=' . $category->slug,
                ];
            }
        }

        // Si hay warehouse seleccionado explícitamente, mostrarlo
        if ($request->has('warehouse') && $request->warehouse) {
            $warehouse = Warehouse::find($request->warehouse);
            if ($warehouse) {
                $filters['warehouse'] = [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'url_param' => 'warehouse=' . $warehouse->id,
                    'is_default' => false,
                ];
            }
        } else {
            // Si no hay warehouse seleccionado, mostrar el principal como activo
            $defaultWarehouse = Warehouse::where('is_default', true)->first();
            if ($defaultWarehouse) {
                $filters['warehouse'] = [
                    'id' => $defaultWarehouse->id,
                    'name' => $defaultWarehouse->name,
                    'url_param' => 'warehouse=' . $defaultWarehouse->id,
                    'is_default' => true,
                ];
            }
        }

        return $filters;
    }

    /**
     * Generar URL con filtros
     */
    public function buildFilterUrl(Request $request, array $filters = [], bool $removeFilter = false, string $filterType = null): string
    {
        $params = $request->query();

        if ($removeFilter && $filterType) {
            // Remover filtro específico
            unset($params[$filterType]);
        } else {
            // Agregar o actualizar filtros
            foreach ($filters as $key => $value) {
                $params[$key] = $value;
            }
        }

        $url = request()->url();
        return $url . ($params ? '?' . http_build_query($params) : '');
    }

    /**
     * Obtener productos con filtros aplicados para un contexto específico
     */
    public function getFilteredProducts(Request $request, array $options = []): Builder
    {
        $query = $this->buildFilteredQuery($request);

        // Opciones adicionales
        if (isset($options['featured']) && $options['featured']) {
            $query->where('featured', true);
        }

        if (isset($options['category_ids']) && is_array($options['category_ids'])) {
            $query->whereIn('category_id', $options['category_ids']);
        }

        if (isset($options['limit'])) {
            $query->limit($options['limit']);
        }

        if (isset($options['order_by'])) {
            $orderBy = $options['order_by'];
            $direction = $options['order_direction'] ?? 'asc';
            $query->orderBy($orderBy, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        return $query;
    }

    /**
     * Obtener categorías con productos filtrados
     */
    public function getFilteredCategories(Request $request): Builder
    {
        $query = Category::where('status', true)->parents();

        // Determinar warehouse a filtrar (explícito o principal por defecto)
        $warehouseId = $request->warehouse;
        if (!$warehouseId) {
            $defaultWarehouse = Warehouse::where('is_default', true)->first();
            $warehouseId = $defaultWarehouse?->id;
        }

        // Si hay warehouse (explícito o por defecto), filtrar categorías con productos con stock
        // COMENTADO: Para mostrar todas las categorías activas en el menú, independientemente del stock
        /*
        if ($warehouseId) {
            $query->whereHas('products', function ($q) use ($warehouseId) {
                $q->where('status', 'active')
                  ->where('for_sale', true)
                  ->whereHas('stocks', function ($stockQuery) use ($warehouseId) {
                      $stockQuery->where('warehouse_id', $warehouseId)
                                 ->where('qty', '>', 0);
                  });
            });
        }
        */

        return $query->with('activeChildren')->orderBy('name');
    }

    /**
     * Contar productos por filtros
     */
    public function getFilteredProductsCount(Request $request, array $options = []): int
    {
        return $this->getFilteredProducts($request, $options)->count();
    }

    /**
     * Verificar si hay filtros activos
     */
    public function hasActiveFilters(Request $request): bool
    {
        return $request->has('category') || $request->has('warehouse');
    }

    /**
     * Obtener breadcrumbs de filtros
     */
    public function getFilterBreadcrumbs(Request $request): array
    {
        $breadcrumbs = [];
        $activeFilters = $this->getActiveFilters($request);

        if (isset($activeFilters['category'])) {
            $breadcrumbs[] = [
                'type' => 'category',
                'label' => 'Categoría: ' . $activeFilters['category']['name'],
                'remove_url' => $this->buildFilterUrl($request, [], true, 'category'),
            ];
        }

        if (isset($activeFilters['warehouse'])) {
            $breadcrumbs[] = [
                'type' => 'warehouse',
                'label' => 'Almacén: ' . $activeFilters['warehouse']['name'],
                'remove_url' => $this->buildFilterUrl($request, [], true, 'warehouse'),
            ];
        }

        return $breadcrumbs;
    }

    /**
     * URL para limpiar todos los filtros
     */
    public function getClearFiltersUrl(Request $request): string
    {
        return request()->url();
    }
}