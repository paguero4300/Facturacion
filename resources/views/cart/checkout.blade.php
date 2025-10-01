<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pedido - Tienda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body class="bg-gray-50">
    @include('partials.header')

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-pink-500 to-rose-500 text-white py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold">Finalizar Pedido</h1>
            <p class="text-base md:text-lg text-pink-100 mt-2">Completa tu información para procesar el pedido</p>
        </div>
    </div>

    <!-- Checkout Content -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Guest/Login Options -->
            @guest
                <div class="mb-6 bg-blue-50 border-2 border-blue-200 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-blue-900 mb-2">¿Tienes una cuenta?</h3>
                            <p class="text-blue-800 mb-4">
                                Inicia sesión para ver tu historial de pedidos y gestionar tu información más fácilmente.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    Iniciar Sesión
                                </a>
                                <a href="{{ route('register') }}"
                                    class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-blue-600 font-semibold px-6 py-2 rounded-lg border-2 border-blue-600 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    Crear Cuenta
                                </a>
                            </div>
                            <p class="text-sm text-blue-700 mt-3">
                                O continúa como <strong>invitado</strong> llenando el formulario a continuación 👇
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-800">
                            <span class="font-semibold">Has iniciado sesión como:</span> {{ Auth::user()->name }}
                        </p>
                    </div>
                </div>
            @endguest

            <form action="{{ route('checkout.process') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Checkout Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Personal Information -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Información Personal
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre completo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="client_name" value="{{ old('client_name') }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                        placeholder="Ej: Juan Pérez García">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Teléfono <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="client_phone" value="{{ old('client_phone') }}" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                        placeholder="Ej: 987654321">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Email
                                    </label>
                                    <input type="email" name="client_email" value="{{ old('client_email') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                        placeholder="correo@ejemplo.com">
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Dirección de Entrega
                            </h2>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Dirección completa <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="client_address" required rows="3"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                        placeholder="Ej: Av. Los Rosales 456, Dpto. 302">{{ old('client_address') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Distrito
                                    </label>
                                    <input type="text" name="client_district" value="{{ old('client_district') }}"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                        placeholder="Ej: San Isidro">
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                Método de Pago
                            </h2>

                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-300 transition">
                                    <input type="radio" name="payment_method" value="cash" {{ old('payment_method') == 'cash' ? 'checked' : '' }} required
                                        class="w-4 h-4 text-pink-600 focus:ring-pink-500">
                                    <span class="ml-3 font-medium text-gray-900">Efectivo contra entrega</span>
                                </label>

                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-300 transition">
                                    <input type="radio" name="payment_method" value="yape" {{ old('payment_method') == 'yape' ? 'checked' : '' }} required
                                        class="w-4 h-4 text-pink-600 focus:ring-pink-500">
                                    <span class="ml-3 font-medium text-gray-900">Yape</span>
                                </label>

                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-300 transition">
                                    <input type="radio" name="payment_method" value="plin" {{ old('payment_method') == 'plin' ? 'checked' : '' }} required
                                        class="w-4 h-4 text-pink-600 focus:ring-pink-500">
                                    <span class="ml-3 font-medium text-gray-900">Plin</span>
                                </label>

                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-300 transition">
                                    <input type="radio" name="payment_method" value="transfer" {{ old('payment_method') == 'transfer' ? 'checked' : '' }} required
                                        class="w-4 h-4 text-pink-600 focus:ring-pink-500">
                                    <span class="ml-3 font-medium text-gray-900">Transferencia Bancaria</span>
                                </label>

                                <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-300 transition">
                                    <input type="radio" name="payment_method" value="card" {{ old('payment_method') == 'card' ? 'checked' : '' }} required
                                        class="w-4 h-4 text-pink-600 focus:ring-pink-500">
                                    <span class="ml-3 font-medium text-gray-900">Tarjeta de Crédito/Débito</span>
                                </label>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de operación / Referencia (opcional)
                                </label>
                                <input type="text" name="payment_reference" value="{{ old('payment_reference') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                    placeholder="Ej: OP-123456">
                            </div>
                        </div>

                        <!-- Additional Notes -->
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Observaciones (opcional)</h2>
                            <textarea name="observations" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                placeholder="Agrega cualquier nota o instrucción especial para tu pedido...">{{ old('observations') }}</textarea>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Resumen del Pedido</h2>

                            <div class="space-y-3 mb-6">
                                @foreach($cart as $item)
                                    <div class="flex gap-3 pb-3 border-b border-gray-200">
                                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                            <img
                                                src="{{ $item['image'] && file_exists(storage_path('app/public/' . $item['image'])) ? asset('storage/' . $item['image']) : asset('images/no-image.png') }}"
                                                alt="{{ $item['name'] }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.src='{{ asset('images/no-image.png') }}';"
                                            >
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-medium text-gray-900 text-sm line-clamp-2">{{ $item['name'] }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $item['quantity'] }} x S/ {{ number_format($item['price'], 2) }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-gray-900">
                                                S/ {{ number_format($item['price'] * $item['quantity'], 2) }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="space-y-2 pt-4 border-t border-gray-200">
                                <div class="flex justify-between text-base">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium text-gray-900">S/ {{ number_format($total, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-base">
                                    <span class="text-gray-600">Envío:</span>
                                    <span class="font-medium text-gray-900">A coordinar</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                                    <span>Total:</span>
                                    <span class="text-pink-600">S/ {{ number_format($total, 2) }}</span>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full mt-6 bg-gradient-to-r from-pink-500 to-rose-500 text-white font-semibold py-3 px-6 rounded-lg hover:from-pink-600 hover:to-rose-600 transition shadow-md hover:shadow-lg">
                                Confirmar Pedido
                            </button>

                            <a href="{{ route('cart.index') }}"
                                class="block text-center mt-3 text-pink-600 hover:text-pink-700 font-medium">
                                Volver al carrito
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('partials.footer')
</body>
</html>
