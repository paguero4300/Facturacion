<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Traits\GlobalFilters;

class StoreController extends Controller
{
    use GlobalFilters;

    /**
     * Display the store locator page.
     */
    public function index(Request $request)
    {
        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->get();
            
        // Obtener categorías para el header
        $menuCategories = $this->getFilteredCategories($request)->get();

        return view('stores.index', compact('warehouses', 'menuCategories'));
    }
}
