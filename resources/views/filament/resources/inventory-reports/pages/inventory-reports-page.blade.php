<x-filament-panels::page>
    <style>
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        @keyframes shimmer {
            0% {
                background-position: -200px 0;
            }
            100% {
                background-position: calc(200px + 100%) 0;
            }
        }
        
        .animate-slide-up {
            animation: slideInUp 0.6s ease-out;
        }
        
        .animate-fade-scale {
            animation: fadeInScale 0.4s ease-out;
        }
        
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            background-size: 200px 100%;
            animation: shimmer 2s infinite;
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }
        
        .tab-glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
        }
    </style>
    
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 -m-6 p-6">
        <div class="max-w-7xl mx-auto space-y-8 animate-slide-up">
            <!-- Header Section -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl mb-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-gray-900 via-blue-800 to-indigo-800 bg-clip-text text-transparent mb-2">
                    📊 Reportes de Inventario
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Sistema avanzado de gestión y análisis de inventario con reportes en tiempo real
                </p>
            </div>
            
            <!-- Navigation Tabs -->
            <div class="glass-effect rounded-3xl shadow-2xl border border-white/20 p-2 animate-fade-scale">
                <nav class="flex space-x-2">
                    <a href="{{ request()->url() }}?tab=existencias" 
                       class="{{ $activeTab === 'existencias' 
                           ? 'bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-white shadow-2xl tab-glow transform scale-105' 
                           : 'text-gray-700 hover:text-blue-700 hover:bg-white/50' 
                       }} group relative flex items-center px-6 py-4 rounded-2xl font-semibold text-base transition-all duration-300 ease-out hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center space-x-3">
                            <div class="{{ $activeTab === 'existencias' ? 'bg-white/20' : 'bg-blue-100 group-hover:bg-blue-200' }} p-2 rounded-xl transition-all duration-300">
                                <svg class="w-6 h-6 {{ $activeTab === 'existencias' ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold">📦 Existencias Actuales</div>
                                <div class="text-xs opacity-75">Stock en tiempo real</div>
                            </div>
                        </div>
                        @if($activeTab === 'existencias')
                            <div class="absolute inset-0 rounded-2xl shimmer"></div>
                        @endif
                    </a>
                    
                    <a href="{{ request()->url() }}?tab=kardex" 
                       class="{{ $activeTab === 'kardex' 
                           ? 'bg-gradient-to-r from-purple-600 via-purple-700 to-pink-700 text-white shadow-2xl transform scale-105' 
                           : 'text-gray-700 hover:text-purple-700 hover:bg-white/50' 
                       }} group relative flex items-center px-6 py-4 rounded-2xl font-semibold text-base transition-all duration-300 ease-out hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center space-x-3">
                            <div class="{{ $activeTab === 'kardex' ? 'bg-white/20' : 'bg-purple-100 group-hover:bg-purple-200' }} p-2 rounded-xl transition-all duration-300">
                                <svg class="w-6 h-6 {{ $activeTab === 'kardex' ? 'text-white' : 'text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold">📋 Kardex</div>
                                <div class="text-xs opacity-75">Historial de movimientos</div>
                            </div>
                        </div>
                        @if($activeTab === 'kardex')
                            <div class="absolute inset-0 rounded-2xl shimmer"></div>
                        @endif
                    </a>
                    
                    <a href="{{ request()->url() }}?tab=bajo-stock" 
                       class="{{ $activeTab === 'bajo-stock' 
                           ? 'bg-gradient-to-r from-red-600 via-red-700 to-rose-700 text-white shadow-2xl transform scale-105' 
                           : 'text-gray-700 hover:text-red-700 hover:bg-white/50' 
                       }} group relative flex items-center px-6 py-4 rounded-2xl font-semibold text-base transition-all duration-300 ease-out hover:scale-105 hover:shadow-lg">
                        <div class="flex items-center space-x-3">
                            <div class="{{ $activeTab === 'bajo-stock' ? 'bg-white/20' : 'bg-red-100 group-hover:bg-red-200' }} p-2 rounded-xl transition-all duration-300">
                                <svg class="w-6 h-6 {{ $activeTab === 'bajo-stock' ? 'text-white' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold">⚠️ Bajo Stock</div>
                                <div class="text-xs opacity-75">Alertas de inventario</div>
                            </div>
                        </div>
                        @if($activeTab === 'bajo-stock')
                            <div class="absolute inset-0 rounded-2xl shimmer"></div>
                        @endif
                    </a>
                </nav>
            </div>

            <!-- Contenido de las pestañas -->
            <div class="mt-8">
                @if($activeTab === 'existencias')
                    <div class="glass-effect rounded-3xl shadow-xl border border-white/20 p-8 animate-fade-scale">
                        <div class="mb-8">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-700 to-indigo-700 bg-clip-text text-transparent">
                                        📦 Existencias Actuales por Almacén
                                    </h2>
                                    <p class="text-gray-600 text-base">
                                        Visualiza el stock actual de todos los productos por almacén en tiempo real
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filtro de almacén premium -->
                        <div class="mb-8">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-base font-semibold text-gray-800 mb-2">🏢 Filtrar por Almacén</label>
                                        <select wire:model="selectedWarehouse" class="block w-full rounded-xl border-0 bg-white shadow-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 text-base py-3 px-4 font-medium">
                                            <option value="" class="py-2">🌟 Todos los almacenes</option>
                                            @foreach(\App\Models\Warehouse::all() as $warehouse)
                                                <option value="{{ $warehouse->id }}" class="py-2">🏭 {{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            {{ $this->table }}
                        </div>
                    </div>
                
                @elseif($activeTab === 'kardex')
                    <div class="glass-effect rounded-3xl shadow-xl border border-white/20 p-8 animate-fade-scale">
                        <div class="mb-8">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold bg-gradient-to-r from-purple-700 to-pink-700 bg-clip-text text-transparent">
                                        📋 Kardex - Historial de Movimientos
                                    </h2>
                                    <p class="text-gray-600 text-base">
                                        Historial detallado de movimientos de inventario con análisis avanzados
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-50 via-pink-50 to-purple-50 rounded-3xl p-16 text-center border-2 border-dashed border-purple-200 relative overflow-hidden">
                            <!-- Elementos decorativos de fondo mejorados -->
                            <div class="absolute top-8 right-8 w-24 h-24 bg-gradient-to-br from-purple-200 to-pink-200 rounded-full opacity-40 animate-pulse"></div>
                            <div class="absolute bottom-8 left-8 w-20 h-20 bg-gradient-to-br from-pink-200 to-purple-200 rounded-full opacity-30 animate-bounce"></div>
                            <div class="absolute top-1/2 left-1/4 w-16 h-16 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full opacity-20 animate-pulse" style="animation-delay: 1s"></div>
                            
                            <div class="relative z-10">
                                <div class="mx-auto w-24 h-24 bg-gradient-to-br from-purple-500 via-purple-600 to-pink-600 rounded-3xl flex items-center justify-center mb-8 shadow-2xl transform rotate-3 hover:rotate-0 transition-all duration-500 hover:scale-110">
                                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                
                                <h3 class="text-3xl font-bold text-gray-900 mb-4 bg-gradient-to-r from-purple-600 via-purple-700 to-pink-700 bg-clip-text text-transparent">
                                    🚀 Kardex en Desarrollo
                                </h3>
                                
                                <p class="text-gray-700 mb-8 max-w-lg mx-auto leading-relaxed text-lg">
                                    Esta funcionalidad estará disponible próximamente. Aquí podrás ver el historial detallado de movimientos de inventario con análisis avanzados y reportes interactivos.
                                </p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-2xl mx-auto">
                                    <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 border border-purple-100 hover:shadow-lg transition-all duration-300">
                                        <div class="w-3 h-3 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full animate-pulse mb-3 mx-auto"></div>
                                        <h4 class="font-semibold text-gray-800 mb-2">📈 Movimientos de Stock</h4>
                                        <p class="text-sm text-gray-600">Entradas y salidas detalladas</p>
                                    </div>
                                    <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 border border-purple-100 hover:shadow-lg transition-all duration-300">
                                        <div class="w-3 h-3 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full animate-pulse mb-3 mx-auto" style="animation-delay: 0.5s"></div>
                                        <h4 class="font-semibold text-gray-800 mb-2">📊 Reportes Detallados</h4>
                                        <p class="text-sm text-gray-600">Análisis completos por período</p>
                                    </div>
                                    <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 border border-purple-100 hover:shadow-lg transition-all duration-300">
                                        <div class="w-3 h-3 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full animate-pulse mb-3 mx-auto" style="animation-delay: 1s"></div>
                                        <h4 class="font-semibold text-gray-800 mb-2">🔍 Análisis Histórico</h4>
                                        <p class="text-sm text-gray-600">Tendencias y patrones</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                @elseif($activeTab === 'bajo-stock')
                    <div class="glass-effect rounded-3xl shadow-xl border border-white/20 p-8 animate-fade-scale">
                        <div class="mb-8">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold bg-gradient-to-r from-red-700 to-rose-700 bg-clip-text text-transparent">
                                        ⚠️ Productos con Bajo Stock
                                    </h2>
                                    <p class="text-gray-600 text-base">
                                        Productos que han alcanzado o están por debajo del stock mínimo establecido
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                            {{ $this->table }}
                        </div>
                    </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>