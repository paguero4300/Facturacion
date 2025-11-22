@extends('layouts.app')

@section('title', 'Pedido Confirmado - Detalles y Más')

@section('content')
    {{-- Spacer for fixed header --}}
    <div class="h-20"></div>
    
    {{-- Progress Indicator --}}
    <x-checkout-progress :currentStep="3" />

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-orange-50 to-blue-50 py-12 border-b border-gray-200">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg animate-bounce">
                    <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-[var(--enlaces-titulos)] mb-3">¡Pedido Confirmado!</h1>
                <p class="text-lg text-[var(--texto-principal)] max-w-2xl mx-auto">
                    Tu pedido ha sido registrado exitosamente. Nos pondremos en contacto contigo pronto para coordinar la entrega.
                </p>
            </div>
        </div>
    </div>


    <!-- Confirmation Content -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Order Number Badge -->
            <div class="bg-white rounded-2xl shadow-lg p-6 text-center mb-8 border-2 border-[var(--naranja)]">
                <p class="text-sm text-[var(--texto-principal)] mb-2">Número de Pedido</p>
                <p class="text-3xl font-bold bg-gradient-to-r from-[var(--naranja)] to-[var(--azul-claro)] bg-clip-text text-transparent">
                    {{ $invoice->full_number }}
                </p>
            </div>

            <!-- Order Details -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-[var(--naranja)] to-[var(--azul-claro)] text-white px-6 py-5">
                    <h2 class="text-2xl font-bold flex items-center gap-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Detalles del Pedido
                    </h2>
                </div>

                <div class="p-6 md:p-8">
                    <!-- Customer Information -->
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-[var(--enlaces-titulos)] mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Información del Cliente
                        </h3>
                        <div class="bg-gradient-to-br from-orange-50 to-blue-50 rounded-xl p-5 space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center">
                                <span class="text-[var(--texto-principal)] font-semibold w-32 mb-1 md:mb-0">Nombre:</span>
                                <span class="font-bold text-[var(--enlaces-titulos)]">{{ $invoice->client_business_name }}</span>
                            </div>
                            @if($invoice->client_email)
                                <div class="flex flex-col md:flex-row md:items-center">
                                    <span class="text-[var(--texto-principal)] font-semibold w-32 mb-1 md:mb-0">Email:</span>
                                    <span class="font-medium text-[var(--enlaces-titulos)]">{{ $invoice->client_email }}</span>
                                </div>
                            @endif
                            <div class="flex flex-col md:flex-row md:items-center">
                                <span class="text-[var(--texto-principal)] font-semibold w-32 mb-1 md:mb-0">Dirección:</span>
                                <span class="font-medium text-[var(--enlaces-titulos)]">{{ $invoice->client_address }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h3 class="text-xl font-bold text-[var(--enlaces-titulos)] mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-[var(--azul-claro)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Información de Pago
                        </h3>
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-5 space-y-3">
                            <div class="flex flex-col md:flex-row md:items-center">
                                <span class="text-[var(--texto-principal)] font-semibold w-32 mb-1 md:mb-0">Método:</span>
                                <span class="font-bold text-[var(--enlaces-titulos)]">
                                    @switch($invoice->payment_method)
                                        @case('cash')
                                            💵 Efectivo contra entrega
                                            @break
                                        @case('yape')
                                            📱 Yape
                                            @break
                                        @case('plin')
                                            💳 Plin
                                            @break
                                        @case('card')
                                            💳 Tarjeta de Crédito/Débito
                                            @break
                                        @case('transfer')
                                            🏦 Transferencia Bancaria
                                            @break
                                        @default
                                            {{ ucfirst($invoice->payment_method) }}
                                    @endswitch
                                </span>
                            </div>
                            @if($invoice->payment_reference)
                                <div class="flex flex-col md:flex-row md:items-center">
                                    <span class="text-[var(--texto-principal)] font-semibold w-32 mb-1 md:mb-0">Referencia:</span>
                                    <span class="font-medium text-[var(--enlaces-titulos)]">{{ $invoice->payment_reference }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-[var(--enlaces-titulos)] mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Productos
                        </h3>
                        <div class="space-y-4">
                            @foreach($invoice->details as $detail)
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:shadow-md transition-shadow">
                                    @if($detail->product && $detail->product->image_path)
                                        <div class="w-20 h-20 bg-white rounded-xl overflow-hidden flex-shrink-0 shadow-sm border border-gray-200">
                                            <img
                                                src="{{ asset('storage/' . $detail->product->image_path) }}"
                                                alt="{{ $detail->description }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.src='{{ asset('images/no-image.png') }}';"
                                            >
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h4 class="font-bold text-[var(--enlaces-titulos)] mb-1">{{ $detail->description }}</h4>
                                        <p class="text-sm text-[var(--texto-principal)]">
                                            Cantidad: <span class="font-semibold">{{ $detail->quantity }}</span> x 
                                            <span class="font-semibold">S/ {{ number_format($detail->unit_price, 2) }}</span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-bold text-[var(--naranja)]">
                                            S/ {{ number_format($detail->line_total, 2) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="bg-gradient-to-r from-orange-50 to-blue-50 rounded-xl p-6 border-2 border-[var(--naranja)]">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg text-[var(--texto-principal)] font-semibold">Subtotal:</span>
                            <span class="text-lg font-bold text-[var(--enlaces-titulos)]">S/ {{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-3 border-t-2 border-[var(--naranja)]">
                            <span class="text-2xl font-bold text-[var(--enlaces-titulos)]">Total:</span>
                            <span class="text-3xl font-bold bg-gradient-to-r from-[var(--naranja)] to-[var(--azul-claro)] bg-clip-text text-transparent">
                                S/ {{ number_format($invoice->total_amount, 2) }}
                            </span>
                        </div>
                    </div>

                    @if($invoice->observations)
                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <h3 class="text-lg font-bold text-[var(--enlaces-titulos)] mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[var(--azul-claro)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                                Dedicatoria
                            </h3>
                            <p class="text-[var(--texto-principal)] bg-gray-50 p-4 rounded-xl italic">"{{ $invoice->observations }}"</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                <a href="{{ route('shop.index') }}"
                    class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-[var(--naranja)] to-[var(--azul-claro)] text-white font-bold py-4 px-8 rounded-xl hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Seguir Comprando
                </a>

                @auth
                    <a href="{{ route('account.orders') }}"
                        class="inline-flex items-center justify-center gap-2 bg-white text-[var(--azul-primario)] font-bold py-4 px-8 rounded-xl border-2 border-[var(--azul-primario)] hover:bg-blue-50 transition-all duration-300 shadow-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Ver Mis Pedidos
                    </a>
                @else
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 bg-white text-[var(--azul-primario)] font-bold py-4 px-8 rounded-xl border-2 border-[var(--azul-primario)] hover:bg-blue-50 transition-all duration-300 shadow-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Crear Cuenta
                    </a>
                @endauth
            </div>

            <!-- Additional Information -->
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-[var(--azul-claro)] rounded-2xl p-6 mb-6 shadow-md">
                <div class="flex gap-4">
                    <svg class="w-8 h-8 text-[var(--azul-primario)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="font-bold text-[var(--enlaces-titulos)] text-lg mb-3">¿Qué sigue ahora?</h3>
                        <ul class="text-[var(--texto-principal)] space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-[var(--naranja)] font-bold">✓</span>
                                <span>Nuestro equipo revisará tu pedido inmediatamente</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[var(--naranja)] font-bold">✓</span>
                                <span>Nos pondremos en contacto contigo para coordinar la entrega</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-[var(--naranja)] font-bold">✓</span>
                                <span>Recibirás actualizaciones sobre el estado de tu pedido</span>
                            </li>
                            @auth
                                <li class="flex items-start gap-2">
                                    <span class="text-[var(--naranja)] font-bold">✓</span>
                                    <span>Puedes consultar el estado de tu pedido en <a href="{{ route('account.orders') }}" class="text-[var(--azul-primario)] font-semibold hover:underline">"Mis Pedidos"</a></span>
                                </li>
                            @else
                                <li class="flex items-start gap-2">
                                    <span class="text-[var(--naranja)] font-bold">✓</span>
                                    <span>Guarda el número de pedido <strong class="text-[var(--enlaces-titulos)]">{{ $invoice->full_number }}</strong> para tu referencia</span>
                                </li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>

            @guest
                <!-- Guest Account Creation Prompt -->
                <div class="bg-gradient-to-r from-orange-50 to-blue-50 border-2 border-[var(--naranja)] rounded-2xl p-6 shadow-lg">
                    <div class="flex gap-4">
                        <svg class="w-10 h-10 text-[var(--naranja)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-[var(--enlaces-titulos)] mb-3">¿Te gustaría crear una cuenta?</h3>
                            <p class="text-[var(--texto-principal)] mb-4">
                                Con una cuenta puedes disfrutar de beneficios exclusivos:
                            </p>
                            <ul class="text-[var(--texto-principal)] space-y-2 mb-5">
                                <li class="flex items-center gap-2">
                                    <span class="text-green-500 font-bold text-xl">✓</span>
                                    Ver historial completo de todos tus pedidos
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-500 font-bold text-xl">✓</span>
                                    Seguimiento en tiempo real del estado de tus compras
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-500 font-bold text-xl">✓</span>
                                    Checkout más rápido con información guardada
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="text-green-500 font-bold text-xl">✓</span>
                                    Recibir ofertas y promociones especiales
                                </li>
                            </ul>
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center gap-3 bg-gradient-to-r from-[var(--naranja)] to-[var(--azul-claro)] text-white font-bold px-8 py-3 rounded-xl hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Crear mi cuenta gratis
                            </a>
                        </div>
                    </div>
                </div>
            @endguest
        </div>
    </div>
@endsection
