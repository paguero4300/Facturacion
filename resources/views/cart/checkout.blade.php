@extends('layouts.app')

@section('title', 'Finalizar Pedido - Detalles y Más')

@section('content')
    <!-- Spacer for fixed header -->
    <div class="h-20"></div>

    <!-- Progress Indicator -->
    <x-checkout-progress currentStep="2" />

    <!-- Page Header -->
    <div class="bg-gray-50 py-8 border-b border-gray-200">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Finalizar Pedido</h1>
            <p class="text-gray-600">Completa tu información para procesar el pedido</p>
        </div>
    </div>

    <!-- Checkout Content -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            @if(session('error'))
                <div class="mb-6 bg-red-50 border-2 border-red-200 text-red-700 px-6 py-4 rounded-xl shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-50 border-2 border-red-200 text-red-700 px-6 py-4 rounded-xl shadow-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @guest
                <div class="mb-6 bg-blue-50 border border-[var(--azul-claro)] rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="bg-white p-2 rounded-full shadow-sm">
                            <svg class="w-5 h-5 text-[var(--azul-primario)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[var(--enlaces-titulos)]">¿Ya tienes cuenta?</h3>
                            <p class="text-sm text-[var(--texto-principal)]">Inicia sesión para agilizar tu compra o continúa como invitado.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-[var(--azul-primario)] hover:underline">Iniciar Sesión</a>
                        <span class="text-gray-300">|</span>
                        <a href="{{ route('register') }}" class="text-sm font-semibold text-[var(--azul-primario)] hover:underline">Crear Cuenta</a>
                    </div>
                </div>
            @else
                <div class="mb-6 bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-300 rounded-2xl p-5 shadow-md">
                    <div class="flex items-center gap-3">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-800">
                            <span class="font-semibold">Has iniciado sesión como:</span> {{ Auth::user()->name }}
                        </p>
                    </div>
                </div>
            @endguest

            <form action="{{ route('checkout.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Checkout Form -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        {{-- 1️⃣ TUS DATOS --}}
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow">
                            <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-2 flex items-center gap-3">
                                <svg class="w-7 h-7 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                1️⃣ Tus Datos
                            </h2>
                            <p class="text-sm text-gray-600 mb-6">Información de quien realiza el pedido</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                        Nombre completo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="client_name" value="{{ old('client_name') }}" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                        placeholder="Ej: Juan Pérez García">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                        Teléfono <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="client_phone" value="{{ old('client_phone') }}" required
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                        placeholder="Ej: 987654321">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                        Email
                                    </label>
                                    <input type="email" name="client_email" value="{{ old('client_email') }}"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                        placeholder="correo@ejemplo.com">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <p class="text-xs text-gray-500 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        Usaremos tu email solo para enviarte la confirmación y actualizaciones de tu pedido
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- 2️⃣ ¿RECOJO O DELIVERY? --}}
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow">
                            <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-2 flex items-center gap-3">
                                <svg class="w-7 h-7 text-[var(--azul-claro)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                                2️⃣ ¿Cómo quieres recibir tu pedido?
                            </h2>
                            <p class="text-sm text-gray-600 mb-6">Elige si lo recoges en tienda o te lo enviamos</p>

                            <div class="space-y-4">
                                {{-- RECOJO EN TIENDA --}}
                                <label class="relative flex items-start p-6 border-2 rounded-xl cursor-pointer transition-all group {{ old('delivery_choice', 'pickup') == 'pickup' ? 'border-[var(--naranja)] bg-orange-50' : 'border-gray-200' }}" id="pickup_label">
                                    <input type="radio" name="delivery_choice" value="pickup" 
                                           {{ old('delivery_choice', 'pickup') == 'pickup' ? 'checked' : '' }}
                                           class="mt-1 w-5 h-5 text-[var(--naranja)] focus:ring-[var(--naranja)]"
                                           onchange="toggleDeliveryType()">
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-lg font-bold text-gray-900">🏪 Recojo en Tienda</span>
                                            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">GRATIS</span>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            Recoge tu pedido en nuestra tienda<br>
                                            🕐 Lunes a Sábado: 9:00 AM - 8:00 PM
                                        </p>
                                    </div>
                                </label>

                                {{-- DELIVERY --}}
                                <label class="relative flex items-start p-6 border-2 rounded-xl cursor-pointer transition-all group {{ old('delivery_choice', 'pickup') == 'delivery' ? 'border-[var(--azul-claro)] bg-blue-50' : 'border-gray-200 hover:border-[var(--azul-claro)]' }}" id="delivery_label">
                                    <input type="radio" name="delivery_choice" value="delivery"
                                           {{ old('delivery_choice') == 'delivery' ? 'checked' : '' }}
                                           class="mt-1 w-5 h-5 text-[var(--azul-claro)] focus:ring-[var(--azul-claro)]"
                                           onchange="toggleDeliveryType()">
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-lg font-bold text-gray-900">🚚 Delivery a Domicilio</span>
                                            <span class="px-3 py-1 bg-orange-100 text-orange-700 text-xs font-bold rounded-full">Costo adicional</span>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            Te lo enviamos a tu domicilio
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- CAMPOS RECOJO --}}
                        <div id="pickup_fields" class="space-y-6">
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow">
                                <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-6 flex items-center gap-3">
                                    <svg class="w-7 h-7 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    ¿Cuándo recogerás tu pedido?
                                </h2>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Fecha de recojo
                                        </label>
                                        <input type="date" name="delivery_date"
                                            id="pickup_date"
                                            value="{{ old('delivery_date') }}"
                                            min="{{ $minDeliveryDate }}"
                                            max="{{ $maxDeliveryDate }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                            onchange="updateAvailableTimeSlots()">
                                    </div>

                                    <div id="pickup_time_container">
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Horario de recojo
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            @foreach($deliveryTimeSlots as $value => $label)
                                                <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[var(--naranja)] transition pickup-time-option" data-slot="{{ $value }}">
                                                    <input type="radio" name="delivery_time_slot" value="{{ $value }}"
                                                           {{ old('delivery_time_slot') == $value ? 'checked' : '' }}
                                                           class="w-4 h-4 text-[var(--naranja)] focus:ring-[var(--naranja)]">
                                                    <span class="ml-2 text-sm font-medium">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            💌 Dedicatoria (Opcional)
                                        </label>
                                        <textarea name="observations" rows="3"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                            placeholder="Escribe tu dedicatoria aquí...">{{ old('observations') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- CAMPOS DELIVERY --}}
                        <div id="delivery_fields" class="space-y-6" style="display: none;">
                            {{-- AVISO GRANDE DELIVERY --}}
                            {{-- AVISO GRANDE DELIVERY --}}
                            <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded-r-lg shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-bold text-orange-900">⚠️ DELIVERY TIENE COSTO ADICIONAL</h3>
                                        <p class="text-sm text-orange-800 mt-1">
                                            El costo varía según el distrito. <strong>Nos contactaremos contigo</strong> (WhatsApp/Llamada) para confirmar el monto exacto antes del pago.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Datos de entrega --}}
                            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow">
                                <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-6 flex items-center gap-3">
                                    <svg class="w-7 h-7 text-[var(--azul-claro)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    ¿A dónde lo enviamos?
                                </h2>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-1">
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Distrito <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="client_district" value="{{ old('client_district') }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Ej: San Isidro">
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Dirección completa <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="client_address" rows="2"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Ej: Av. Los Rosales 456, Dpto. 302">{{ old('client_address') }}</textarea>
                                    </div>

                                    <div class="md:col-span-1">
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Fecha de entrega
                                        </label>
                                        <input type="date" name="delivery_date"
                                            id="delivery_date_input"
                                            value="{{ old('delivery_date') }}"
                                            min="{{ $minDeliveryDate }}"
                                            max="{{ $maxDeliveryDate }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            onchange="updateAvailableTimeSlots()">
                                    </div>

                                    <div class="md:col-span-2" id="delivery_time_container">
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Horario preferido
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            @foreach($deliveryTimeSlots as $value => $label)
                                                <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[var(--azul-claro)] transition delivery-time-option" data-slot="{{ $value }}">
                                                    <input type="radio" name="delivery_time_slot" value="{{ $value }}"
                                                           {{ old('delivery_time_slot') == $value ? 'checked' : '' }}
                                                           class="w-4 h-4 text-[var(--azul-claro)] focus:ring-[var(--azul-claro)]">
                                                    <span class="ml-2 text-sm font-medium">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="md:col-span-2 pt-4 border-t border-gray-100">
                                        <h3 class="text-lg font-bold text-[var(--enlaces-titulos)] mb-4">¿Quién recibirá el pedido?</h3>
                                    </div>

                                    <div class="md:col-span-1">
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Nombre de quien recibe <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="recipient_name" value="{{ old('recipient_name') }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Ej: María González">
                                    </div>

                                    <div class="md:col-span-1">
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Teléfono de quien recibe <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel" name="recipient_phone" value="{{ old('recipient_phone') }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Ej: 987654321">
                                        <p class="text-xs text-gray-500 mt-1">Para coordinar la entrega</p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            💌 Dedicatoria (Opcional)
                                        </label>
                                        <textarea name="observations" rows="3"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Escribe tu dedicatoria aquí...">{{ old('observations') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow">
                            <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-6 flex items-center gap-3">
                                <svg class="w-7 h-7 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                3️⃣ Método de Pago
                            </h2>

                            <!-- Payment Options Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <!-- Yape -->
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="payment_method" value="yape" class="peer sr-only" onchange="togglePaymentFields()" {{ old('payment_method') == 'yape' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-xl border-2 border-gray-200 hover:border-[var(--naranja)] peer-checked:border-[var(--naranja)] peer-checked:bg-orange-50 transition-all h-full flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 group-hover:text-[var(--naranja)] transition-colors">Yape</h3>
                                            <p class="text-xs text-gray-500">Pago rápido con QR/Número</p>
                                        </div>
                                        <div class="ml-auto opacity-0 peer-checked:opacity-100 transition-opacity">
                                            <svg class="w-6 h-6 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </label>

                                <!-- Plin (Disabled) -->
                                <div class="relative opacity-60 cursor-not-allowed">
                                    <div class="p-4 rounded-xl border-2 border-gray-100 bg-gray-50 h-full flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-400">Plin</h3>
                                            <span class="inline-block px-2 py-0.5 bg-gray-200 text-gray-500 text-[10px] font-bold rounded-full uppercase tracking-wider">Pronto</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transferencia -->
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="payment_method" value="transfer" class="peer sr-only" onchange="togglePaymentFields()" {{ old('payment_method') == 'transfer' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-xl border-2 border-gray-200 hover:border-[var(--azul-primario)] peer-checked:border-[var(--azul-primario)] peer-checked:bg-blue-50 transition-all h-full flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-[var(--azul-primario)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 group-hover:text-[var(--azul-primario)] transition-colors">Transferencia</h3>
                                            <p class="text-xs text-gray-500">BCP, Interbank, BBVA</p>
                                        </div>
                                        <div class="ml-auto opacity-0 peer-checked:opacity-100 transition-opacity">
                                            <svg class="w-6 h-6 text-[var(--azul-primario)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </label>

                                <!-- Tarjeta -->
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="payment_method" value="card" class="peer sr-only" onchange="togglePaymentFields()" {{ old('payment_method') == 'card' ? 'checked' : '' }}>
                                    <div class="p-4 rounded-xl border-2 border-gray-200 hover:border-[var(--naranja)] peer-checked:border-[var(--naranja)] peer-checked:bg-orange-50 transition-all h-full flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-gray-900 group-hover:text-[var(--naranja)] transition-colors">Tarjeta</h3>
                                            <p class="text-xs text-gray-500">Link de pago seguro</p>
                                        </div>
                                        <div class="ml-auto opacity-0 peer-checked:opacity-100 transition-opacity">
                                            <svg class="w-6 h-6 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Payment Details Sections -->
                            <div class="space-y-4">
                                <!-- Yape Fields -->
                                <div id="yape-fields" class="payment-fields p-6 bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-100 rounded-xl shadow-sm" style="display: none;">
                                    <div class="flex flex-col md:flex-row gap-6 items-center mb-6">
                                        <div class="bg-white p-4 rounded-xl shadow-sm text-center min-w-[160px]">
                                            <div class="w-32 h-32 mx-auto bg-gray-200 rounded-lg flex items-center justify-center mb-2">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                                </svg>
                                            </div>
                                            <p class="font-bold text-purple-900 text-lg">941 492 316</p>
                                            <p class="text-xs text-purple-600">DETALLESYMASFLORES SAC</p>
                                        </div>
                                        <div class="flex-1 space-y-2">
                                            <h4 class="font-bold text-purple-900 text-lg">¿Cómo pagar con Yape?</h4>
                                            <ol class="list-decimal list-inside text-sm text-purple-800 space-y-1">
                                                <li>Abre tu app <strong>Yape</strong></li>
                                                <li>Escanea el QR o yapea al número mostrado</li>
                                                <li>Ingresa el monto total de tu pedido</li>
                                                <li>Confirma el pago y <strong>guarda la captura</strong></li>
                                                <li>Sube la captura en el campo de abajo 👇</li>
                                            </ol>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                Número de operación <span class="text-gray-400 text-xs">(Opcional)</span>
                                            </label>
                                            <input type="text" name="payment_operation_number" value="{{ old('payment_operation_number') }}"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                                placeholder="Ej: 123456">
                                            @error('payment_operation_number')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                Tu número de celular
                                            </label>
                                            <input type="text" name="client_payment_phone" value="{{ old('client_payment_phone') }}"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                                placeholder="Desde donde yapeaste">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                Comprobante de pago (Captura) <span class="text-gray-400 text-xs">(Opcional)</span>
                                            </label>
                                            <input type="file" name="payment_evidence" accept=".jpg,.jpeg,.png,.pdf"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all bg-white">
                                            @error('payment_evidence')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                            <p class="text-xs text-gray-500 mt-2 flex items-start gap-1">
                                                <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>Puedes completar tu pedido ahora y enviar el comprobante más tarde por WhatsApp si lo prefieres.</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>


                                <!-- Transfer Fields -->
                                <div id="transfer-fields" class="payment-fields p-6 bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-100 rounded-xl shadow-sm" style="display: none;">
                                    <div class="bg-white p-5 rounded-xl shadow-sm mb-6 border-l-4 border-[var(--azul-primario)]">
                                        <h4 class="font-bold text-[var(--azul-primario)] mb-3 flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            Datos Bancarios (BCP)
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-500">Titular</p>
                                                <p class="font-bold text-gray-900">DETALLESYMASFLORES SAC</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Cuenta Soles</p>
                                                <p class="font-bold text-gray-900 font-mono">355-7129566-0-74</p>
                                            </div>
                                            <div class="md:col-span-2">
                                                <p class="text-gray-500">CCI (Interbancario)</p>
                                                <p class="font-bold text-gray-900 font-mono">002-355-007129566074-61</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                Número de operación <span class="text-gray-400 text-xs">(Opcional)</span>
                                            </label>
                                            <input type="text" name="payment_operation_number" value="{{ old('payment_operation_number') }}"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-primario)] focus:border-[var(--azul-primario)] transition-all"
                                                placeholder="Ej: 1234567">
                                            @error('payment_operation_number')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                Comprobante de transferencia <span class="text-gray-400 text-xs">(Opcional)</span>
                                            </label>
                                            <input type="file" name="payment_evidence" accept=".jpg,.jpeg,.png,.pdf"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-primario)] focus:border-[var(--azul-primario)] transition-all bg-white">
                                            @error('payment_evidence')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                            <p class="text-xs text-gray-500 mt-2 flex items-start gap-1">
                                                <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>Puedes completar tu pedido ahora y enviar el comprobante más tarde por WhatsApp si lo prefieres.</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Fields -->
                                <div id="card-fields" class="payment-fields p-6 bg-gradient-to-br from-orange-50 to-yellow-50 border border-orange-100 rounded-xl shadow-sm" style="display: none;">
                                    <div class="flex items-start gap-4">
                                        <div class="bg-white p-3 rounded-full shadow-sm text-orange-500">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-bold text-orange-900 text-lg mb-2">Solicitar Link de Pago</h4>
                                            <p class="text-sm text-orange-800 mb-4">
                                                Para tu seguridad, generaremos un <strong>link de pago único</strong> para ti. Te lo enviaremos por WhatsApp para que puedas pagar con cualquier tarjeta (Visa, Mastercard, Amex) de forma segura.
                                            </p>
                                            <a href="https://wa.me/51941492316?text=Hola,%20quiero%20pagar%20mi%20pedido%20con%20tarjeta" target="_blank"
                                               class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-md hover:shadow-lg">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                                Solicitar Link por WhatsApp
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campo de referencia general (solo visible para efectivo y tarjeta) -->
                            <div id="general-reference-field" class="mt-4" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de referencia (opcional)
                                </label>
                                <input type="text" name="payment_reference" value="{{ old('payment_reference') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                    placeholder="Ej: REF-123456">
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 sticky top-24 hover:shadow-xl transition-shadow">
                            <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-6">Resumen del Pedido</h2>

                            <div class="space-y-3 mb-6">
                                @foreach($cart as $item)
                                    <div class="flex gap-3 pb-3 border-b border-gray-100">
                                        <div class="w-16 h-16 bg-gray-50 rounded-xl overflow-hidden flex-shrink-0 border border-gray-100">
                                            <img
                                                src="{{ $item['image'] && file_exists(storage_path('app/public/' . $item['image'])) ? asset('storage/' . $item['image']) : asset('images/no-image.png') }}"
                                                alt="{{ $item['name'] }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.src='{{ asset('images/no-image.png') }}';"
                                            >
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-[var(--enlaces-titulos)] text-sm line-clamp-2">{{ $item['name'] }}</h3>
                                            <p class="text-sm text-[var(--texto-principal)] mt-1">
                                                {{ $item['quantity'] }} x S/ {{ number_format($item['price'], 2) }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-[var(--enlaces-titulos)]">
                                                S/ {{ number_format($item['price'] * $item['quantity'], 2) }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="space-y-3 pt-4 border-t-2 border-gray-100">
                                <div class="flex justify-between text-base">
                                    <span class="text-[var(--texto-principal)] font-medium">Subtotal:</span>
                                    <span class="font-semibold text-[var(--enlaces-titulos)]">S/ {{ number_format($total, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-base">
                                    <span class="text-[var(--texto-principal)] font-medium">Envío:</span>
                                    <span class="font-semibold text-[var(--enlaces-titulos)]">A coordinar</span>
                                </div>
                                <div class="flex justify-between text-xl font-bold pt-3 border-t-2 border-gray-100">
                                    <span class="text-[var(--enlaces-titulos)]">Total:</span>
                                    <span class="bg-gradient-to-r from-[var(--naranja)] to-[var(--azul-claro)] bg-clip-text text-transparent">S/ {{ number_format($total, 2) }}</span>
                                </div>
                            </div>

                            {{-- Términos y Condiciones --}}
                            <div class="mt-6 pt-6 border-t-2 border-gray-100">
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="checkbox" name="accept_terms" value="1" 
                                           {{ old('accept_terms') ? 'checked' : '' }}
                                           class="mt-1 w-5 h-5 text-[var(--naranja)] focus:ring-[var(--naranja)] rounded border-gray-300">
                                    <span class="text-sm text-gray-700">
                                        Acepto los 
                                        <a href="{{ route('legal.terms') }}" target="_blank" class="text-[var(--azul-primario)] hover:text-[var(--naranja)] font-semibold underline">
                                            términos y condiciones
                                        </a>
                                        y la
                                        <a href="{{ route('legal.privacy') }}" target="_blank" class="text-[var(--azul-primario)] hover:text-[var(--naranja)] font-semibold underline">
                                            política de privacidad
                                        </a>
                                        <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                @error('accept_terms')
                                    <p class="text-red-500 text-xs mt-2 ml-8">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit"
                                class="w-full mt-6 bg-gradient-to-r from-[var(--naranja)] to-[var(--azul-claro)] text-white font-bold py-4 px-6 rounded-xl hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg">
                                Confirmar Pedido
                            </button>

                            <a href="{{ route('cart.index') }}"
                                class="block text-center mt-4 text-[var(--azul-primario)] hover:text-[var(--azul-claro)] font-semibold transition-colors">
                                ← Volver al carrito
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateAvailableTimeSlots() {
            const deliveryChoice = document.querySelector('input[name="delivery_choice"]:checked')?.value;
            
            let dateInput, timeContainer, timeOptions;
            
            if (deliveryChoice === 'pickup') {
                dateInput = document.getElementById('pickup_date');
                timeContainer = document.getElementById('pickup_time_container');
                timeOptions = document.querySelectorAll('.pickup-time-option');
            } else {
                dateInput = document.getElementById('delivery_date_input');
                timeContainer = document.getElementById('delivery_time_container');
                timeOptions = document.querySelectorAll('.delivery-time-option');
            }

            if (!dateInput || !timeContainer) return;

            if (!dateInput.value) {
                // Don't hide container, just reset
                return;
            }

            const selectedDate = new Date(dateInput.value);
            const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, etc.

            // Reset all time slots
            timeOptions.forEach(option => {
                option.style.display = 'flex'; // Restore display
                option.classList.remove('opacity-50', 'cursor-not-allowed');
                const radio = option.querySelector('input[type="radio"]');
                if(radio) radio.disabled = false;
            });

            // Sunday - disable all time slots and show message
            if (dayOfWeek === 0) { // 0 is Sunday in JS getDay() (but check timezone issues, better to use ISO string)
                // Actually, let's rely on the input value string "YYYY-MM-DD" to avoid timezone issues
                const dateParts = dateInput.value.split('-');
                const dateObj = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                if (dateObj.getDay() === 0) {
                    alert('Las entregas no están disponibles los domingos. Por favor selecciona otro día.');
                    dateInput.value = ''; // Clear invalid date
                }
                 // Saturday - hide evening slot
            if (dayOfWeek === 6) {
                // Use timeOptions which is already scoped to the correct container
                timeOptions.forEach(option => {
                    if (option.dataset.slot === 'evening') {
                        option.style.display = 'none';
                        const radio = option.querySelector('input[type="radio"]');
                        if(radio) {
                            radio.disabled = true;
                            radio.checked = false;
                        }
                    }
                });
            }
            }
        }

        // Toggle between pickup and delivery fields
        function toggleDeliveryType() {
            const deliveryChoice = document.querySelector('input[name="delivery_choice"]:checked')?.value;
            const pickupFields = document.getElementById('pickup_fields');
            const deliveryFields = document.getElementById('delivery_fields');
            const pickupLabel = document.getElementById('pickup_label');
            const deliveryLabel = document.getElementById('delivery_label');
            
            if (deliveryChoice === 'pickup') {
                // Show pickup fields, hide delivery fields
                if (pickupFields) {
                    pickupFields.style.display = 'block';
                    // Enable inputs
                    pickupFields.querySelectorAll('input, textarea, select').forEach(el => el.disabled = false);
                }
                if (deliveryFields) {
                    deliveryFields.style.display = 'none';
                    // Disable inputs
                    deliveryFields.querySelectorAll('input, textarea, select').forEach(el => el.disabled = true);
                }
                
                // Update label styles
                if (pickupLabel) {
                    pickupLabel.classList.add('border-[var(--naranja)]', 'bg-orange-50');
                    pickupLabel.classList.remove('border-gray-200');
                }
                if (deliveryLabel) {
                    deliveryLabel.classList.remove('border-[var(--azul-claro)]', 'bg-blue-50');
                    deliveryLabel.classList.add('border-gray-200');
                }
                
            } else if (deliveryChoice === 'delivery') {
                // Show delivery fields, hide pickup fields
                if (pickupFields) {
                    pickupFields.style.display = 'none';
                    // Disable inputs
                    pickupFields.querySelectorAll('input, textarea, select').forEach(el => el.disabled = true);
                }
                if (deliveryFields) {
                    deliveryFields.style.display = 'block';
                    // Enable inputs
                    deliveryFields.querySelectorAll('input, textarea, select').forEach(el => el.disabled = false);
                }
                
                // Update label styles
                if (pickupLabel) {
                    pickupLabel.classList.remove('border-[var(--naranja)]', 'bg-orange-50');
                    pickupLabel.classList.add('border-gray-200');
                }
                if (deliveryLabel) {
                    deliveryLabel.classList.add('border-[var(--azul-claro)]', 'bg-blue-50');
                    deliveryLabel.classList.remove('border-gray-200');
                }
                
                // Enable delivery field validations
                const requiredDeliveryFields = ['client_address', 'client_district', 'recipient_name', 'recipient_phone'];
                requiredDeliveryFields.forEach(fieldName => {
                    const field = document.querySelector(`[name="${fieldName}"]`);
                    if (field && deliveryFields.contains(field)) {
                        field.setAttribute('required', 'required');
                    }
                });
            }
            
            // Update time slots availability based on the active date input
            updateAvailableTimeSlots();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleDeliveryType(); // Initialize pickup/delivery fields
            updateAvailableTimeSlots();
            togglePaymentFields(); // Initialize payment fields
        });

        function togglePaymentFields() {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

            // Hide all payment fields and DISABLE their inputs
            document.querySelectorAll('.payment-fields').forEach(field => {
                field.style.display = 'none';
                // Disable all inputs inside hidden payment fields
                field.querySelectorAll('input, textarea, select').forEach(input => {
                    input.disabled = true;
                });
            });

            // Hide general reference field
            const generalRefField = document.getElementById('general-reference-field');
            if (generalRefField) {
                generalRefField.style.display = 'none';
            }

            // Show specific fields based on selected method and ENABLE their inputs
            if (selectedMethod) {
                const specificFields = document.getElementById(selectedMethod + '-fields');
                if (specificFields) {
                    specificFields.style.display = 'block';
                    // Enable all inputs inside visible payment fields
                    specificFields.querySelectorAll('input, textarea, select').forEach(input => {
                        input.disabled = false;
                    });
                } else if (['cash', 'card'].includes(selectedMethod)) {
                    // Show general reference field for cash and card
                    if (generalRefField) {
                        generalRefField.style.display = 'block';
                    }
                }
            }
        }
    </script>
    @endpush
@endsection
