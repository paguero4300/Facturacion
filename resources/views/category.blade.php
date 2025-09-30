<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $category->name }} - Detalles - Tienda de Regalos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body class="bg-gray-50">
    @include('partials.header')
    
    <!-- Category Header -->
    <div class="bg-gradient-to-r from-pink-500 to-rose-500 text-white py-8">
        <div class="max-w-7xl mx-auto px-4">
            <nav class="text-sm mb-3 opacity-90">
                <a href="{{ route('detalles.index') }}" class="hover:underline hover:opacity-100 transition">Inicio</a>
                @if($category->parent)
                    <span class="mx-2">›</span>
                    <a href="{{ url('/' . $category->parent->slug) }}" class="hover:underline hover:opacity-100 transition">{{ $category->parent->name }}</a>
                @endif
                <span class="mx-2">›</span>
                <span class="font-semibold">{{ $category->name }}</span>
            </nav>
            <h1 class="text-3xl md:text-4xl font-bold mb-1">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-base md:text-lg text-pink-100">{{ $category->description }}</p>
            @endif
        </div>
    </div>

    <!-- Products Grid -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            @if($products->count() > 0)
                <p class="text-gray-600 mb-6 text-base">
                    {{ $products->count() }} {{ $products->count() === 1 ? 'producto encontrado' : 'productos encontrados' }}
                </p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                            <!-- Product Image -->
                            <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                                <img 
                                    src="{{ $product->image_path && file_exists(storage_path('app/public/' . $product->image_path)) ? asset('storage/' . $product->image_path) : asset('images/no-image.png') }}" 
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                    onerror="this.src='{{ asset('images/no-image.png') }}';"
                                >
                            </div>
                            
                            <!-- Product Info -->
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 min-h-[3.5rem]">
                                    {{ $product->name }}
                                </h3>
                                
                                @if($product->description)
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                        {{ $product->description }}
                                    </p>
                                @endif
                                
                                <!-- Price -->
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-pink-600">
                                        S/ {{ number_format($product->unit_price, 2) }}
                                    </span>
                                    <button 
                                        class="bg-pink-500 hover:bg-pink-600 text-white p-2 rounded-full transition"
                                        title="Agregar al carrito"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- No Products -->
                <div class="text-center py-16">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="text-2xl font-semibold text-gray-700 mb-2">No hay productos disponibles</h3>
                    <p class="text-gray-500 mb-8">Aún no tenemos productos en esta categoría.</p>
                    <a href="{{ route('detalles.index') }}" class="inline-block px-6 py-3 bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded-lg">
                        Volver al inicio
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    @include('partials.footer')
</body>
</html>
