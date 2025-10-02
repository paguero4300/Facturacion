<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Traits\GlobalFilters;

class DetallesController extends Controller
{
    use GlobalFilters;
    /**
     * Muestra la página principal de Detalles
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Obtener datos de filtros
        $filterData = $this->getFilterData();
        $activeFilters = $this->getActiveFilters($request);
        $filterBreadcrumbs = $this->getFilterBreadcrumbs($request);
        $clearFiltersUrl = $this->getClearFiltersUrl($request);

        $menuCategories = $this->getFilteredCategories($request)->get();
        $mainCategories = $menuCategories;

        // Cargar productos destacados con filtros aplicados
        $featuredProducts = $this->getFilteredProducts($request, [
            'featured' => true,
            'order_by' => 'name',
            'order_direction' => 'asc'
        ])->get();

        return view('index', compact(
            'menuCategories', 
            'mainCategories', 
            'featuredProducts',
            'filterData',
            'activeFilters',
            'filterBreadcrumbs',
            'clearFiltersUrl'
        ));
    }
    
    /**
     * Muestra una categoría específica con sus productos
     *
     * @param \Illuminate\Http\Request $request
     * @param string $categorySlug
     * @return \Illuminate\View\View
     */
    public function showCategory(Request $request, string $categorySlug)
    {
        $category = Category::where('slug', $categorySlug)
            ->where('status', true)
            ->with(['parent.activeChildren', 'activeChildren'])
            ->firstOrFail();
        
        // Obtener datos de filtros
        $filterData = $this->getFilterData();
        $activeFilters = $this->getActiveFilters($request);
        $filterBreadcrumbs = $this->getFilterBreadcrumbs($request);
        $clearFiltersUrl = $this->getClearFiltersUrl($request);

        // Construir consulta base para los productos de la categoría
        $query = Product::where('status', 'active')
            ->where('for_sale', true);
        
        // Si es una categoría padre (tiene subcategorías), incluir productos de subcategorías
        if ($category->hasChildren()) {
            $categoryIds = $category->activeChildren->pluck('id')->push($category->id);
            $query->whereIn('category_id', $categoryIds);
        } else {
            // Si es una subcategoría, solo productos de esta categoría
            $query->where('category_id', $category->id);
        }
        
        // Aplicar filtros adicionales (almacén)
        // Si no hay warehouse seleccionado, usar el warehouse principal por defecto
        $warehouseId = $request->warehouse;
        if (!$warehouseId) {
            $defaultWarehouse = \App\Models\Warehouse::where('is_default', true)->first();
            $warehouseId = $defaultWarehouse?->id;
        }

        if ($warehouseId) {
            $query->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                  ->where('qty', '>', 0);
            });
        }
        
        $products = $query->orderBy('name', 'asc')->get();
        
        $menuCategories = $this->getFilteredCategories($request)->get();
        
        return view('category', [
            'category' => $category,
            'products' => $products,
            'menuCategories' => $menuCategories,
            'filterData' => $filterData,
            'activeFilters' => $activeFilters,
            'filterBreadcrumbs' => $filterBreadcrumbs,
            'clearFiltersUrl' => $clearFiltersUrl,
        ]);
    }
    
    /**
     * Procesa el formulario de contacto
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitContact(Request $request)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
        ]);
        
        // Aquí se procesaría el formulario (enviar email, guardar en BD, etc.)
        // Por ahora, solo redirigimos con un mensaje de éxito
        
        return redirect()->route('contacto')->with('success', '¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.');
    }
}
