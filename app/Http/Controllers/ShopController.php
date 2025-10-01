<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display product catalog
     */
    public function index(Request $request)
    {
        $query = Product::where('status', 'active')
            ->where('for_sale', true);

        // Filter by category if provided
        if ($request->has('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $products = $query->orderBy('name')->paginate(12);
        $categories = Category::where('status', true)
            ->parents()
            ->orderBy('name')
            ->get();

        return view('shop.index', compact('products', 'categories'));
    }

    /**
     * Display individual product
     */
    public function show($id)
    {
        $product = Product::where('status', 'active')
            ->where('for_sale', true)
            ->with('category')
            ->findOrFail($id);

        // Get related products from same category
        $related = Product::where('status', 'active')
            ->where('for_sale', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('shop.product', compact('product', 'related'));
    }
}
