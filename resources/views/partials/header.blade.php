<!--
    =============================================
    SECCIÓN 1: ENCABEZADO DE NAVEGACIÓN (OPTIMIZADO)
    =============================================
    - Diseño compacto y moderno con backdrop-blur
    - Menú móvil totalmente funcional
    - UX mejorada para selector de local y usuario
-->
<header id="main-header" class="fixed top-0 w-full z-50 transition-all duration-300 bg-white shadow-sm border-b border-gray-100">
    <nav class="container mx-auto px-4 h-16 md:h-20 flex justify-between items-center">
        
        <!-- 1. Menú Móvil Toggle (Izquierda en móvil) -->
        <div class="md:hidden flex items-center">
            <button id="mobile-menu-btn" class="p-2 text-gray-600 hover:text-gray-900 focus:outline-none transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- 2. Logo (Centrado en móvil, Izquierda en desktop) -->
        <div class="flex-shrink-0 flex items-center justify-center md:justify-start flex-1 md:flex-none">
            <a href="{{ route('home') }}" class="block transition-transform hover:scale-105 duration-300">
                <img src="{{ asset('logos/logook.png') }}" alt="Detalles y Más Flores"
                     class="h-16 md:h-20 w-auto object-contain mix-blend-multiply">
            </a>
        </div>

        <!-- 3. Navegación Desktop (Centro) -->
        <div class="hidden md:flex items-center justify-center flex-1 px-8">
            <ul class="flex gap-8 text-sm font-medium">
                <li>
                    <a href="{{ route('home') }}" class="relative flex items-center py-2 text-gray-700 hover:text-naranja transition-colors group">
                        INICIO
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-naranja transition-all duration-300 group-hover:w-full"></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('home') }}#nosotros" class="relative flex items-center py-2 text-gray-700 hover:text-naranja transition-colors group">
                        NOSOTROS
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-naranja transition-all duration-300 group-hover:w-full"></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('shop.index') }}" class="relative flex items-center py-2 text-gray-700 hover:text-naranja transition-colors group">
                        TIENDA
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-naranja transition-all duration-300 group-hover:w-full"></span>
                    </a>
                </li>

                @if (isset($menuCategories) && $menuCategories->count() > 0)
                    @foreach ($menuCategories as $category)
                        <li class="relative group">
                            <a href="{{ url('/' . $category->slug) }}" class="flex items-center gap-1 py-2 text-gray-700 hover:text-naranja transition-colors cursor-pointer">
                                {{ strtoupper($category->name) }}
                                @if ($category->activeChildren->count() > 0)
                                    <svg class="w-3 h-3 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            </a>

                            @if ($category->activeChildren->count() > 0)
                                <div class="absolute top-full left-0 mt-1 w-56 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-left z-50 overflow-hidden">
                                    <ul class="py-1">
                                        @foreach ($category->activeChildren as $subcategory)
                                            <li>
                                                <a href="{{ url('/' . $subcategory->slug) }}"
                                                    class="block px-5 py-3 text-sm text-gray-600 hover:bg-orange-50 hover:text-naranja transition-colors border-l-2 border-transparent hover:border-naranja">
                                                    {{ $subcategory->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>

        <!-- 4. Acciones Derecha (Selector, Usuario, Carrito) -->
        <div class="flex items-center gap-3 md:gap-5">
            
            <!-- Selector de Almacén (Desktop) -->
            <div class="hidden md:block relative group">
                <button onclick="markWarehouseSelectorSeen()" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-all text-xs font-medium text-gray-700 group-hover:border-blue-300 group-hover:shadow-sm">
                    <i class="fas fa-map-marker-alt text-naranja"></i>
                    <span class="max-w-[140px] md:max-w-[200px] truncate">
                        @php
                            $currentWarehouse = request()->has('warehouse') 
                                ? \App\Models\Warehouse::find(request()->warehouse) 
                                : \App\Models\Warehouse::where('is_default', true)->first();
                        @endphp
                        {{ $currentWarehouse ? $currentWarehouse->name : 'LOCALES' }}
                    </span>
                    <i class="fas fa-chevron-down text-[10px] text-gray-400 group-hover:rotate-180 transition-transform"></i>
                    
                    <!-- Badge Pulsante -->
                    <span id="warehouse-badge" class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                </button>
                
                <!-- Dropdown Almacén -->
                <div class="absolute top-full right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 transform origin-top-right">
                    <div class="p-3">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-2">Seleccionar Ubicación</div>
                        <ul class="space-y-1 max-h-60 overflow-y-auto custom-scrollbar">
                            <li>
                                <a href="{{ request()->url() }}{{ request()->has('category') ? '?category=' . request()->category : '' }}"
                                   class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ !request()->has('warehouse') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <i class="fas fa-globe w-5 text-center mr-2"></i> Todos los Locales
                                </a>
                            </li>
                            @foreach(\App\Models\Warehouse::where('is_active', true)->orderBy('is_default', 'desc')->orderBy('name')->get() as $warehouse)
                                <li>
                                    @php
                                        $warehouseParams = ['warehouse' => $warehouse->id];
                                        if (request()->has('category')) $warehouseParams['category'] = request()->category;
                                    @endphp
                                    <a href="{{ request()->url() }}?{{ http_build_query($warehouseParams) }}" 
                                       class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->warehouse == $warehouse->id ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                        <i class="fas fa-store w-5 text-center mr-2 {{ $warehouse->is_default ? 'text-yellow-500' : 'text-gray-400' }}"></i>
                                        <span class="truncate">{{ $warehouse->name }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Usuario (Desktop) -->
            <div class="hidden md:block relative group">
                <a href="{{ route('login') }}" class="p-2 text-gray-600 hover:text-naranja transition-colors relative">
                    @auth
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-naranja font-bold text-sm border border-orange-200">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @endauth
                </a>
                
                @auth
                <!-- Dropdown Usuario -->
                <div class="absolute top-full right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 transform origin-top-right">
                    <div class="p-4 border-b border-gray-50">
                        <p class="text-sm font-bold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <ul class="py-2">
                        <li><a href="{{ route('account.orders') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-naranja transition"><i class="fas fa-box-open mr-2 w-4"></i> Mis Pedidos</a></li>
                        <li><a href="{{ route('filament.admin.pages.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-naranja transition"><i class="fas fa-cog mr-2 w-4"></i> Panel Admin</a></li>
                        <li class="border-t border-gray-50 mt-1 pt-1">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition"><i class="fas fa-sign-out-alt mr-2 w-4"></i> Cerrar Sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endauth
            </div>

            <!-- Carrito (Siempre visible) -->
            <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-600 hover:text-naranja transition-transform hover:scale-110">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                @php $cartCount = session()->has('cart') ? count(session('cart')) : 0; @endphp
                @if($cartCount > 0)
                    <span class="absolute top-0 right-0 bg-naranja text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center shadow-sm border border-white">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>
        </div>
    </nav>

    <!-- MENÚ MÓVIL (Overlay + Sidebar) -->
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-300 opacity-0" onclick="toggleMobileMenu()"></div>
    
    <div id="mobile-menu-sidebar" class="fixed top-0 left-0 w-[280px] h-full bg-white z-50 transform -translate-x-full transition-transform duration-300 shadow-2xl overflow-y-auto">
        <!-- Header Móvil -->
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <span class="font-bold text-gray-800 text-lg">Menú</span>
            <button onclick="toggleMobileMenu()" class="text-gray-500 hover:text-red-500 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Usuario Móvil -->
        <div class="p-4 border-b border-gray-100">
            @auth
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-naranja font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-800 text-sm">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate max-w-[150px]">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('account.orders') }}" class="text-center py-2 text-xs font-medium bg-gray-50 rounded hover:bg-orange-50 hover:text-naranja transition">Mis Pedidos</a>
                    <form action="{{ route('logout') }}" method="POST" class="block">
                        @csrf
                        <button type="submit" class="w-full text-center py-2 text-xs font-medium bg-gray-50 rounded hover:bg-red-50 hover:text-red-500 transition">Salir</button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full py-2.5 bg-naranja text-white rounded-lg font-medium shadow-sm hover:bg-orange-600 transition">
                    <i class="fas fa-user-circle"></i> Iniciar Sesión
                </a>
            @endauth
        </div>

        <!-- Links Móvil -->
        <div class="p-4">
            <nav class="space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:text-naranja transition">
                    <i class="fas fa-home w-6 text-center text-gray-400"></i> Inicio
                </a>
                <a href="{{ route('shop.index') }}" class="block px-3 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:text-naranja transition">
                    <i class="fas fa-shopping-bag w-6 text-center text-gray-400"></i> Tienda
                </a>
                
                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Categorías</p>
                </div>

                @if (isset($menuCategories) && $menuCategories->count() > 0)
                    @foreach ($menuCategories as $category)
                        <div x-data="{ open: false }">
                            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:text-naranja transition">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-gift w-6 text-center text-gray-400"></i> {{ $category->name }}
                                </span>
                                @if ($category->activeChildren->count() > 0)
                                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" :class="{'rotate-180': open}"></i>
                                @endif
                            </button>
                            
                            @if ($category->activeChildren->count() > 0)
                                <div x-show="open" x-collapse class="pl-10 pr-3 space-y-1 pb-2" style="display: none;">
                                    <a href="{{ url('/' . $category->slug) }}" class="block py-1.5 text-sm text-gray-600 hover:text-naranja">Ver todo</a>
                                    @foreach ($category->activeChildren as $subcategory)
                                        <a href="{{ url('/' . $subcategory->slug) }}" class="block py-1.5 text-sm text-gray-600 hover:text-naranja">
                                            {{ $subcategory->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <a href="{{ url('/' . $category->slug) }}" class="hidden"></a> <!-- Link oculto para funcionalidad -->
                            @endif
                        </div>
                    @endforeach
                @endif
            </nav>
        </div>

        <!-- Selector Almacén Móvil -->
        <div class="p-4 border-t border-gray-100 bg-gray-50">
            <p class="px-1 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Ubicación</p>
            <div class="space-y-1">
                @foreach(\App\Models\Warehouse::where('is_active', true)->orderBy('is_default', 'desc')->take(3)->get() as $warehouse)
                    <a href="{{ request()->url() }}?warehouse={{ $warehouse->id }}" 
                       class="block px-3 py-2 text-sm rounded-lg {{ request()->warehouse == $warehouse->id ? 'bg-white shadow-sm text-blue-600 font-medium' : 'text-gray-600' }}">
                       <span class="block truncate max-w-[140px] md:max-w-[200px]">{{ $warehouse->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</header>

<style>
    /* Estilos adicionales para el header */
    .text-naranja { color: var(--naranja); }
    .bg-naranja { background-color: var(--naranja); }
    .hover\:text-naranja:hover { color: var(--naranja); }
    .hover\:bg-orange-50:hover { background-color: #fff7ed; }
    
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ccc; }
</style>

<script>
    // Lógica del Menú Móvil
    function toggleMobileMenu() {
        const sidebar = document.getElementById('mobile-menu-sidebar');
        const overlay = document.getElementById('mobile-menu-overlay');
        const body = document.body;
        
        if (sidebar.classList.contains('-translate-x-full')) {
            // Abrir
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            body.style.overflow = 'hidden'; // Prevenir scroll
        } else {
            // Cerrar
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
            body.style.overflow = '';
        }
    }

    // Alpine.js simple implementation for dropdowns if not present
    document.addEventListener('alpine:init', () => {
        // Alpine ya maneja x-data, x-show, etc.
    });

    // Fallback para dropdowns móviles si Alpine no está cargado
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Alpine === 'undefined') {
            const dropdownBtns = document.querySelectorAll('[x-data] button');
            dropdownBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const container = this.parentElement.querySelector('[x-show]');
                    const icon = this.querySelector('.fa-chevron-down');
                    
                    if (container.style.display === 'none') {
                        container.style.display = 'block';
                        if(icon) icon.classList.add('rotate-180');
                    } else {
                        container.style.display = 'none';
                        if(icon) icon.classList.remove('rotate-180');
                    }
                });
            });
        }

        // Header scroll effect
        const header = document.getElementById('main-header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 10) {
                header.classList.add('shadow-md', 'bg-white/95');
                header.classList.remove('shadow-sm', 'bg-white/90');
            } else {
                header.classList.remove('shadow-md', 'bg-white/95');
                header.classList.add('shadow-sm', 'bg-white/90');
            }
        });
    });

    // Lógica del Selector de Almacén (Legacy)
    function markWarehouseSelectorSeen() {
        sessionStorage.setItem('warehouse_selector_seen', 'true');
        const badge = document.getElementById('warehouse-badge');
        if (badge) badge.style.display = 'none';
    }
    
    if (sessionStorage.getItem('warehouse_selector_seen') === 'true') {
        const badge = document.getElementById('warehouse-badge');
        if (badge) badge.style.display = 'none';
    }
</script>
