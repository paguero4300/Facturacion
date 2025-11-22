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

            <!-- Guest/Login Options (Discrete) -->
            @guest
                <div class="mb-6 bg-white rounded-xl border-2 border-gray-200 p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm text-[var(--texto-principal)] mb-3">
                                ¿Tienes cuenta? 
                                <a href="{{ route('login') }}" class="text-[var(--azul-primario)] hover:underline font-semibold">Inicia sesión</a>
                                o
                                <a href="{{ route('register') }}" class="text-[var(--azul-primario)] hover:underline font-semibold">Regístrate</a>
                                para acceder a beneficios.
                            </p>
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                También puedes continuar sin cuenta completando el formulario.
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
                        <!-- Personal Information -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow">
                            <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-6 flex items-center gap-3">
                                <svg class="w-7 h-7 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Información Personal
                            </h2>

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

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                        Email
                                    </label>
                                    <input type="email" name="client_email" value="{{ old('client_email') }}"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                        placeholder="correo@ejemplo.com">
                                    <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        Usaremos tu email solo para enviarte la confirmación y actualizaciones de tu pedido
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Address -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow">
                            <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-6 flex items-center gap-3">
                                <svg class="w-7 h-7 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Dirección de Entrega
                            </h2>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                        Dirección completa <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="client_address" required rows="3"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                        placeholder="Ej: Av. Los Rosales 456, Dpto. 302">{{ old('client_address') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                        Distrito
                                    </label>
                                    <input type="text" name="client_district" value="{{ old('client_district') }}"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                        placeholder="Ej: San Isidro">
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Scheduling -->
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-shadow">
                            <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-6 flex items-center gap-3">
                                <svg class="w-7 h-7 text-[var(--naranja)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4M3 12h18M8 12l2 3 4-6M3 21h18a2 2 0 002-2V9a2 2 0 00-2-2H3a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Programar Entrega (Opcional)
                            </h2>
                            <p class="text-sm text-[var(--texto-principal)] mb-4">
                                Puedes programar tu entrega para un día y horario específico. Si no seleccionas, te contactaremos para coordinar.
                            </p>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                        Fecha de entrega preferida
                                    </label>
                                    <input type="date" name="delivery_date"
                                        value="{{ old('delivery_date') }}"
                                        min="{{ $minDeliveryDate }}"
                                        max="{{ $maxDeliveryDate }}"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                        id="delivery_date"
                                        onchange="updateAvailableTimeSlots()">
                                    <p class="text-xs text-gray-500 mt-1">
                                        Las entregas están disponibles de lunes a sábado
                                    </p>
                                </div>

                                <div id="time_slot_container" style="display: none;">
                                    <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                        Horario preferido <span class="text-red-500 hidden" id="time_required">*</span>
                                    </label>
                                    <div class="space-y-2" id="time_slots">
                                        @foreach($deliveryTimeSlots as $value => $label)
                                            <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-pink-300 transition time-slot-option" data-slot="{{ $value }}">
                                                <input type="radio" name="delivery_time_slot" value="{{ $value }}"
                                                    {{ old('delivery_time_slot') == $value ? 'checked' : '' }}
                                                    class="w-4 h-4 text-pink-600 focus:ring-pink-500">
                                                <span class="ml-3 font-medium text-gray-900">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-[var(--enlaces-titulos)] mb-2">
                                        Instrucciones especiales para la entrega
                                    </label>
                                    <textarea name="delivery_notes" rows="3"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-[var(--naranja)] focus:border-[var(--naranja)] transition-all"
                                        placeholder="Ej: Tocar timbre del edificio, dejar con portería, llamar al llegar...">{{ old('delivery_notes') }}</textarea>
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

                            <!-- Trust Badges -->
                            <div class="mb-6 flex items-center justify-center gap-4 p-4 bg-gray-50 rounded-xl">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Pago 100% Seguro y Protegido</span>
                            </div>

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

                            <!-- Términos y Condiciones -->
                            <div class="mt-6 p-4 bg-gray-50 rounded-xl">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="accept_terms" value="1" required
                                        class="mt-1 w-5 h-5 text-[var(--naranja)] focus:ring-[var(--naranja)] rounded">
                                    <span class="text-sm text-[var(--texto-principal)]">
                                        Acepto los 
                                        <a href="{{ route('legal.terms') }}" target="_blank" class="text-[var(--azul-primario)] hover:underline font-semibold">términos y condiciones</a>
                                        y la 
                                        <a href="{{ route('legal.privacy') }}" target="_blank" class="text-[var(--azul-primario)] hover:underline font-semibold">política de privacidad</a>
                                    </span>
                                </label>
                            </div>

                            <button type="submit"
                                class="w-full mt-6 bg-gradient-to-r from-[var(--naranja)] to-[var(--azul-claro)] text-white font-bold py-4 px-6 rounded-xl hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Confirmar Pedido y Pagar
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
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
