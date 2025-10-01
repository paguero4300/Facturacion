<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - Detalles y Más</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body class="bg-gray-50">
    @include('partials.header')
    
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-pink-500 to-rose-500 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-4xl md:text-5xl font-bold mb-3">Nuestra Tienda</h1>
            <p class="text-lg md:text-xl text-pink-100">Descubre todos nuestros productos</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar - Categories Filter Only -->
                <aside class="lg:w-64 flex-shrink-0">
                    <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
                        <h3 class="text-lg font-bold mb-4" style="color: var(--enlaces-titulos);">Categorías</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('shop.index') }}{{ request()->has('warehouse') ? '?warehouse=' . request()->warehouse : '' }}" 
                                   class="block px-3 py-2 rounded-lg transition {{ !request('category') ? 'bg-pink-50 text-pink-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                    Todos los productos
                                </a>
                            </li>
                            @foreach($categories as $category)
                                <li>
                                    @php
                                        $categoryParams = ['category' => $category->slug];
                                        if (request()->has('warehouse')) {
                                            $categoryParams['warehouse'] = request()->warehouse;
                                        }
                                    @endphp
                                    <a href="{{ route('shop.index') }}?{{ http_build_query($categoryParams) }}" 
                                       class="block px-3 py-2 rounded-lg transition {{ request('category') == $category->slug ? 'bg-pink-50 text-pink-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        
                        <!-- Indicador de filtro de almacén activo -->
                        @if(request()->has('warehouse') && request()->warehouse)
                            @php
                                $selectedWarehouse = \App\Models\Warehouse::find(request()->warehouse);
                            @endphp
                            @if($selectedWarehouse)
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    <div class="text-sm text-gray-600 mb-2">
                                        <i class="fas fa-filter mr-1"></i>Filtrado por:
                                    </div>
                                    <div class="flex items-center justify-between bg-blue-50 px-3 py-2 rounded-lg">
                                        <span class="text-sm font-medium text-blue-800">
                                            <i class="fas fa-warehouse mr-1"></i>{{ $selectedWarehouse->name }}
                                        </span>
                                        <a href="{{ route('shop.index') }}{{ request()->has('category') ? '?category=' . request()->category : '' }}" 
                                           class="text-blue-600 hover:text-blue-800 transition" title="Quitar filtro">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </aside>

                <!-- Products Grid -->
                <div class="flex-1">
                    <!-- Filter Breadcrumbs -->
                    @include('partials.filter-breadcrumbs', [
                        'filterBreadcrumbs' => $filterBreadcrumbs ?? [],
                        'clearFiltersUrl' => $clearFiltersUrl ?? request()->url(),
                        'filteredProductsCount' => $products->total()
                    ])
                    
                    @if($products->count() > 0)
                        <div class="mb-6 flex items-center justify-between">
                            <p class="text-gray-600">
                                Mostrando <span class="font-semibold">{{ $products->firstItem() }}</span> - 
                                <span class="font-semibold">{{ $products->lastItem() }}</span> de 
                                <span class="font-semibold">{{ $products->total() }}</span> productos
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($products as $product)
                                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                                    <!-- Product Image -->
                                    <a href="{{ route('shop.product', $product->id) }}" class="block">
                                        <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                                            <img
                                                src="{{ $product->image_path && file_exists(storage_path('app/public/' . $product->image_path)) ? asset('storage/' . $product->image_path) : asset('images/no-image.png') }}"
                                                alt="{{ $product->name }}"
                                                class="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
                                                loading="lazy"
                                                onerror="this.src='{{ asset('images/no-image.png') }}';"
                                            >
                                        </div>
                                    </a>

                                    <!-- Product Info -->
                                    <div class="p-4">
                                        <a href="{{ route('shop.product', $product->id) }}">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 min-h-[3.5rem] hover:text-pink-600 transition">
                                                {{ $product->name }}
                                            </h3>
                                        </a>

                                        @if($product->description)
                                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                                {{ $product->description }}
                                            </p>
                                        @endif

                                        <!-- Category Badge -->
                                        @if($product->category)
                                            <div class="mb-3">
                                                <span class="inline-block px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded">
                                                    {{ $product->category->name }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <!-- Indicador de Stock si hay filtro de almacén activo -->
                                        @if(request()->has('warehouse') && request()->warehouse)
                                            @php
                                                $warehouseStock = $product->stocks->where('warehouse_id', request()->warehouse)->first();
                                                $stockQty = $warehouseStock ? $warehouseStock->qty : 0;
                                            @endphp
                                            <div class="mb-3">
                                                @if($stockQty > 0)
                                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                                        <i class="fas fa-check-circle mr-1"></i>Stock: {{ number_format($stockQty, 0) }}
                                                    </span>
                                                @else
                                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">
                                                        <i class="fas fa-times-circle mr-1"></i>Sin stock
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        <!-- Price and Add to Cart -->
                                        <div class="flex items-center justify-between">
                                            <div>
                                                @if($product->sale_price && $product->sale_price < $product->unit_price)
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xl font-bold text-pink-600">
                                                            S/ {{ number_format($product->sale_price, 2) }}
                                                        </span>
                                                        <span class="text-sm text-gray-400 line-through">
                                                            S/ {{ number_format($product->unit_price, 2) }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-2xl font-bold text-pink-600">
                                                        S/ {{ number_format($product->unit_price, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <form action="{{ route('cart.add') }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button
                                                    type="submit"
                                                    class="bg-pink-500 hover:bg-pink-600 text-white p-2 rounded-full transition"
                                                    title="Agregar al carrito"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($products->hasPages())
                            <div class="mt-8">
                                {{ $products->links() }}
                            </div>
                        @endif
                    @else
                        <!-- No Products -->
                        <div class="text-center py-16">
                            <div class="bg-white rounded-xl shadow-md p-12 max-w-md mx-auto">
                                <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <h3 class="text-2xl font-semibold text-gray-700 mb-2">No hay productos disponibles</h3>
                                <p class="text-gray-500 mb-8">No encontramos productos en esta categoría.</p>
                                <a href="{{ route('shop.index') }}" class="inline-block px-6 py-3 bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded-lg transition">
                                    Ver todos los productos
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @include('partials.footer')

    @if(session('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="success-message">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('success-message')?.remove();
            }, 3000);
        </script>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="error-message">
            {{ session('error') }}
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('error-message')?.remove();
            }, 3000);
        </script>
    @endif
</body>
</html>
