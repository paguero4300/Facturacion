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
    <nav class="container mx-auto px-4 py-1 flex justify-between items-center">
        <div class="flex flex-col items-center">
            <div class="flex space-x-2 mb-2">
                <a href="#" target="_blank" class="transition-transform hover:scale-110">
                    <img src="{{ asset('storage/icons/facebook.png') }}" alt="Facebook" class="h-6 w-6 object-contain bg-none bg-transparent inline-block align-middle">
                </a>
                <a href="#" target="_blank" class="transition-transform hover:scale-110">
                    <img src="{{ asset('storage/icons/instagram.png') }}" alt="Instagram" class="h-6 w-6 object-contain bg-none bg-transparent inline-block align-middle">
                </a>
                <a href="#" target="_blank" class="transition-transform hover:scale-110">
                    <img src="{{ asset('storage/icons/whatsapp.png') }}" alt="WhatsApp" class="h-6 w-6 object-contain bg-none bg-transparent inline-block align-middle">
                </a>
                <a href="#" target="_blank" class="transition-transform hover:scale-110">
                    <img src="{{ asset('storage/icons/tik-tok.png') }}" alt="TikTok" class="h-6 w-6 object-contain bg-none bg-transparent inline-block align-middle">
                </a>
            </div>
            <img src="{{ asset('logos/logo_horizontal.png') }}" alt="Detalles y Más" class="h-8 w-auto object-contain"
                style="color: var(--naranja);">
        </div>
        <ul class="hidden md:flex gap-6 text-sm font-medium">
            <li><a href="{{ route('detalles.index') }}" class="transition"
                    style="color: var(--enlaces-titulos);">INICIO</a></li>
            <li><a href="{{ route('detalles.index') }}#nosotros" class="transition"
                    style="color: var(--enlaces-titulos);">NOSOTROS</a></li>

            @if (isset($menuCategories) && $menuCategories->count() > 0)
                @foreach ($menuCategories as $category)
                    <li class="relative group">
                        <a href="{{ url('/' . $category->slug) }}" class="transition flex items-center gap-1"
                            style="color: var(--enlaces-titulos);">
                            {{ strtoupper($category->name) }}
                            @if ($category->activeChildren->count() > 0)
                                <svg class="w-4 h-4 group-hover:rotate-180 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            @endif
                        </a>

                        @if ($category->activeChildren->count() > 0)
                            <div
                                class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
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
        <div class="flex gap-4 text-sm items-center">
            <a href="#buscar" class="transition-transform hover:scale-110">
                <img src="{{ asset('storage/icons/buscar.png') }}" alt="Buscar" class="h-5 w-5 object-contain bg-none bg-transparent inline-block align-middle">
            </a>
            <a href="#usuario" class="transition-transform hover:scale-110">
                <img src="{{ asset('storage/icons/usuario.png') }}" alt="Usuario" class="h-5 w-5 object-contain bg-none bg-transparent inline-block align-middle">
            </a>
            <a href="#carrito" class="transition-transform hover:scale-110">
                <img src="{{ asset('storage/icons/carrito-de-compras.png') }}" alt="Carrito de compras" class="h-5 w-5 object-contain bg-none bg-transparent inline-block align-middle">
            </a>
        </div>
    </nav>
</header>
