<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - Tienda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body class="bg-gray-50">
    @include('partials.header')

    <!-- Breadcrumb -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="flex text-sm" aria-label="Breadcrumb">
                <a href="{{ route('shop.index') }}" class="text-gray-500 hover:text-pink-600 transition">Tienda</a>
                @if($product->category)
                    <span class="mx-2 text-gray-400">/</span>
                    <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="text-gray-500 hover:text-pink-600 transition">
                        {{ $product->category->name }}
                    </a>
                @endif
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900 font-medium">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    <!-- Product Detail -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 bg-white rounded-xl shadow-md overflow-hidden p-6 lg:p-8">
                <!-- Product Image -->
                <div class="flex items-center justify-center">
                    <div class="w-full max-w-md">
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                            <img
                                src="{{ $product->image_path && file_exists(storage_path('app/public/' . $product->image_path)) ? asset('storage/' . $product->image_path) : asset('images/no-image.png') }}"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-contain"
                                onerror="this.src='{{ asset('images/no-image.png') }}';"
                            >
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex flex-col">
                    <div class="flex-1">
                        <!-- Category Badge -->
                        @if($product->category)
                            <span class="inline-block px-3 py-1 text-sm font-medium text-pink-600 bg-pink-50 rounded-full mb-3">
                                {{ $product->category->name }}
                            </span>
                        @endif

                        <!-- Product Name -->
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>

                        <!-- Price -->
                        <div class="mb-6">
                            <div class="flex items-baseline gap-3">
                                <span class="text-4xl font-bold text-pink-600">
                                    S/ {{ number_format($product->sale_price ?? $product->unit_price, 2) }}
                                </span>
                                @if($product->sale_price && $product->unit_price != $product->sale_price)
                                    <span class="text-xl text-gray-400 line-through">
                                        S/ {{ number_format($product->unit_price, 2) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        @if($product->description)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Descripción</h3>
                                <p class="text-gray-600 leading-relaxed">{{ $product->description }}</p>
                            </div>
                        @endif

                        <!-- Product Details -->
                        <div class="mb-6 space-y-2">
                            <div class="flex items-center text-sm">
                                <span class="text-gray-500 w-24">Código:</span>
                                <span class="font-medium text-gray-900">{{ $product->code }}</span>
                            </div>
                            @if($product->brand)
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-500 w-24">Marca:</span>
                                    <span class="font-medium text-gray-900">{{ $product->brand->name ?? $product->brand }}</span>
                                </div>
                            @endif
                            @if($product->track_inventory)
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-500 w-24">Stock:</span>
                                    <span class="font-medium {{ $product->current_stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $product->current_stock > 0 ? $product->current_stock . ' unidades disponibles' : 'Sin stock' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Add to Cart Form -->
                    <form action="{{ route('cart.add') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <!-- Quantity Selector -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad:</label>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="decreaseQuantity()"
                                    class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                    </svg>
                                </button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1"
                                    class="w-20 text-center border border-gray-300 rounded-lg py-2 focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                <button type="button" onclick="increaseQuantity()"
                                    class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button type="submit"
                                class="flex-1 bg-gradient-to-r from-pink-500 to-rose-500 text-white font-semibold py-3 px-6 rounded-lg hover:from-pink-600 hover:to-rose-600 transition shadow-md hover:shadow-lg">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Agregar al carrito
                                </span>
                            </button>
                            <a href="{{ route('cart.index') }}"
                                class="flex items-center justify-center px-6 py-3 border-2 border-pink-500 text-pink-600 font-semibold rounded-lg hover:bg-pink-50 transition">
                                Ver carrito
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Related Products -->
            @if($related->count() > 0)
                <div class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Productos relacionados</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                        @foreach($related as $relatedProduct)
                            <a href="{{ route('shop.product', $relatedProduct->id) }}"
                               class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition group">
                                <div class="aspect-square bg-gray-100 overflow-hidden">
                                    <img
                                        src="{{ $relatedProduct->image_path && file_exists(storage_path('app/public/' . $relatedProduct->image_path)) ? asset('storage/' . $relatedProduct->image_path) : asset('images/no-image.png') }}"
                                        alt="{{ $relatedProduct->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                        onerror="this.src='{{ asset('images/no-image.png') }}';"
                                    >
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 text-sm md:text-base">
                                        {{ $relatedProduct->name }}
                                    </h3>
                                    <p class="text-pink-600 font-bold text-lg">
                                        S/ {{ number_format($relatedProduct->sale_price ?? $relatedProduct->unit_price, 2) }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('partials.footer')

    <script>
        function increaseQuantity() {
            const input = document.getElementById('quantity');
            input.value = parseInt(input.value) + 1;
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }
    </script>
</body>
</html>
