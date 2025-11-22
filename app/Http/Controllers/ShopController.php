<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\GlobalFilters;

class ShopController extends Controller
{
    use GlobalFilters;
    /**
     * Display product catalog
     */
    public function index(Request $request)
    {
        // Obtener datos de filtros
        $filterData = $this->getFilterData();
        $activeFilters = $this->getActiveFilters($request);
        $filterBreadcrumbs = $this->getFilterBreadcrumbs($request);
        $clearFiltersUrl = $this->getClearFiltersUrl($request);

        // Construir consulta con filtros aplicados
        $query = $this->buildFilteredQuery($request);
        
        $products = $query->orderBy('name')->paginate(12);
        $categories = $filterData['categories'];
        $warehouses = $filterData['warehouses'];
        
        // Obtener categorías para el header (mismo método que home)
        $menuCategories = $this->getFilteredCategories($request)->get();

        return view('shop.index', compact(
            'products', 
            'categories', 
            'warehouses',
            'activeFilters',
            'filterBreadcrumbs',
            'clearFiltersUrl',
            'menuCategories'
        ));
    }

    /**
     * Display individual product
     */
    public function show(Request $request, $id)
    {
        $product = Product::where('status', 'active')
            ->where('for_sale', true)
            ->with('category')
            ->findOrFail($id);

        // Get related products from same category with filters applied
        $relatedQuery = Product::where('status', 'active')
            ->where('for_sale', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id);
        
        // Apply warehouse filter to related products
        // Si no hay warehouse seleccionado, usar el warehouse principal por defecto
        $warehouseId = $request->warehouse;
        if (!$warehouseId) {
            $defaultWarehouse = \App\Models\Warehouse::where('is_default', true)->first();
            $warehouseId = $defaultWarehouse?->id;
        }

        if ($warehouseId) {
            $relatedQuery->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                  ->where('qty', '>', 0);
            });
        }
        
        $related = $relatedQuery->limit(4)->get();
        
        // Obtener categorías para el header (mismo método que home)
        $menuCategories = $this->getFilteredCategories($request)->get();

        return view('shop.product', compact('product', 'related', 'menuCategories'));
    }
}
