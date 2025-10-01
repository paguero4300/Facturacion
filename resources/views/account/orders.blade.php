<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - Tienda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body class="bg-gray-50">
    @include('partials.header')

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-pink-500 to-rose-500 text-white py-8">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold">Mis Pedidos</h1>
            <p class="text-base md:text-lg text-pink-100 mt-2">Historial de tus compras realizadas</p>
        </div>
    </div>

    <!-- Orders Content -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            @if($orders->count() > 0)
                <div class="space-y-6">
                    @foreach($orders as $order)
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                            <!-- Order Header -->
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    <div>
                                        <h2 class="text-lg font-bold text-gray-900">
                                            Pedido #{{ $order->full_number }}
                                        </h2>
                                        <p class="text-sm text-gray-600">
                                            Realizado el {{ \Carbon\Carbon::parse($order->issue_date)->format('d/m/Y') }}
                                            a las {{ \Carbon\Carbon::parse($order->issue_time)->format('H:i') }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            {{ $order->status == 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $order->status == 'draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $order->status == 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                            @switch($order->status)
                                                @case('paid')
                                                    ✓ Completado
                                                    @break
                                                @case('draft')
                                                    ⏱ En Proceso
                                                    @break
                                                @case('cancelled')
                                                    ✕ Cancelado
                                                    @break
                                                @default
                                                    {{ ucfirst($order->status) }}
                                            @endswitch
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Details -->
                            <div class="p-6">
                                <!-- Products -->
                                <div class="space-y-3 mb-4">
                                    @foreach($order->details as $detail)
                                        <div class="flex items-center gap-4">
                                            @if($detail->product && $detail->product->image_path)
                                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                                    <img
                                                        src="{{ asset('storage/' . $detail->product->image_path) }}"
                                                        alt="{{ $detail->description }}"
                                                        class="w-full h-full object-cover"
                                                        onerror="this.src='{{ asset('images/no-image.png') }}';"
                                                    >
                                                </div>
                                            @else
                                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-gray-900 truncate">{{ $detail->description }}</h3>
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

                                <!-- Order Summary -->
                                <div class="border-t pt-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600">Método de pago:</span>
                                        <span class="font-medium text-gray-900">
                                            @switch($order->payment_method)
                                                @case('cash')
                                                    Efectivo
                                                    @break
                                                @case('yape')
                                                    Yape
                                                    @break
                                                @case('plin')
                                                    Plin
                                                    @break
                                                @case('card')
                                                    Tarjeta
                                                    @break
                                                @case('transfer')
                                                    Transferencia
                                                    @break
                                                @default
                                                    {{ ucfirst($order->payment_method) }}
                                            @endswitch
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-lg font-bold">
                                        <span>Total pagado:</span>
                                        <span class="text-pink-600">S/ {{ number_format($order->total_amount, 2) }}</span>
                                    </div>
                                </div>

                                @if($order->observations)
                                    <div class="mt-4 pt-4 border-t">
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Observaciones:</span> {{ $order->observations }}
                                        </p>
                                    </div>
                                @endif

                                <!-- Actions -->
                                <div class="mt-4 pt-4 border-t flex flex-col sm:flex-row gap-3">
                                    <a href="{{ route('checkout.confirmation', $order->id) }}"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-pink-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-pink-700 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Ver Detalles
                                    </a>
                                    <a href="{{ route('shop.index') }}"
                                        class="flex-1 inline-flex items-center justify-center gap-2 bg-white text-pink-600 font-semibold py-2 px-4 rounded-lg border-2 border-pink-500 hover:bg-pink-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                        Comprar de Nuevo
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($orders->hasPages())
                    <div class="mt-8">
                        {{ $orders->links() }}
                    </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="bg-white rounded-xl shadow-md p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">No tienes pedidos aún</h2>
                    <p class="text-gray-600 mb-6">
                        Explora nuestra tienda y encuentra los productos perfectos para ti
                    </p>
                    <a href="{{ route('shop.index') }}"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-pink-500 to-rose-500 text-white font-semibold py-3 px-8 rounded-lg hover:from-pink-600 hover:to-rose-600 transition shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Ir a la Tienda
                    </a>
                </div>
            @endif
        </div>
    </div>

    @include('partials.footer')
</body>
</html>
