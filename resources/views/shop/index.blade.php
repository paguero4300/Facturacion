@extends('layouts.app')

@section('title', 'Tienda - Detalles y Más')

@section('content')
    <!-- Page Header (Matching Home Hero Style) -->
    <div class="relative bg-gray-900 py-16 md:py-24 overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('logos/herosection.png') }}" alt="Tienda Background" class="w-full h-full object-cover opacity-20">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-900/90 to-gray-900/50"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4 tracking-tight">
                NUESTRA <span class="text-[var(--naranja)]">TIENDA</span>
            </h1>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto font-light">
                Descubre nuestra selección exclusiva de arreglos florales y regalos para cada ocasión.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar - Categories Filter -->
                <aside class="lg:w-64 flex-shrink-0">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                        <h3 class="text-lg font-bold mb-6 text-gray-900 border-b pb-2">Categorías</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('shop.index') }}{{ request()->has('warehouse') ? '?warehouse=' . request()->warehouse : '' }}" 
                                   class="block px-4 py-2.5 rounded-lg transition-all duration-200 {{ !request('category') ? 'bg-[var(--naranja)] text-white font-medium shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-[var(--naranja)]' }}">
                                    Todos los productos
                                </a>
                            </li>
                            @foreach($menuCategories as $category)
                                <li>
                                    @php
                                        $categoryParams = ['category' => $category->slug];
                                        if (request()->has('warehouse')) {
                                            $categoryParams['warehouse'] = request()->warehouse;
                                        }
                                    @endphp
                                    <a href="{{ route('shop.index') }}?{{ http_build_query($categoryParams) }}" 
                                       class="block px-4 py-2.5 rounded-lg transition-all duration-200 {{ request('category') == $category->slug ? 'bg-[var(--naranja)] text-white font-medium shadow-md' : 'text-gray-600 hover:bg-gray-50 hover:text-[var(--naranja)]' }}">
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
                                <div class="mt-8 pt-6 border-t border-gray-100">
                                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                                        <i class="fas fa-filter mr-1"></i>Filtro Activo
                                    </div>
                                    <div class="flex items-center justify-between bg-blue-50 px-3 py-2 rounded-lg border border-blue-100">
                                        <span class="text-sm font-medium text-blue-800">
                                            <i class="fas fa-warehouse mr-1"></i>{{ $selectedWarehouse->name }}
                                        </span>
                                        <a href="{{ route('shop.index') }}{{ request()->has('category') ? '?category=' . request()->category : '' }}" 
                                           class="text-blue-600 hover:text-blue-800 transition p-1" title="Quitar filtro">
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
                        <div class="mb-6 flex items-center justify-between bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                            <p class="text-gray-600 text-sm">
                                Mostrando <span class="font-bold text-gray-900">{{ $products->firstItem() }}</span> - 
                                <span class="font-bold text-gray-900">{{ $products->lastItem() }}</span> de 
                                <span class="font-bold text-gray-900">{{ $products->total() }}</span> productos
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($products as $product)
                                <div class="group relative flex flex-col overflow-hidden rounded-lg bg-white shadow-sm transition-all duration-300 hover:shadow-xl border border-gray-100">
                                    <!-- Imagen y Overlay -->
                                    <div class="relative aspect-square overflow-hidden bg-gray-100">
                                        <!-- Etiqueta Oferta -->
                                        @if($product->sale_price && $product->sale_price < $product->unit_price)
                                            <span class="absolute left-2 top-2 z-10 rounded bg-red-500 px-2 py-1 text-xs font-bold text-white shadow-sm">
                                                -{{ round((($product->unit_price - $product->sale_price) / $product->unit_price) * 100) }}%
                                            </span>
                                        @endif

                                        <a href="{{ route('shop.product', $product->id) }}" class="block h-full w-full">
                                            <img src="{{ $product->image_path && file_exists(storage_path('app/public/' . $product->image_path)) ? asset('storage/' . $product->image_path) : asset('images/no-image.png') }}"
                                                 alt="{{ $product->name }}"
                                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                                 loading="lazy"
                                                 onerror="this.src='{{ asset('images/no-image.png') }}';">
                                        </a>

                                        <!-- Botones Flotantes (Hover) -->
                                        <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto">
                                            <a href="{{ route('shop.product', $product->id) }}" 
                                               class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-900 hover:text-[var(--naranja)] transition-colors shadow-lg transform hover:scale-110">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Info Producto -->
                                    <div class="flex flex-1 flex-col p-5">
                                        <!-- Category Badge -->
                                        @if($product->category)
                                            <div class="mb-2">
                                                <span class="inline-block text-xs font-medium text-gray-500 uppercase tracking-wide">
                                                    {{ $product->category->name }}
                                                </span>
                                            </div>
                                        @endif

                                        <h3 class="mb-2 text-lg font-bold text-gray-900 line-clamp-2 min-h-[3.5rem]">
                                            <a href="{{ route('shop.product', $product->id) }}" class="hover:text-[var(--naranja)] transition-colors">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        
                                        <div class="mb-4 flex items-center gap-2">
                                            @if($product->sale_price && $product->sale_price < $product->unit_price)
                                                <span class="text-sm text-gray-400 line-through">S/ {{ number_format($product->unit_price, 2) }}</span>
                                                <span class="text-xl font-bold text-[var(--naranja)]">S/ {{ number_format($product->sale_price, 2) }}</span>
                                            @else
                                                <span class="text-xl font-bold text-[var(--naranja)]">S/ {{ number_format($product->unit_price, 2) }}</span>
                                            @endif
                                        </div>

                                        <!-- Stock Info -->
                                        @php
                                            $warehouseStock = $product->stocks->where('warehouse_id', $warehouseId ?? null)->first();
                                            $stockQty = $warehouseStock ? $warehouseStock->qty : 0;
                                            $isOutOfStock = $stockQty <= 0;
                                        @endphp
                                        <div class="mb-4">
                                            @if($stockQty > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1.5"></span>
                                                    Stock: {{ number_format($stockQty, 0) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1.5"></span>
                                                    Agotado
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Botón Añadir -->
                                        @if($isOutOfStock)
                                            <button type="button" disabled class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-300 py-2.5 text-sm font-bold text-gray-500 cursor-not-allowed">
                                                <i class="fas fa-times"></i>
                                                Agotado
                                            </button>
                                        @else
                                            <form action="{{ route('cart.add') }}" method="POST" class="mt-auto">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" 
                                                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 py-2.5 text-sm font-bold text-white hover:bg-[var(--naranja)] transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                                    <i class="fas fa-shopping-cart"></i>
                                                    Añadir al carrito
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($products->hasPages())
                            <div class="mt-12">
                                {{ $products->links() }}
                            </div>
                        @endif
                    @else
                        <!-- No Products -->
                        <div class="text-center py-20">
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 max-w-lg mx-auto">
                                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fas fa-search text-3xl text-gray-300"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-3">No encontramos productos</h3>
                                <p class="text-gray-500 mb-8">Intenta ajustar tus filtros o busca en otra categoría.</p>
                                <a href="{{ route('shop.index') }}" 
                                   class="inline-flex items-center justify-center px-8 py-3 text-base font-bold text-white bg-[var(--naranja)] rounded-full hover:bg-white hover:text-[var(--naranja)] border-2 border-transparent hover:border-[var(--naranja)] transition-all duration-300">
                                    Ver todos los productos
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-xl z-50 flex items-center gap-3 animate-fade-in-up" id="success-message">
            <i class="fas fa-check-circle text-xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById('success-message');
                if(msg) {
                    msg.style.opacity = '0';
                    msg.style.transform = 'translateY(10px)';
                    setTimeout(() => msg.remove(), 300);
                }
            }, 3000);
        </script>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-xl z-50 flex items-center gap-3 animate-fade-in-up" id="error-message">
            <i class="fas fa-exclamation-circle text-xl"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById('error-message');
                if(msg) {
                    msg.style.opacity = '0';
                    msg.style.transform = 'translateY(10px)';
                    setTimeout(() => msg.remove(), 300);
                }
            }, 3000);
        </script>
    @endif
@endsection

