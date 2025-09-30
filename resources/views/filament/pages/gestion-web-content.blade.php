<x-filament-panels::page>
    <x-filament-panels::header heading="Gestión Web" />

    <div class="space-y-8">
        <!-- Hero Section con estilo de la web -->
        <div class="relative overflow-hidden rounded-xl" style="background: linear-gradient(135deg, #fff6f7, #f0f0f0);">
            <!-- Elementos decorativos -->
            <div class="absolute top-4 right-4 w-16 h-16 rounded-full opacity-20 animate-bounce" style="background-color: var(--naranja);"></div>
            <div class="absolute bottom-4 left-4 w-12 h-12 rounded-full opacity-20 animate-bounce" style="animation-delay: 1s; background-color: var(--azul-claro);"></div>
            
            <div class="relative z-10 p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex-1">
                        <h1 class="text-3xl md:text-4xl font-bold mb-3" style="color: var(--enlaces-titulos);">
                            Gestión Web
                        </h1>
                        <p class="text-lg" style="color: var(--texto-principal);">
                            Administra las categorías y productos que se muestran en la página web.
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="w-24 h-24 rounded-full overflow-hidden border-4 shadow-lg" style="border-color: var(--naranja);">
                            <img src="{{ asset('logos/logo_horizontal.png') }}" alt="Logo" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4" style="border-left-color: var(--naranja);">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4" style="background-color: rgba(255, 153, 0, 0.1);">
                        <svg class="w-6 h-6" style="color: var(--naranja);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium" style="color: var(--texto-principal);">Total Categorías</p>
                        <p class="text-2xl font-bold" style="color: var(--enlaces-titulos);">{{ App\Models\Category::count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4" style="border-left-color: var(--azul-claro);">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4" style="background-color: rgba(30, 160, 195, 0.1);">
                        <svg class="w-6 h-6" style="color: var(--azul-claro);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium" style="color: var(--texto-principal);">Categorías Activas</p>
                        <p class="text-2xl font-bold" style="color: var(--enlaces-titulos);">{{ App\Models\Category::where('status', true)->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4" style="border-left-color: var(--rojo-intenso);">
                <div class="flex items-center">
                    <div class="p-3 rounded-full mr-4" style="background-color: rgba(204, 69, 69, 0.1);">
                        <svg class="w-6 h-6" style="color: var(--rojo-intenso);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium" style="color: var(--texto-principal);">Visibles en Web</p>
                        <p class="text-2xl font-bold" style="color: var(--enlaces-titulos);">{{ App\Models\Category::where('show_on_web', true)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario para crear categorías principales -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold mb-6" style="color: var(--enlaces-titulos);">Crear Categoría Principal</h2>
            
            <div class="mt-6">
                <button wire:click="create" class="inline-flex items-center px-4 py-2 rounded-lg font-medium transition-all duration-300 transform hover:-translate-y-1 shadow-md hover:shadow-lg" style="background-color: var(--naranja); color: white;">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Crear Categoría Principal
                </button>
            </div>
        </div>
        
        <!-- Gestión de categorías principales y asignación -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Categorías principales -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold mb-6" style="color: var(--enlaces-titulos);">Categorías Principales</h2>
                
                @if(count($mainCategories) > 0)
                    <div class="space-y-4">
                        @foreach($mainCategories as $mainCategory)
                            <div class="p-4 rounded-lg border" style="background-color: rgba({{ hexToRgb($mainCategory['color'] ?? '#ff9900') }}, 0.05); border-color: {{ $mainCategory['color'] ?? '#ff9900' }};">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-bold" style="color: var(--enlaces-titulos);">{{ $mainCategory['name'] }}</h3>
                                    <span class="text-xs px-2 py-1 rounded-full" style="background-color: {{ $mainCategory['color'] ?? '#ff9900' }}; color: white;">Principal</span>
                                </div>
                                <p class="text-sm mb-3" style="color: var(--texto-principal);">{{ $mainCategory['description'] ?? 'Sin descripción' }}</p>
                                
                                <!-- Subcategorías asignadas -->
                                <div class="mt-3">
                                    <h4 class="text-xs font-medium mb-2" style="color: var(--texto-principal);">Categorías en este grupo:</h4>
                                    <div class="space-y-2">
                                        @php
                                            $subCategories = App\Models\Category::where('main_category_id', $mainCategory['id'])->get();
                                        @endphp
                                        
                                        @if($subCategories->count() > 0)
                                            @foreach($subCategories as $subCategory)
                                                <div class="flex items-center justify-between p-2 rounded bg-gray-50">
                                                    <span class="text-sm" style="color: var(--texto-principal);">{{ $subCategory->name }}</span>
                                                    <button wire:click="removeFromMainCategory({{ $subCategory->id }})" class="text-xs px-2 py-1 rounded" style="background-color: rgba(204, 69, 69, 0.1); color: var(--rojo-intenso);">
                                                        Quitar
                                                    </button>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-xs italic" style="color: var(--texto-principal);">No hay categorías asignadas a este grupo.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium" style="color: var(--texto-principal);">No hay categorías principales</h3>
                        <p class="mt-1 text-sm" style="color: var(--texto-principal);">Crea categorías principales para agrupar las categorías existentes.</p>
                    </div>
                @endif
            </div>
            
            <!-- Categorías disponibles para asignar -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold mb-6" style="color: var(--enlaces-titulos);">Categorías Disponibles</h2>
                
                @if(count($regularCategories) > 0)
                    <div class="space-y-4">
                        @foreach($regularCategories as $category)
                            <div class="p-4 rounded-lg border" style="background-color: var(--fondo-categorias); border-color: var(--borde-categorias);">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-bold" style="color: var(--enlaces-titulos);">{{ $category['name'] }}</h3>
                                    <span class="text-xs px-2 py-1 rounded-full" style="background-color: rgba(255, 153, 0, 0.1); color: var(--naranja);">{{ $category['web_group'] }}</span>
                                </div>
                                <p class="text-sm mb-3" style="color: var(--texto-principal);">{{ $category['description'] ?? 'Sin descripción' }}</p>
                                
                                <!-- Asignar a categoría principal -->
                                <div class="mt-3">
                                    <label class="block text-xs font-medium mb-2" style="color: var(--texto-principal);">Asignar a categoría principal:</label>
                                    <div class="flex space-x-2">
                                        <select wire:model="assignToMainCategory{{ $category['id'] }}" class="flex-1 px-3 py-2 text-sm rounded-lg border focus:ring-2 focus:outline-none" style="border-color: var(--borde-categorias); focus:ring-color: var(--naranja);">
                                            <option value="">Seleccionar...</option>
                                            @foreach($mainCategories as $mainCategory)
                                                <option value="{{ $mainCategory['id'] }}">{{ $mainCategory['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <button wire:click="assignToMainCategory({{ $category['id'] }}, assignToMainCategory{{ $category['id'] }})" class="px-3 py-2 text-sm rounded-lg font-medium transition-colors" style="background-color: var(--naranja); color: white;">
                                            Asignar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium" style="color: var(--texto-principal);">No hay categorías disponibles</h3>
                        <p class="mt-1 text-sm" style="color: var(--texto-principal);">Todas las categorías están asignadas a grupos principales.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold mb-4" style="color: var(--enlaces-titulos);">Acciones Rápidas</h3>
                <div class="space-y-3">
                    <a href="{{ url('/admin/detalles') }}" class="flex items-center p-3 rounded-lg transition-colors hover:bg-gray-50" style="border: 1px solid var(--borde-categorias);">
                        <div class="p-2 rounded-lg mr-3" style="background-color: rgba(255, 153, 0, 0.1);">
                            <svg class="w-5 h-5" style="color: var(--naranja);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-medium" style="color: var(--enlaces-titulos);">Nueva Categoría</h4>
                            <p class="text-sm" style="color: var(--texto-principal);">Agregar una nueva categoría a la web</p>
                        </div>
                    </a>
                    
                    <a href="{{ url('/detalles') }}" target="_blank" class="flex items-center p-3 rounded-lg transition-colors hover:bg-gray-50" style="border: 1px solid var(--borde-categorias);">
                        <div class="p-2 rounded-lg mr-3" style="background-color: rgba(30, 160, 195, 0.1);">
                            <svg class="w-5 h-5" style="color: var(--azul-claro);" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-medium" style="color: var(--enlaces-titulos);">Ver Sitio Web</h4>
                            <p class="text-sm" style="color: var(--texto-principal);">Previsualizar el sitio web actual</p>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold mb-4" style="color: var(--enlaces-titulos);">Grupos de Categorías</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background-color: rgba(255, 153, 0, 0.05); border: 1px solid rgba(255, 153, 0, 0.2);">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" style="background-color: var(--naranja);"></span>
                            <span class="font-medium" style="color: var(--enlaces-titulos);">Principales</span>
                        </div>
                        <span class="text-sm font-medium" style="color: var(--texto-principal);">{{ App\Models\Category::where('web_group', 'principales')->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background-color: rgba(30, 160, 195, 0.05); border: 1px solid rgba(30, 160, 195, 0.2);">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" style="background-color: var(--azul-claro);"></span>
                            <span class="font-medium" style="color: var(--enlaces-titulos);">Secundarias</span>
                        </div>
                        <span class="text-sm font-medium" style="color: var(--texto-principal);">{{ App\Models\Category::where('web_group', 'secundarias')->count() }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-lg" style="background-color: rgba(204, 69, 69, 0.05); border: 1px solid rgba(204, 69, 69, 0.2);">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full mr-2" style="background-color: var(--rojo-intenso);"></span>
                            <span class="font-medium" style="color: var(--enlaces-titulos);">Especiales</span>
                        </div>
                        <span class="text-sm font-medium" style="color: var(--texto-principal);">{{ App\Models\Category::where('web_group', 'especiales')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>