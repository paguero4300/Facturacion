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
        <!-- Logo y Redes Sociales -->
        <div class="flex flex-col items-center gap-2">
            <!-- Redes Sociales Arriba -->
            <div class="flex gap-3">
                <a href="https://facebook.com" target="_blank" class="transition-transform hover:scale-110" title="Facebook">
                    <img src="{{ asset('storage/icons/facebook.png') }}" alt="Facebook" class="h-5 w-5 object-contain">
                </a>
                <a href="https://instagram.com" target="_blank" class="transition-transform hover:scale-110" title="Instagram">
                    <img src="{{ asset('storage/icons/instagram.png') }}" alt="Instagram" class="h-5 w-5 object-contain">
                </a>
                <a href="https://wa.me/51944492316" target="_blank" class="transition-transform hover:scale-110" title="WhatsApp">
                    <img src="{{ asset('storage/icons/whatsapp.png') }}" alt="WhatsApp" class="h-5 w-5 object-contain">
                </a>
                <a href="https://tiktok.com" target="_blank" class="transition-transform hover:scale-110" title="TikTok">
                    <img src="{{ asset('storage/icons/tik-tok.png') }}" alt="TikTok" class="h-5 w-5 object-contain">
                </a>
            </div>
            <!-- Logo -->
            <a href="{{ route('detalles.index') }}">
                <img src="{{ asset('logos/logo_horizontal.png') }}" alt="Detalles y Más" class="h-12 w-auto object-contain" style="color: var(--naranja);">
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
                        <img src="{{ asset('storage/icons/usuario.png') }}" alt="Usuario" class="h-6 w-6 object-contain">
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
                <a href="{{ route('login') }}" class="transition-transform hover:scale-110" title="Iniciar Sesión">
                    <img src="{{ asset('storage/icons/usuario.png') }}" alt="Iniciar Sesión" class="h-6 w-6 object-contain">
                </a>
            @endauth

            <!-- Carrito con Contador -->
            <a href="{{ route('cart.index') }}" class="relative transition-transform hover:scale-110" title="Carrito">
                <img src="{{ asset('storage/icons/carrito-de-compras.png') }}" alt="Carrito" class="h-6 w-6 object-contain">
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
