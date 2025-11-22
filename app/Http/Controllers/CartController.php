<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display the cart
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = $this->calculateTotal($cart);

        return view('cart.index', compact('cart', 'total'));
    }

    /**
     * Add product to cart
     */
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = max(1, (int) $request->input('quantity', 1));
        $action = $request->input('action', 'add_to_cart');

        $product = Product::findOrFail($productId);

        // Verify product is available
        if ($product->status !== 'active' || !$product->for_sale) {
            return back()->with('error', 'Producto no disponible');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            // Update quantity if product already in cart
            $cart[$productId]['quantity'] += $quantity;
        } else {
            // Add new product to cart
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sale_price ?? $product->unit_price,
                'quantity' => $quantity,
                'image' => $product->image_path,
            ];
        }

        session()->put('cart', $cart);

        // Si es "comprar ahora", redirigir directamente a checkout
        if ($action === 'buy_now') {
            return redirect()->route('checkout.index');
        }

        return back()->with('success', '¡Producto agregado al carrito!');
    }


    /**
     * Update cart item quantity
     */
    public function update(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = max(1, (int) $request->input('quantity', 1));

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Carrito actualizado');
    }

    /**
     * Remove item from cart
     */
    public function remove($productId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Producto eliminado del carrito');
    }

    /**
     * Calculate cart total
     */
    private function calculateTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
}
