@extends('layouts.app')

@section('title', 'Carrito de Compras - Detalles y Más')

@section('content')
    <!-- Page Header (Matching Shop Style) -->
    <div class="relative bg-gray-900 py-16 md:py-24 overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ asset('logos/herosection.png') }}" alt="Carrito Background" class="w-full h-full object-cover opacity-20">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-900/90 to-gray-900/50"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4 tracking-tight">
                TU <span class="text-[var(--naranja)]">CARRITO</span>
            </h1>
            <p class="text-xl text-gray-300 max-w-2xl mx-auto font-light">
                Revisa tus productos antes de finalizar tu pedido
            </p>
        </div>
    </div>

    <!-- Cart Content -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            @if(count($cart) > 0)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-gray-900 mb-4">Productos ({{ count($cart) }})</h2>
                                
                                <div class="space-y-4">
                                    @foreach($cart as $id => $item)
                                        <div class="flex gap-4 p-4 border border-gray-200 rounded-lg hover:shadow-md transition-all duration-300">
                                            <!-- Product Image -->
                                            <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                                <img
                                                    src="{{ $item['image'] && file_exists(storage_path('app/public/' . $item['image'])) ? asset('storage/' . $item['image']) : asset('images/no-image.png') }}"
                                                    alt="{{ $item['name'] }}"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.src='{{ asset('images/no-image.png') }}';"
                                                >
                                            </div>

                                            <!-- Product Info -->
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 mb-1">{{ $item['name'] }}</h3>
                                                <p class="text-[var(--naranja)] font-bold text-lg">S/ {{ number_format($item['price'], 2) }}</p>
                                                
                                                <!-- Quantity Controls -->
                                                <div class="flex items-center gap-3 mt-3">
                                                    <span class="text-sm text-gray-600">Cantidad:</span>
                                                    <form action="{{ route('cart.update') }}" method="POST" class="flex items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="product_id" value="{{ $id }}">
                                                        <input
                                                            type="number"
                                                            name="quantity"
                                                            value="{{ $item['quantity'] }}"
                                                            min="1"
                                                            class="w-16 px-2 py-1 border border-gray-300 rounded text-center focus:border-[var(--naranja)] focus:ring-1 focus:ring-[var(--naranja)] transition"
                                                            onchange="this.form.submit()"
                                                        >
                                                    </form>
                                                    
                                                    <!-- Remove Button -->
                                                    <form action="{{ route('cart.remove', $id) }}" method="POST" class="ml-auto">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="text-red-500 hover:text-red-700 transition p-2 hover:bg-red-50 rounded-lg"
                                                            title="Eliminar"
                                                        >
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Subtotal -->
                                            <div class="text-right">
                                                <p class="text-sm text-gray-600 mb-1">Subtotal</p>
                                                <p class="text-xl font-bold text-gray-900">S/ {{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Continue Shopping -->
                        <div class="mt-6">
                            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 text-[var(--naranja)] hover:text-gray-900 font-semibold transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Continuar comprando
                            </a>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Resumen del Pedido</h2>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span>S/ {{ number_format($total, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Envío</span>
                                    <span class="text-green-600">A calcular</span>
                                </div>
                                <div class="border-t pt-3 flex justify-between text-lg font-bold text-gray-900">
                                    <span>Total</span>
                                    <span class="text-[var(--naranja)]">S/ {{ number_format($total, 2) }}</span>
                                </div>
                            </div>

                            <a href="{{ route('checkout.index') }}"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 py-3 px-6 text-sm font-bold text-white hover:bg-[var(--naranja)] transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                            >
                                <i class="fas fa-lock"></i>
                                Proceder al Pago
                            </a>

                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-600 text-center flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Compra segura y protegida
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty Cart -->
                <div class="text-center py-20">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 max-w-lg mx-auto">
                        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Tu carrito está vacío</h3>
                        <p class="text-gray-500 mb-8">¡Agrega productos para comenzar tu compra!</p>
                        <a href="{{ route('shop.index') }}" 
                           class="inline-flex items-center justify-center px-8 py-3 text-base font-bold text-white bg-[var(--naranja)] rounded-full hover:bg-gray-900 border-2 border-transparent hover:border-[var(--naranja)] transition-all duration-300 shadow-md hover:shadow-lg">
                            <i class="fas fa-shopping-bag mr-2"></i>
                            Ir a la tienda
                        </a>
                    </div>
                </div>
            @endif
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
