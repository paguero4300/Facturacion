@extends('layouts.app')

@section('title', 'Nuestras Tiendas - Detalles y Más')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Nuestras Tiendas</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Visítanos en cualquiera de nuestras ubicaciones para encontrar el regalo perfecto. 
                Selecciona tu tienda preferida para ver la disponibilidad de productos en tiempo real.
            </p>
        </div>

        <!-- Stores Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-6xl mx-auto">
            @foreach($warehouses as $warehouse)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">{{ $warehouse->name }}</h2>
                            @if($warehouse->is_default)
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                                    Principal
                                </span>
                            @endif
                        </div>

                        <div class="space-y-4 mb-8">
                            <!-- Address -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-[var(--naranja)]"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-700">Dirección</h3>
                                    <p class="text-gray-600">{{ $warehouse->address ?? 'Dirección no disponible' }}</p>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-phone text-[var(--azul-primario)]"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-700">Teléfono</h3>
                                    <p class="text-gray-600">{{ $warehouse->phone ?? 'No disponible' }}</p>
                                </div>
                            </div>

                            <!-- Schedule (Static for now, could be dynamic later) -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-clock text-green-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-700">Horario de Atención</h3>
                                    <p class="text-gray-600">Lunes a Sábado: 9:00 AM - 8:00 PM</p>
                                    <p class="text-gray-600">Domingos: 10:00 AM - 6:00 PM</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ route('home') }}?warehouse={{ $warehouse->id }}" 
                               class="flex-1 text-center px-6 py-3 bg-[var(--naranja)] text-white font-semibold rounded-lg hover:bg-orange-600 transition-colors">
                                <i class="fas fa-check mr-2"></i> Seleccionar esta Tienda
                            </a>
                            
                            @if($warehouse->latitude && $warehouse->longitude)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $warehouse->latitude }},{{ $warehouse->longitude }}" 
                                   target="_blank"
                                   class="flex-1 text-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-directions mr-2"></i> Cómo llegar
                                </a>
                            @else
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($warehouse->address . ', ' . $warehouse->name) }}" 
                                   target="_blank"
                                   class="flex-1 text-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-directions mr-2"></i> Cómo llegar
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Map Placeholder (Optional) -->
                    <div class="bg-gray-200 h-48 w-full flex items-center justify-center text-gray-400">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-map text-2xl"></i>
                            Mapa de ubicación
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
