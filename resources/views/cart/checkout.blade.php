@extends('layouts.app')

@section('title', 'Finalizar Pedido - Detalles y Más')

@section('content')
    <!-- Spacer for fixed header -->
    <div class="h-20"></div>

    <!-- Page Header -->
    <div class="relative bg-gradient-to-br from-[var(--fondo-principal)] via-white to-orange-50 py-12 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-96 h-96 bg-[var(--naranja)] rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-[var(--azul-claro)] rounded-full blur-3xl"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-[var(--enlaces-titulos)] mb-3">
                Finalizar Pedido
            </h1>
            <p class="text-base md:text-lg text-[var(--texto-principal)]">
                Completa tu información para procesar el pedido
            </p>
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

            <!-- Guest/Login Options -->
            @guest
                <div class="mb-6 bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-[var(--azul-claro)] rounded-2xl p-6 shadow-md">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-10 h-10 text-[var(--azul-primario)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-[var(--enlaces-titulos)] mb-2">¿Tienes una cuenta?</h3>
                            <p class="text-[var(--texto-principal)] mb-4">
                                Inicia sesión para ver tu historial de pedidos y gestionar tu información más fácilmente.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('login') }}"
                                    class="inline-flex items-center gap-2 bg-[var(--azul-primario)] hover:bg-[var(--azul-claro)] text-white font-semibold px-6 py-3 rounded-lg transition-all shadow-md hover:shadow-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    Iniciar Sesión
                                </a>
                                <a href="{{ route('register') }}"
                                    class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-[var(--azul-primario)] font-semibold px-6 py-3 rounded-lg border-2 border-[var(--azul-primario)] transition-all shadow-md hover:shadow-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    Crear Cuenta
                                </a>
                            </div>
                            <p class="text-sm text-[var(--texto-principal)] mt-3 font-medium">
                                O continúa como <strong class="text-[var(--enlaces-titulos)]">invitado</strong> llenando el formulario a continuación 👇
                            </p>
                        </div>
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
                                <label class="relative flex items-start p-6 border-2 rounded-xl cursor-pointer transition-all group border-[var(--naranja)] bg-orange-50" id="pickup_label">
                                    <input type="radio" name="delivery_choice" value="pickup" checked
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
                                <label class="relative flex items-start p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[var(--azul-claro)] transition-all group" id="delivery_label">
                                    <input type="radio" name="delivery_choice" value="delivery"
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
                                            value="{{ old('delivery_date') }}"
                                            min="{{ $minDeliveryDate }}"
                                            max="{{ $maxDeliveryDate }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Horario de recojo
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            @foreach($deliveryTimeSlots as $value => $label)
                                                <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[var(--naranja)] transition">
                                                    <input type="radio" name="delivery_time_slot" value="{{ $value }}"
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
                            <div class="bg-gradient-to-r from-orange-50 to-red-50 border-4 border-orange-400 rounded-2xl p-8 shadow-xl">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <svg class="w-16 h-16 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-2xl font-black text-orange-900 mb-3">⚠️ DELIVERY TIENE COSTO ADICIONAL</h3>
                                        <div class="bg-white rounded-lg p-4 mb-3">
                                            <p class="text-orange-900 text-base font-semibold leading-relaxed">
                                                El costo del delivery <u>varía según el distrito</u> y será coordinado contigo después de confirmar tu pedido.
                                            </p>
                                        </div>
                                        <p class="text-orange-800 text-sm leading-relaxed">
                                            📱 <strong>Nos contactaremos contigo</strong> via WhatsApp o llamada para confirmar el <strong>monto exacto del envío</strong> antes de procesar tu pago.
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

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Distrito <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="client_district" value="{{ old('client_district') }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Ej: San Isidro">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Dirección completa <span class="text-red-500">*</span>
                                        </label>
                                        <textarea name="client_address" rows="2"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Ej: Av. Los Rosales 456, Dpto. 302">{{ old('client_address') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Fecha de entrega
                                        </label>
                                        <input type="date" name="delivery_date_delivery"
                                            value="{{ old('delivery_date') }}"
                                            min="{{ $minDeliveryDate }}"
                                            max="{{ $maxDeliveryDate }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Horario preferido
                                        </label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            @foreach($deliveryTimeSlots as $value => $label)
                                                <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[var(--azul-claro)] transition">
                                                    <input type="radio" name="delivery_time_slot_delivery" value="{{ $value }}"
                                                           class="w-4 h-4 text-[var(--azul-claro)] focus:ring-[var(--azul-claro)]">
                                                    <span class="ml-2 text-sm font-medium">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Nombre de quien recibe <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="recipient_name" value="{{ old('recipient_name') }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Ej: María González">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            Teléfono de quien recibe <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel" name="recipient_phone" value="{{ old('recipient_phone') }}"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Ej: 987654321">
                                        <p class="text-xs text-gray-500 mt-1">Para coordinar la entrega</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                            💌 Dedicatoria (Opcional)
                                        </label>
                                        <textarea name="observations_delivery" rows="3"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--azul-claro)] focus:border-[var(--azul-claro)] transition-all"
                                            placeholder="Escribe tu dedicatoria aquí...">{{ old('observations') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Observaciones antiguas --}}
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow" style="display: none;">
                            <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                Observaciones adicionales
                            </label>
                            <textarea name="delivery_notes" rows="2"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                placeholder="Cualquier indicación adicional">{{ old('delivery_notes') }}</textarea>
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
                                Método de Pago
                            </h2>

                            <div class="space-y-3">
                                <!-- Yape -->
                                <div class="payment-method-container">
                                    <label class="flex items-center p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[var(--naranja)] hover:bg-orange-50 transition-all payment-method-option" data-method="yape">
                                        <input type="radio" name="payment_method" value="yape" {{ old('payment_method') == 'yape' ? 'checked' : '' }} required
                                            class="w-5 h-5 text-[var(--naranja)] focus:ring-[var(--naranja)]" onchange="togglePaymentFields()">
                                        <span class="ml-3 font-semibold text-[var(--enlaces-titulos)]">Yape</span>
                                    </label>

                                    <!-- Campos específicos para Yape -->
                                    <div id="yape-fields" class="payment-fields mt-4 p-6 bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-xl shadow-sm" style="display: none;">
                                        <div class="mb-4 p-4 bg-white/80 backdrop-blur rounded-xl shadow-sm">
                                            <h4 class="font-bold text-purple-800 mb-3 flex items-center gap-2">
                                                <span class="text-2xl">📱</span> Datos para Yape:
                                            </h4>
                                            <p class="text-sm text-purple-700 font-medium">Número: <strong class="text-purple-900">941 492 316</strong></p>
                                            <p class="text-sm text-purple-700 font-medium">Nombre: <strong class="text-purple-900">DETALLESYMASFLORES SAC</strong></p>
                                            <p class="text-xs text-purple-600 mt-2">Realiza el pago y sube tu comprobante</p>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                    Número de operación <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" name="payment_operation_number" value="{{ old('payment_operation_number') }}"
                                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                                    placeholder="Ej: OP-123456789">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                    Tu número de Yape
                                                </label>
                                                <input type="text" name="client_payment_phone" value="{{ old('client_payment_phone') }}"
                                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                                    placeholder="Ej: 987654321">
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                Comprobante de pago <span class="text-red-500">*</span>
                                            </label>
                                            <input type="file" name="payment_evidence" accept=".jpg,.jpeg,.png,.pdf"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all">
                                            <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, PDF. Máximo 2MB</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Plin -->
                                <div class="payment-method-container">
                                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg opacity-50 cursor-not-allowed">
                                        <input type="radio" name="payment_method" value="plin" disabled
                                            class="w-4 h-4 text-pink-600 focus:ring-pink-500">
                                        <span class="ml-3 font-medium text-gray-900">Plin</span>
                                        <span class="ml-auto text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">No disponible aún</span>
                                    </label>
                                </div>

                                <!-- Transferencia Bancaria -->
                                <div class="payment-method-container">
                                    <label class="flex items-center p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[var(--azul-claro)] hover:bg-blue-50 transition-all payment-method-option" data-method="transfer">
                                        <input type="radio" name="payment_method" value="transfer" {{ old('payment_method') == 'transfer' ? 'checked' : '' }} required
                                            class="w-5 h-5 text-[var(--azul-primario)] focus:ring-[var(--azul-primario)]" onchange="togglePaymentFields()">
                                        <span class="ml-3 font-semibold text-[var(--enlaces-titulos)]">Transferencia Bancaria</span>
                                    </label>

                                    <!-- Campos específicos para Transferencia -->
                                    <div id="transfer-fields" class="payment-fields mt-4 p-6 bg-gradient-to-br from-green-50 to-teal-50 border-2 border-green-200 rounded-xl shadow-sm" style="display: none;">
                                        <div class="mb-4 p-4 bg-white/80 backdrop-blur rounded-xl shadow-sm">
                                            <h4 class="font-bold text-green-800 mb-3 flex items-center gap-2">
                                                <span class="text-2xl">🏦</span> Datos Bancarios BCP:
                                            </h4>
                                            <p class="text-sm text-green-700 font-medium">Titular: <strong class="text-green-900">DETALLESYMASFLORES SAC</strong></p>
                                            <p class="text-sm text-green-700 font-medium">Cuenta BCP Soles: <strong class="text-green-900">3557129566074</strong></p>
                                            <p class="text-sm text-green-700 font-medium">CCI: <strong class="text-green-900">00235500712956607461</strong></p>
                                            <p class="text-xs text-green-600 mt-2">Realiza la transferencia y sube tu comprobante</p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                Número de operación <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="payment_operation_number" value="{{ old('payment_operation_number') }}"
                                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                                placeholder="Ej: OP-123456789">
                                        </div>

                                        <div class="mt-4">
                                            <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                                Comprobante de transferencia <span class="text-red-500">*</span>
                                            </label>
                                            <input type="file" name="payment_evidence" accept=".jpg,.jpeg,.png,.pdf"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                            <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, PDF. Máximo 2MB</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tarjeta de Crédito/Débito -->
                                <div class="payment-method-container">
                                    <label class="flex items-center p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-[var(--naranja)] hover:bg-orange-50 transition-all payment-method-option" data-method="card">
                                        <input type="radio" name="payment_method" value="card" {{ old('payment_method') == 'card' ? 'checked' : '' }} required
                                            class="w-5 h-5 text-[var(--naranja)] focus:ring-[var(--naranja)]" onchange="togglePaymentFields()">
                                        <div class="ml-3 flex-1">
                                            <span class="font-semibold text-[var(--enlaces-titulos)]">Tarjeta de Crédito/Débito</span>
                                            <p class="text-xs text-[var(--texto-principal)] mt-1">Solicitar link de pago al WhatsApp: <a href="https://wa.me/51941492316" target="_blank" class="text-[var(--azul-primario)] hover:text-[var(--azul-claro)] font-semibold">941 492 316</a></p>
                                        </div>

                                    </label>
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

                        <!-- Additional Notes -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow">
                            <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-6 flex items-center gap-3">
                                <svg class="w-7 h-7 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Dedicatoria
                            </h2>
                            <textarea name="observations" rows="4"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all resize-none"
                                placeholder="Agrega cualquier nota o instrucción especial para tu pedido...">{{ old('observations') }}</textarea>
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
            const dateInput = document.getElementById('delivery_date');
            const timeSlotContainer = document.getElementById('time_slot_container');
            const timeRequired = document.getElementById('time_required');
            const timeSlotOptions = document.querySelectorAll('.time-slot-option');

            if (!dateInput.value) {
                timeSlotContainer.style.display = 'none';
                timeRequired.classList.add('hidden');
                return;
            }

            const selectedDate = new Date(dateInput.value);
            const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, etc.

            // Show time slot container
            timeSlotContainer.style.display = 'block';
            timeRequired.classList.remove('hidden');

            // Reset all time slots
            timeSlotOptions.forEach(option => {
                option.style.display = 'block';
                option.classList.remove('opacity-50', 'cursor-not-allowed');
                const radio = option.querySelector('input[type="radio"]');
                radio.disabled = false;
            });

            // Sunday - hide all time slots
            if (dayOfWeek === 0) {
                timeSlotContainer.innerHTML = '<p class="text-red-600 text-sm">Las entregas no están disponibles los domingos. Por favor selecciona otro día.</p>';
                return;
            }

            // Saturday - hide evening slot
            if (dayOfWeek === 6) {
                const eveningSlot = document.querySelector('.time-slot-option[data-slot="evening"]');
                if (eveningSlot) {
                    eveningSlot.style.display = 'none';
                    const radio = eveningSlot.querySelector('input[type="radio"]');
                    if (radio.checked) {
                        radio.checked = false;
                    }
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
                if (pickupFields) pickupFields.style.display = 'block';
                if (deliveryFields) deliveryFields.style.display = 'none';
                
                // Update label styles
                if (pickupLabel) {
                    pickupLabel.classList.add('border-[var(--naranja)]', 'bg-orange-50');
                    pickupLabel.classList.remove('border-gray-200');
                }
                if (deliveryLabel) {
                    deliveryLabel.classList.remove('border-[var(--azul-claro)]', 'bg-blue-50');
                    deliveryLabel.classList.add('border-gray-200');
                }
                
                // Disable delivery field validations
                if (deliveryFields) {
                    deliveryFields.querySelectorAll('input[required], textarea[required]').forEach(input => {
                        input.removeAttribute('required');
                    });
                }
            } else if (deliveryChoice === 'delivery') {
                // Show delivery fields, hide pickup fields
                if (pickupFields) pickupFields.style.display = 'none';
                if (deliveryFields) deliveryFields.style.display = 'block';
                
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
