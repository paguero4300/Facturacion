<!--
    =============================================
    SECCIÓN 1: ENCABEZADO DE NAVEGACIÓN
    =============================================
    - Barra de navegación fija en la parte superior
    - Contiene logo, enlaces de navegación e iconos de usuario
    - Diseño responsivo que se adapta a diferentes tamaños de pantalla
-->
<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50" style="background-color: var(--fondo-footer);">
    <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
        <!-- Logo -->
        <div class="flex items-center">
            <a href="{{ route('detalles.index') }}">
                <img src="{{ asset('logos/logo_horizontal.png') }}" alt="Detalles y Más" class="h-16 w-auto object-contain">
            </a>
        </div>

        <!-- Menú de Navegación -->
        <ul class="hidden md:flex gap-6 text-sm font-medium">
            <li><a href="{{ route('detalles.index') }}" class="transition hover:opacity-80" style="color: var(--enlaces-titulos);">INICIO</a></li>
            <li><a href="{{ route('detalles.index') }}#nosotros" class="transition hover:opacity-80" style="color: var(--enlaces-titulos);">NOSOTROS</a></li>
            <li><a href="{{ route('shop.index') }}" class="transition hover:opacity-80" style="color: var(--enlaces-titulos);">TIENDA</a></li>

            @if (isset($menuCategories) && $menuCategories->count() > 0)
                @foreach ($menuCategories as $category)
                    <li class="relative group">
                        <a href="{{ url('/' . $category->slug) }}" class="transition flex items-center gap-1 hover:opacity-80"
                            style="color: var(--enlaces-titulos);">
                            {{ strtoupper($category->name) }}
                            @if ($category->activeChildren->count() > 0)
                                <svg class="w-4 h-4 group-hover:rotate-180 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        </a>

                        @if ($category->activeChildren->count() > 0)
                            <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                <ul class="py-2">
                                    @foreach ($category->activeChildren as $subcategory)
                                        <li>
                                            <a href="{{ url('/' . $subcategory->slug) }}"
                                                class="block px-4 py-2 text-sm hover:bg-gray-100 transition"
                                                style="color: var(--texto-principal);">
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

        <!-- Iconos de Usuario y Carrito -->
        <div class="flex gap-4 text-sm items-center">
            @auth
                <!-- Usuario Autenticado con Dropdown -->
                <div class="relative group">
                    <button class="flex items-center gap-2 transition-transform hover:scale-110" style="color: var(--enlaces-titulos);">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden md:inline text-xs font-medium">{{ Auth::user()->name }}</span>
                    </button>
                    <div class="absolute top-full right-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                        <ul class="py-2">
                            <li>
                                <a href="{{ route('account.orders') }}"
                                   class="block px-4 py-2 text-sm hover:bg-gray-100 transition"
                                   style="color: var(--texto-principal);">
                                    📦 Mis Pedidos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('filament.admin.pages.dashboard') }}"
                                   class="block px-4 py-2 text-sm hover:bg-gray-100 transition"
                                   style="color: var(--texto-principal);">
                                    ⚙️ Panel Admin
                                </a>
                            </li>
                            <li class="border-t" style="border-color: rgba(0,0,0,0.1);">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition" style="color: var(--texto-principal);">
                                        🚪 Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            @else
                <!-- Login/Register -->
                <a href="{{ route('login') }}" class="transition-transform hover:scale-110" title="Iniciar Sesión" style="color: var(--enlaces-titulos);">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>
            @endauth

            <!-- Carrito con Contador -->
            <a href="{{ route('cart.index') }}" class="relative transition-transform hover:scale-110" title="Carrito" style="color: var(--enlaces-titulos);">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @php
                    $cartCount = session()->has('cart') ? count(session('cart')) : 0;
                @endphp
                @if($cartCount > 0)
                    <span class="absolute -top-2 -right-2 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center" style="background-color: var(--naranja);">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>
        </div>
    </nav>
</header>
