@extends('layouts.app')

@section('title', $product->name . ' - Tienda')

@section('content')
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <nav class="flex text-sm font-medium" aria-label="Breadcrumb">
                <a href="{{ route('shop.index') }}" class="text-gray-500 hover:text-[var(--naranja)] transition-colors">Tienda</a>
                @if($product->category)
                    <span class="mx-2 text-gray-300">/</span>
                    <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="text-gray-500 hover:text-[var(--naranja)] transition-colors">
                        {{ $product->category->name }}
                    </a>
                @endif
                <span class="mx-2 text-gray-300">/</span>
                <span class="text-gray-900">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    <!-- Product Detail -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6 lg:p-10">
                <!-- Product Image -->
                <div class="flex items-center justify-center bg-gray-50 rounded-xl p-4">
                    <div class="w-full max-w-lg aspect-square relative overflow-hidden rounded-lg bg-white shadow-sm">
                         @if($product->sale_price && $product->sale_price < $product->unit_price)
                            <span class="absolute left-4 top-4 z-10 rounded bg-red-500 px-3 py-1.5 text-sm font-bold text-white shadow-md">
                                -{{ round((($product->unit_price - $product->sale_price) / $product->unit_price) * 100) }}%
                            </span>
                        @endif
                        <img
                            src="{{ $product->image_path && file_exists(storage_path('app/public/' . $product->image_path)) ? asset('storage/' . $product->image_path) : asset('images/no-image.png') }}"
                            alt="{{ $product->name }}"
                            class="w-full h-full object-contain hover:scale-105 transition-transform duration-500"
                            onerror="this.src='{{ asset('images/no-image.png') }}';"
                        >
                    </div>
                </div>

                <!-- Product Info -->
                <div class="flex flex-col justify-center">
                    <div class="flex-1">
                        <!-- Category Badge -->
                        @if($product->category)
                            <span class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-wider text-[var(--naranja)] bg-orange-50 rounded-full mb-4">
                                {{ $product->category->name }}
                            </span>
                        @endif

                        <!-- Product Name -->
                        <h1 class="text-3xl lg:text-4xl font-black text-gray-900 mb-6 leading-tight">{{ $product->name }}</h1>

                        <!-- Price -->
                        <div class="mb-8 pb-8 border-b border-gray-100">
                            <div class="flex items-baseline gap-4">
                                <span class="text-4xl lg:text-5xl font-black text-[var(--naranja)]">
                                    S/ {{ number_format($product->sale_price ?? $product->unit_price, 2) }}
                                </span>
                                @if($product->sale_price && $product->unit_price != $product->sale_price)
                                    <span class="text-2xl text-gray-300 line-through font-medium">
                                        S/ {{ number_format($product->unit_price, 2) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        @if($product->description)
                            <div class="mb-8">
                                <h3 class="text-lg font-bold text-gray-900 mb-3">Descripción</h3>
                                <p class="text-gray-600 leading-relaxed text-lg">{{ $product->description }}</p>
                            </div>
                        @endif

                        <!-- Product Details -->
                        <div class="mb-8 space-y-3 bg-gray-50 p-5 rounded-xl">
                            <div class="flex items-center text-sm">
                                <span class="text-gray-500 w-32 font-medium">Código:</span>
                                <span class="font-bold text-gray-900">{{ $product->code }}</span>
                            </div>
                            @if($product->brand)
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-500 w-32 font-medium">Marca:</span>
                                    <span class="font-bold text-gray-900">{{ $product->brand->name ?? $product->brand }}</span>
                                </div>
                            @endif
                            @if($product->track_inventory)
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-500 w-32 font-medium">Disponibilidad:</span>
                                    <span class="font-bold {{ $product->current_stock > 0 ? 'text-green-600' : 'text-red-600' }} flex items-center gap-2">
                                        @if($product->current_stock > 0)
                                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                            {{ $product->current_stock }} unidades
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                            Agotado
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Add to Cart Form -->
                    <form action="{{ route('cart.add') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <!-- Quantity Selector -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3">Cantidad</label>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center border-2 border-gray-200 rounded-lg overflow-hidden">
                                    <button type="button" onclick="decreaseQuantity()"
                                        class="w-12 h-12 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 transition active:bg-gray-200">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1"
                                        class="w-16 text-center border-none py-2 font-bold text-lg text-gray-900 focus:ring-0 bg-white appearance-none">
                                    <button type="button" onclick="increaseQuantity()"
                                        class="w-12 h-12 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 transition active:bg-gray-200">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="submit" name="action" value="add_to_cart"
                                class="flex-1 bg-gray-900 text-white font-bold py-4 px-8 rounded-xl hover:bg-[var(--naranja)] transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center gap-3">
                                <i class="fas fa-shopping-cart"></i>
                                AÑADIR AL CARRITO
                            </button>
                            <button type="submit" name="action" value="buy_now"
                                class="flex-1 bg-[var(--naranja)] text-white font-bold py-4 px-8 rounded-xl hover:bg-gray-900 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center gap-3">
                                <i class="fas fa-bolt"></i>
                                COMPRAR AHORA
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Related Products -->
            @if($related->count() > 0)
                <div class="mt-16">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Productos relacionados</h2>
                        @if($product->category)
                            <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="text-[var(--naranja)] font-medium hover:underline">Ver más</a>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($related as $relatedProduct)
                            <div class="group relative flex flex-col overflow-hidden rounded-lg bg-white shadow-sm transition-all duration-300 hover:shadow-xl border border-gray-100">
                                <div class="relative aspect-square overflow-hidden bg-gray-100">
                                    <a href="{{ route('shop.product', $relatedProduct->id) }}" class="block h-full w-full">
                                        <img
                                            src="{{ $relatedProduct->image_path && file_exists(storage_path('app/public/' . $relatedProduct->image_path)) ? asset('storage/' . $relatedProduct->image_path) : asset('images/no-image.png') }}"
                                            alt="{{ $relatedProduct->name }}"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            loading="lazy"
                                            onerror="this.src='{{ asset('images/no-image.png') }}';"
                                        >
                                    </a>
                                </div>
                                <div class="p-4 flex-1 flex flex-col">
                                    <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 hover:text-[var(--naranja)] transition-colors">
                                        <a href="{{ route('shop.product', $relatedProduct->id) }}">
                                            {{ $relatedProduct->name }}
                                        </a>
                                    </h3>
                                    <div class="mt-auto pt-2">
                                        <span class="text-xl font-bold text-[var(--naranja)]">
                                            S/ {{ number_format($relatedProduct->sale_price ?? $relatedProduct->unit_price, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

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
@endsection
