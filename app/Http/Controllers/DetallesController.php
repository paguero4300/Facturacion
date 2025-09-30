<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class DetallesController extends Controller
{
    /**
     * Muestra la página principal de Detalles
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $menuCategories = Category::where('status', true)
            ->parents()
            ->with('activeChildren')
            ->get();
        
        $mainCategories = $menuCategories;
        
        return view('index', compact('menuCategories', 'mainCategories'));
    }
    
    /**
     * Muestra una categoría específica con sus productos
     *
     * @param  string  $categorySlug
     * @return \Illuminate\View\View
     */
    public function showCategory(string $categorySlug)
    {
        $category = Category::where('slug', $categorySlug)
            ->where('status', true)
            ->with([
                'products' => function ($query) {
                    $query->where('status', 'active')
                          ->where('for_sale', true)
                          ->orderBy('name', 'asc');
                },
                'parent.activeChildren',
                'activeChildren'
            ])
            ->firstOrFail();
        
        // Si es una categoría padre (tiene subcategorías), cargar todos los productos
        // incluyendo los de las subcategorías
        if ($category->hasChildren()) {
            $categoryIds = $category->activeChildren->pluck('id')->push($category->id);
            
            $products = Product::whereIn('category_id', $categoryIds)
                ->where('status', 'active')
                ->where('for_sale', true)
                ->orderBy('name', 'asc')
                ->get();
        } else {
            // Si es una subcategoría, solo mostrar sus productos
            $products = $category->products;
        }
        
        $menuCategories = Category::where('status', true)
            ->parents()
            ->with('activeChildren')
            ->get();
        
        return view('category', [
            'category' => $category,
            'products' => $products,
            'menuCategories' => $menuCategories,
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
