<!--
    =============================================
    SECCIÓN 1: ENCABEZADO DE NAVEGACIÓN (OPTIMIZADO)
    =============================================
    - Diseño compacto y moderno con backdrop-blur
    - Menú móvil totalmente funcional
    - UX mejorada para selector de local y usuario
-->
<header id="main-header" class="fixed top-0 w-full z-50 transition-all duration-300 bg-white shadow-sm border-b border-gray-100">
    <div class="container mx-auto px-4 h-20 flex justify-between items-center">
        
        <!-- 1. Menú Móvil Toggle (Izquierda en móvil) -->
        <div class="lg:hidden flex items-center">
            <button id="mobile-menu-btn" class="p-2 text-gray-600 hover:bg-gray-100 rounded-md transition-colors" onclick="toggleMobileMenu()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- 2. Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('logos/logook.png') }}" alt="Rosaliz" class="h-12 w-auto object-contain" style="transform: scale(0.65); transform-origin: center; filter: brightness(1.1) contrast(1.1);">
        </a>

        <!-- 3. Navegación Desktop -->
        <nav class="hidden lg:flex items-center gap-8">
            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-700 hover:text-[var(--naranja)] transition-colors">INICIO</a>
            <a href="{{ route('home') }}#nosotros" class="text-sm font-medium text-gray-700 hover:text-[var(--naranja)] transition-colors">NOSOTROS</a>
            <a href="{{ route('shop.index') }}" class="text-sm font-medium text-gray-700 hover:text-[var(--naranja)] transition-colors">TIENDA</a>
            
            @if (isset($menuCategories) && $menuCategories->count() > 0)
                @foreach ($menuCategories as $category)
                    <div class="relative group">
                        <a href="{{ url('/' . $category->slug) }}" class="flex items-center gap-1 text-sm font-medium text-gray-700 hover:text-[var(--naranja)] transition-colors cursor-pointer">
                            {{ strtoupper($category->name) }}
                            @if ($category->activeChildren->count() > 0)
                                <svg class="w-3 h-3 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        </a>

                        @if ($category->activeChildren->count() > 0)
                            <div class="absolute top-full left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-left z-50 overflow-hidden">
                                <ul class="py-1">
                                    @foreach ($category->activeChildren as $subcategory)
                                        <li>
                                            <a href="{{ url('/' . $subcategory->slug) }}"
                                                class="block px-5 py-3 text-sm text-gray-600 hover:bg-orange-50 hover:text-[var(--naranja)] transition-colors">
                                                {{ $subcategory->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </nav>

        <!-- 4. Acciones -->
        <div class="flex items-center gap-4">
            <!-- Acceso / Registro (Desktop) -->
            <div class="hidden lg:block">
                @auth
                    <div class="relative group">
                        <a href="{{ route('account.orders') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 uppercase flex items-center gap-1">
                            HOLA, {{ substr(Auth::user()->name, 0, 10) }}
                        </a>
                        <!-- Dropdown Usuario -->
                        <div class="absolute top-full right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <ul class="py-1">
                                <li><a href="{{ route('account.orders') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-[var(--naranja)]">Mis Pedidos</a></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50">Cerrar Sesión</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-medium text-gray-500 hover:text-gray-900 uppercase">
                        ACCESO / REGISTRO
                    </a>
                @endauth
            </div>

            <div class="flex items-center gap-2">
                <!-- Selector Almacén (Icono) -->
                <div class="relative group hidden sm:block">
                    <button class="p-2 text-gray-700 hover:text-[var(--naranja)] hover:bg-gray-100 rounded-full transition-colors" title="Seleccionar Local">
                        <i class="fas fa-map-marker-alt text-lg"></i>
                    </button>
                    <!-- Dropdown Almacén -->
                    <div class="absolute top-full right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                        <div class="p-3">
                            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-2">Ubicación</div>
                            <ul class="space-y-1 max-h-60 overflow-y-auto custom-scrollbar">
                                @foreach(\App\Models\Warehouse::where('is_active', true)->orderBy('is_default', 'desc')->get() as $warehouse)
                                    <li>
                                        <a href="{{ request()->url() }}?warehouse={{ $warehouse->id }}" 
                                           class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ request()->warehouse == $warehouse->id ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                            <i class="fas fa-store w-5 text-center mr-2"></i>
                                            <span class="truncate">{{ $warehouse->name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Search (Placeholder) -->
                <button class="p-2 text-gray-700 hover:text-[var(--naranja)] hover:bg-gray-100 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>

                <!-- Cart -->
                <a href="{{ route('cart.index') }}" class="flex items-center gap-2 p-2 text-gray-700 hover:text-[var(--naranja)] hover:bg-gray-100 rounded-md transition-colors group">
                    <div class="relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        @php $cartCount = session()->has('cart') ? count(session('cart')) : 0; @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-[var(--naranja)] text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </div>
                    <span class="hidden text-sm font-medium sm:inline-block group-hover:text-[var(--naranja)]">
                        <!-- Precio Opcional -->
                    </span>
                </a>
            </div>
        </div>
    </div>

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
