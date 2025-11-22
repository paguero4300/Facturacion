@extends('layouts.app')

@section('title', 'Mis Pedidos - Detalles y Más')

@section('content')
<!-- Page Header -->
<div class="bg-gray-50 py-8 border-b border-gray-200">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">Mis Pedidos</h1>
        <p class="text-gray-600">Historial de tus compras realizadas</p>
    </div>
</div>

<!-- Orders Content -->
<div class="py-12 bg-white min-h-[60vh]">
    <div class="container mx-auto px-4">
        @if($orders->count() > 0)
            <div class="space-y-8 max-w-5xl mx-auto">
                @foreach($orders as $order)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <!-- Order Header -->
                        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        <h2 class="text-lg font-bold text-gray-800">
                                            Pedido #{{ $order->full_number }}
                                        </h2>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
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
                                    <p class="text-sm text-gray-500 flex items-center gap-2">
                                        <i class="far fa-calendar-alt"></i>
                                        {{ \Carbon\Carbon::parse($order->issue_date)->format('d/m/Y') }}
                                        <span class="text-gray-300">|</span>
                                        <i class="far fa-clock"></i>
                                        {{ \Carbon\Carbon::parse($order->issue_time)->format('H:i') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500 mb-1">Total del pedido</p>
                                    <p class="text-xl font-bold text-[var(--naranja)]">
                                        S/ {{ number_format($order->total_amount, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="p-6">
                            <!-- Products -->
                            <div class="space-y-4 mb-6">
                                @foreach($order->details as $detail)
                                    <div class="flex items-start gap-4 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                        @if($detail->product && $detail->product->image_path)
                                            <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 border border-gray-200">
                                                <img
                                                    src="{{ asset('storage/' . $detail->product->image_path) }}"
                                                    alt="{{ $detail->description }}"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.src='{{ asset('images/no-image.png') }}';"
                                                >
                                            </div>
                                        @else
                                            <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 border border-gray-200">
                                                <i class="fas fa-gift text-gray-300 text-xl"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-800 truncate mb-1">{{ $detail->description }}</h3>
                                            <div class="flex items-center text-sm text-gray-500">
                                                <span class="font-medium text-gray-700">{{ $detail->quantity }}</span>
                                                <span class="mx-1">x</span>
                                                <span>S/ {{ number_format($detail->unit_price, 2) }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right font-medium text-gray-800">
                                            S/ {{ number_format($detail->line_total, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Info & Actions -->
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-6 border-t border-gray-100">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <i class="fas fa-credit-card text-gray-400"></i>
                                    <span>Pago con: </span>
                                    <span class="font-medium text-gray-800">
                                        @switch($order->payment_method)
                                            @case('cash') Efectivo @break
                                            @case('yape') Yape @break
                                            @case('plin') Plin @break
                                            @case('card') Tarjeta @break
                                            @case('transfer') Transferencia @break
                                            @default {{ ucfirst($order->payment_method) }}
                                        @endswitch
                                    </span>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-3">
                                    <a href="{{ route('checkout.confirmation', $order->id) }}"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors">
                                        <i class="fas fa-eye"></i>
                                        Ver Detalles
                                    </a>
                                    <a href="{{ route('shop.index') }}"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 border border-[var(--naranja)] text-[var(--naranja)] text-sm font-medium rounded-lg hover:bg-orange-50 transition-colors">
                                        <i class="fas fa-redo-alt"></i>
                                        Comprar de Nuevo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="mt-10 flex justify-center">
                    {{ $orders->links() }}
                </div>
            @endif

        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-shopping-bag text-4xl text-[var(--naranja)] opacity-50"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">No tienes pedidos aún</h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">
                    Explora nuestra tienda y encuentra los detalles perfectos para esa persona especial.
                </p>
                <a href="{{ route('shop.index') }}"
                    class="inline-flex items-center gap-2 bg-[var(--naranja)] text-white font-semibold py-3 px-8 rounded-lg hover:bg-orange-600 transition-colors shadow-lg shadow-orange-200">
                    <i class="fas fa-store"></i>
                    Ir a la Tienda
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
