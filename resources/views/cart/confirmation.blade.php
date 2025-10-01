<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Tienda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body class="bg-gray-50">
    @include('partials.header')

    <!-- Confirmation Content -->
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Success Message -->
            <div class="bg-white rounded-xl shadow-lg p-8 text-center mb-8">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">¡Pedido Confirmado!</h1>
                <p class="text-lg text-gray-600 mb-6">
                    Tu pedido ha sido registrado exitosamente. Nos pondremos en contacto contigo pronto para coordinar la entrega.
                </p>
                <div class="inline-flex items-center gap-2 bg-pink-50 text-pink-700 font-semibold px-6 py-3 rounded-lg">
                    <span class="text-sm text-pink-600">Número de Pedido:</span>
                    <span class="text-xl">{{ $invoice->full_number }}</span>
                </div>
            </div>

            <!-- Order Details -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-6 py-4">
                    <h2 class="text-xl font-bold">Detalles del Pedido</h2>
                </div>

                <div class="p-6">
                    <!-- Customer Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Información del Cliente
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <div class="flex">
                                <span class="text-gray-600 w-32">Nombre:</span>
                                <span class="font-medium text-gray-900">{{ $invoice->client_business_name }}</span>
                            </div>
                            @if($invoice->client_email)
                                <div class="flex">
                                    <span class="text-gray-600 w-32">Email:</span>
                                    <span class="font-medium text-gray-900">{{ $invoice->client_email }}</span>
                                </div>
                            @endif
                            <div class="flex">
                                <span class="text-gray-600 w-32">Dirección:</span>
                                <span class="font-medium text-gray-900">{{ $invoice->client_address }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            Información de Pago
                        </h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <div class="flex">
                                <span class="text-gray-600 w-32">Método:</span>
                                <span class="font-medium text-gray-900">
                                    @switch($invoice->payment_method)
                                        @case('cash')
                                            Efectivo contra entrega
                                            @break
                                        @case('yape')
                                            Yape
                                            @break
                                        @case('plin')
                                            Plin
                                            @break
                                        @case('card')
                                            Tarjeta de Crédito/Débito
                                            @break
                                        @case('transfer')
                                            Transferencia Bancaria
                                            @break
                                        @default
                                            {{ ucfirst($invoice->payment_method) }}
                                    @endswitch
                                </span>
                            </div>
                            @if($invoice->payment_reference)
                                <div class="flex">
                                    <span class="text-gray-600 w-32">Referencia:</span>
                                    <span class="font-medium text-gray-900">{{ $invoice->payment_reference }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Productos
                        </h3>
                        <div class="space-y-3">
                            @foreach($invoice->details as $detail)
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                    @if($detail->product && $detail->product->image_path)
                                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            <img
                                                src="{{ asset('storage/' . $detail->product->image_path) }}"
                                                alt="{{ $detail->description }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.src='{{ asset('images/no-image.png') }}';"
                                            >
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $detail->description }}</h4>
                                        <p class="text-sm text-gray-600">
                                            Cantidad: {{ $detail->quantity }} x S/ {{ number_format($detail->unit_price, 2) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">
                                            S/ {{ number_format($detail->line_total, 2) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium text-gray-900">S/ {{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xl font-bold">
                            <span>Total:</span>
                            <span class="text-pink-600">S/ {{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>

                    @if($invoice->observations)
                        <div class="mt-6 pt-6 border-t">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Observaciones</h3>
                            <p class="text-gray-600">{{ $invoice->observations }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('shop.index') }}"
                    class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-pink-500 to-rose-500 text-white font-semibold py-3 px-8 rounded-lg hover:from-pink-600 hover:to-rose-600 transition shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Seguir Comprando
                </a>

                @auth
                    <a href="{{ route('account.orders') }}"
                        class="inline-flex items-center justify-center gap-2 bg-white text-pink-600 font-semibold py-3 px-8 rounded-lg border-2 border-pink-500 hover:bg-pink-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Ver Mis Pedidos
                    </a>
                @else
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 bg-white text-pink-600 font-semibold py-3 px-8 rounded-lg border-2 border-pink-500 hover:bg-pink-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Crear Cuenta
                    </a>
                @endauth
            </div>

            <!-- Additional Information -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex gap-3">
                    <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-blue-900 mb-2">¿Qué sigue ahora?</h3>
                        <ul class="text-blue-800 space-y-1 text-sm">
                            <li>• Nuestro equipo revisará tu pedido</li>
                            <li>• Nos pondremos en contacto contigo para coordinar la entrega</li>
                            <li>• Recibirás actualizaciones sobre el estado de tu pedido</li>
                            @auth
                                <li>• Puedes consultar el estado de tu pedido en "Mis Pedidos"</li>
                            @else
                                <li>• Guarda el número de pedido <strong>{{ $invoice->full_number }}</strong> para tu referencia</li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>

            @guest
                <!-- Guest Account Creation Prompt -->
                <div class="mt-6 bg-gradient-to-r from-pink-50 to-rose-50 border-2 border-pink-200 rounded-lg p-6">
                    <div class="flex gap-4">
                        <svg class="w-8 h-8 text-pink-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-pink-900 mb-2">¿Te gustaría crear una cuenta?</h3>
                            <p class="text-pink-800 mb-3">
                                Con una cuenta puedes disfrutar de beneficios exclusivos:
                            </p>
                            <ul class="text-pink-800 space-y-1 text-sm mb-4">
                                <li>✓ Ver historial completo de todos tus pedidos</li>
                                <li>✓ Seguimiento en tiempo real del estado de tus compras</li>
                                <li>✓ Checkout más rápido con información guardada</li>
                                <li>✓ Recibir ofertas y promociones especiales</li>
                            </ul>
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-pink-500 to-rose-500 text-white font-semibold px-6 py-2 rounded-lg hover:from-pink-600 hover:to-rose-600 transition shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    @include('partials.footer')
</body>
</html>
