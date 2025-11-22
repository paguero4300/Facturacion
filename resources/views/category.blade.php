@extends('layouts.app')

@section('title', $category->name . ' - Detalles y Más')

@section('content')
    <!-- Page Header -->
    <div class="relative bg-gradient-to-br from-[var(--fondo-principal)] via-white to-orange-50 py-16 overflow-hidden">
        <div class="absolute inset-0 bg-[url('/api/placeholder/20/20')] opacity-5"></div>
        <div class="relative max-w-7xl mx-auto px-4 text-center">
            <!-- Breadcrumb -->
            <nav class="flex justify-center text-sm font-medium mb-4" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="text-gray-500 hover:text-[var(--naranja)] transition-colors">Inicio</a>
                <span class="mx-2 text-gray-300">/</span>
                @if($category->parent)
                    <a href="{{ url('/' . $category->parent->slug) }}" class="text-gray-500 hover:text-[var(--naranja)] transition-colors">
                        {{ $category->parent->name }}
                    </a>
                    <span class="mx-2 text-gray-300">/</span>
                @endif
                <span class="text-gray-900 font-semibold">{{ $category->name }}</span>
            </nav>
            
            <h1 class="text-4xl md:text-5xl font-black text-[var(--enlaces-titulos)] mb-4">
                {{ $category->name }}
            </h1>
            
            @if($category->description)
                <p class="text-lg text-[var(--texto-principal)] max-w-2xl mx-auto">
                    {{ $category->description }}
                </p>
            @endif
        </div>
    </div>

    <!-- Products Section -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <!-- Filter Breadcrumbs -->
            @include('partials.filter-breadcrumbs', [
                'filterBreadcrumbs' => $filterBreadcrumbs ?? [],
                'clearFiltersUrl' => $clearFiltersUrl ?? url('/' . $category->slug),
                'filteredProductsCount' => $products->count()
            ])
            
            @if($products->count() > 0)
                <div class="mb-6 flex items-center justify-between bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <p class="text-gray-600 text-sm">
                        <span class="font-bold text-gray-900">{{ $products->count() }}</span> 
                        {{ $products->count() === 1 ? 'producto encontrado' : 'productos encontrados' }}
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

                            <!-- Información del Producto -->
                            <div class="flex flex-1 flex-col p-6">
                                <!-- Categoría Badge -->
                                @if($product->category)
                                    <span class="inline-block w-fit px-2 py-1 text-xs font-semibold uppercase tracking-wider text-[var(--naranja)] bg-orange-50 rounded mb-3">
                                        {{ $product->category->name }}
                                    </span>
                                @endif

                                <!-- Nombre del Producto -->
                                <a href="{{ route('shop.product', $product->id) }}" class="group/title">
                                    <h3 class="text-lg font-bold text-gray-900 group-hover/title:text-[var(--naranja)] transition-colors line-clamp-2 min-h-[3.5rem] mb-3">
                                        {{ $product->name }}
                                    </h3>
                                </a>

                                <!-- Stock Indicator -->
                                @php
                                    $warehouseStock = $product->stocks->where('warehouse_id', $warehouseId ?? null)->first();
                                    $stockQty = $warehouseStock ? $warehouseStock->qty : 0;
                                    $isOutOfStock = $stockQty <= 0;
                                @endphp
                                <div class="mb-3">
                                    @if($stockQty > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-green-50 text-green-700 rounded-full">
                                            <i class="fas fa-check-circle"></i>
                                            Stock: {{ number_format($stockQty, 0) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-red-50 text-red-700 rounded-full">
                                            <i class="fas fa-times-circle"></i>
                                            Agotado
                                        </span>
                                    @endif
                                </div>

                                <!-- Precio y Botón -->
                                <div class="mt-auto">
                                    <div class="mb-4">
                                        @if($product->sale_price && $product->sale_price < $product->unit_price)
                                            <div class="flex items-center gap-2">
                                                <span class="text-2xl font-black text-[var(--naranja)]">
                                                    S/ {{ number_format($product->sale_price, 2) }}
                                                </span>
                                                <span class="text-sm text-gray-400 line-through">
                                                    S/ {{ number_format($product->unit_price, 2) }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-2xl font-black text-[var(--naranja)]">
                                                S/ {{ number_format($product->unit_price, 2) }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Add to Cart Button -->
                                    @if($isOutOfStock)
                                        <button type="button" disabled class="w-full bg-gray-300 text-gray-500 font-bold py-3 px-4 rounded-lg cursor-not-allowed flex items-center justify-center gap-2">
                                            <i class="fas fa-times"></i>
                                            <span>Agotado</span>
                                        </button>
                                    @else
                                        <form action="{{ route('cart.add') }}" method="POST" class="w-full">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit"
                                                    class="w-full bg-gray-900 hover:bg-[var(--naranja)] text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                                <i class="fas fa-shopping-cart"></i>
                                                <span>Añadir al carrito</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Estado Vacío -->
                <div class="text-center py-16 bg-white rounded-2xl shadow-sm">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No hay productos disponibles</h3>
                        <p class="text-gray-600 mb-8">
                            @if(request()->has('warehouse'))
                                No tenemos productos de esta categoría en el almacén seleccionado.
                            @else
                                Aún no tenemos productos en esta categoría.
                            @endif
                        </p>
                        <div class="flex gap-4 justify-center">
                            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-[var(--naranja)] hover:bg-gray-900 text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-md hover:shadow-lg">
                                <i class="fas fa-shopping-bag"></i>
                                Ver toda la tienda
                            </a>
                            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold px-6 py-3 rounded-lg border-2 border-gray-200 transition-all">
                                <i class="fas fa-home"></i>
                                Volver al inicio
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
