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
        <div class="flex items-center">
            <img src="{{ asset('logos/logo_horizontal.png') }}" alt="Detalles y Más" class="h-12 w-auto object-contain" style="color: var(--naranja);">
        </div>
        <ul class="hidden md:flex gap-6 text-sm font-medium">
            <li><a href="{{ route('inicio') }}" class="transition" style="color: var(--enlaces-titulos);">INICIO</a></li>
            <li><a href="{{ route('nosotros') }}" class="transition" style="color: var(--enlaces-titulos);">NOSOTROS</a></li>
            <li class="relative group">
                <a href="{{ route('ocasiones') }}" class="transition flex items-center gap-1" style="color: var(--enlaces-titulos);">
                    OCASIONES
                    <svg class="w-4 h-4 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </a>
                <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                    <ul class="py-2">
                        <li><a href="{{ route('amor') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Amor</a></li>
                        <li><a href="{{ route('aniversario') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Aniversario</a></li>
                        <li><a href="{{ route('cumpleanos') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Cumpleaños</a></li>
                        <li><a href="{{ route('graduacion') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Graduación</a></li>
                        <li><a href="{{ route('nacimiento') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Nacimiento</a></li>
                    </ul>
                </div>
            </li>
            <li class="relative group">
                <a href="{{ route('arreglos') }}" class="transition flex items-center gap-1" style="color: var(--enlaces-titulos);">
                    ARREGLOS
                    <svg class="w-4 h-4 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </a>
                <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                    <ul class="py-2">
                        <li><a href="{{ route('rosas') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Rosas</a></li>
                        <li><a href="{{ route('girasoles') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Girasoles</a></li>
                        <li><a href="{{ route('flores-mixtas') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Flores Mixtas</a></li>
                        <li><a href="{{ route('lirios') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Lirios</a></li>
                        <li><a href="{{ route('tulipanes') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Tulipanes</a></li>
                    </ul>
                </div>
            </li>
            <li class="relative group">
                <a href="{{ route('regalos') }}" class="transition flex items-center gap-1" style="color: var(--enlaces-titulos);">
                    REGALOS
                    <svg class="w-4 h-4 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </a>
                <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                    <ul class="py-2">
                        <li><a href="{{ route('peluches') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Peluches</a></li>
                        <li><a href="{{ route('chocolates') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Chocolates</a></li>
                        <li><a href="{{ route('desayunos') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Desayunos</a></li>
                        <li><a href="{{ route('joyas') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Joyas</a></li>
                        <li><a href="{{ route('perfumes') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Perfumes</a></li>
                    </ul>
                </div>
            </li>
            <li class="relative group">
                <a href="{{ route('festivos') }}" class="transition flex items-center gap-1" style="color: var(--enlaces-titulos);">
                    FESTIVOS
                    <svg class="w-4 h-4 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </a>
                <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                    <ul class="py-2">
                        <li><a href="{{ route('san-valentin') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">San Valentín</a></li>
                        <li><a href="{{ route('dia-madre') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Día de la Madre</a></li>
                        <li><a href="{{ route('dia-padre') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Día del Padre</a></li>
                        <li><a href="{{ route('navidad') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Navidad</a></li>
                        <li><a href="{{ route('ano-nuevo') }}" class="block px-4 py-2 text-sm" style="color: var(--texto-principal);">Año Nuevo</a></li>
                    </ul>
                </div>
            </li>
        </ul>
        <div class="flex gap-4 text-sm">
            <a href="{{ route('buscar') }}" class="transition" style="color: var(--enlaces-titulos);">🔍</a>
            <a href="{{ route('usuario') }}" class="transition" style="color: var(--enlaces-titulos);">👤</a>
            <a href="{{ route('carrito') }}" class="transition relative" style="color: var(--enlaces-titulos);">
                🛒
                <span
                    class="absolute -top-2 -right-2 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" style="background-color: var(--naranja);">0</span>
            </a>
        </div>
    </nav>
</header>