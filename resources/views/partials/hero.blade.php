<!--
    =============================================
    SECCIÓN 2: SECCIÓN HERO PRINCIPAL
    =============================================
    - Sección principal de aterrizaje con diseño atractivo
    - Contiene título principal, información de contacto y mensaje emocional
    - Incluye botones de llamada a la acción y enlaces a redes sociales
    - Diseño de dos columnas que se adapta a dispositivos móviles
-->
<!-- Hero Section -->
<section class="relative overflow-hidden">
    <!-- Fondo con gradiente y partículas decorativas -->
    <div class="absolute inset-0" style="background: linear-gradient(135deg, #fff6f7, #f0f0f0);"></div>

    <!-- Elementos decorativos flotantes -->
    <div class="absolute top-20 left-10 w-20 h-20 rounded-full opacity-20 animate-bounce"
        style="background-color: var(--naranja);"></div>
    <div class="absolute bottom-20 right-10 w-32 h-32 rounded-full opacity-20 animate-bounce"
        style="animation-delay: 1s; background-color: var(--azul-claro);"></div>
    <div class="absolute top-1/2 left-1/4 w-16 h-16 rounded-full opacity-20 animate-bounce"
        style="animation-delay: 2s; background-color: var(--rojo-intenso);"></div>

    <!-- Formas geométricas decorativas -->
    <div class="absolute top-40 right-20 w-0 h-0 border-l-[30px] border-l-transparent border-b-[50px] border-r-[30px] border-r-transparent opacity-30 transform rotate-45"
        style="border-bottom-color: var(--naranja);">
    </div>
    <div class="absolute bottom-40 left-20 w-0 h-0 border-l-[25px] border-l-transparent border-b-[40px] border-r-[25px] border-r-transparent opacity-30 transform -rotate-45"
        style="border-bottom-color: var(--azul-claro);">
    </div>

    <div class="container mx-auto px-4 py-16 md:py-24 relative z-10">
        <div class="grid md:grid-cols-2 gap-12 lg:gap-16 items-center max-w-7xl mx-auto">
            <!-- Contenido de texto -->
            <div class="order-2 md:order-1 space-y-8">
                <div class="space-y-4">
                    <!-- Badge con animación -->
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold tracking-wide animate-pulse"
                        style="background-color: rgba(255, 153, 0, 0.1); color: var(--naranja);">
                        <span class="w-2 h-2 rounded-full mr-2" style="background-color: var(--naranja);"></span>
                        Detalles y Más
                    </div>

                    <!-- Título principal con efecto de gradiente -->
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold leading-tight">
                        <span class="bg-clip-text text-transparent"
                            style="background-image: linear-gradient(to right, var(--naranja), var(--azul-claro));">
                            Detalles
                        </span>
                        <br>
                        <span style="color: var(--enlaces-titulos);">que enamoran</span>
                    </h1>

                    <!-- Subtítulo -->
                    <p class="text-lg md:text-xl max-w-lg" style="color: var(--texto-principal);">
                        Creamos momentos especiales con flores frescas y regalos únicos para cada ocasión importante
                        de tu vida.
                    </p>
                </div>

                <!-- Información de contacto destacada con imagen -->
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 shadow-lg border transform hover:scale-105 transition duration-300"
                    style="border-color: var(--naranja);">
                    <div class="flex flex-col md:flex-row items-center gap-6">
                        <!-- Imagen de contacto -->
                        <div class="relative">
                            <div class="w-40 h-40 rounded-full overflow-hidden border-4 shadow-lg transform hover:scale-105 transition duration-300"
                                style="border-color: var(--naranja);">
                                <img src="{{ asset('logos/herocontac1.jpg') }}" alt="Imagen de contacto"
                                    class="w-full h-full object-cover">
                            </div>
                            <!-- Efecto de brillo en la imagen -->
                            <div class="absolute inset-0 rounded-full opacity-0 hover:opacity-30 transition duration-300"
                                style="background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,153,0,0.4) 100%);">
                            </div>
                        </div>
                        <!-- Información de contacto -->
                        <div class="text-center md:text-left flex-1">
                            <p class="font-bold text-xl flex items-center justify-center md:justify-start gap-3"
                                style="color: var(--naranja);">
                                <span class="text-3xl p-3 rounded-full animate-pulse"
                                    style="background-color: rgba(255, 153, 0, 0.1);">🌻</span>
                                <a href="tel:+51944492316" class="hover:underline transition duration-300"
                                    style="color: var(--naranja);">(51) 944 492 316</a>
                            </p>
                            <p class="text-base mt-3 font-medium" style="color: var(--texto-principal);">Llámanos para
                                hacer tu pedido</p>
                            <div class="mt-3 flex justify-center md:justify-start">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                                    style="background-color: rgba(255, 153, 0, 0.1); color: var(--naranja);">Disponible
                                    24/7</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje emocional con diseño mejorado -->
                <div class="rounded-xl p-6 border-l-4 shadow-sm"
                    style="background: linear-gradient(to right, rgba(255, 153, 0, 0.05), rgba(30, 160, 195, 0.05)); border-left-color: var(--naranja);">
                    <p class="italic leading-relaxed" style="color: var(--texto-principal);">
                        "Tu confianza nos inspira a crear momentos inolvidables. En Detalles, cada flor cuenta una
                        historia, pensada para permitirnos ser parte de ella."
                    </p>
                </div>

                <!-- Botones de acción con efectos mejorados -->
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('shop.index') }}"
                        class="group relative overflow-hidden text-white px-8 py-4 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                        style="background-color: var(--naranja);">
                        <span class="relative z-10 flex items-center">
                            Ver Tienda
                            <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </span>
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                            style="background: linear-gradient(to right, var(--azul-claro), var(--azul-primario));">
                        </div>
                    </a>
                    <a href="{{ route('detalles.index') }}#contacto"
                        class="px-8 py-4 rounded-lg font-semibold transition-all duration-300 border-2 shadow-md hover:shadow-lg transform hover:-translate-y-1"
                        style="color: var(--naranja); background-color: var(--fondo-footer); border-color: var(--naranja);">
                        Contáctanos
                    </a>
                </div>

                <!-- Redes sociales con diseño mejorado -->
                <div class="flex gap-4">
                    <a href="https://instagram.com" target="_blank"
                        class="group w-12 h-12 rounded-full flex items-center justify-center shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 hover:scale-110"
                        title="Instagram" style="background-color: var(--fondo-footer);">
                        <img src="{{ asset('storage/icons/instagram.png') }}" alt="Instagram" class="h-6 w-6 object-contain group-hover:scale-110 transition-transform">
                    </a>
                    <a href="https://facebook.com" target="_blank"
                        class="group w-12 h-12 rounded-full flex items-center justify-center shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 hover:scale-110"
                        title="Facebook" style="background-color: var(--fondo-footer);">
                        <img src="{{ asset('storage/icons/facebook.png') }}" alt="Facebook" class="h-6 w-6 object-contain group-hover:scale-110 transition-transform">
                    </a>
                    <a href="https://wa.me/51944492316" target="_blank"
                        class="group w-12 h-12 rounded-full flex items-center justify-center shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 hover:scale-110"
                        title="WhatsApp" style="background-color: var(--fondo-footer);">
                        <img src="{{ asset('storage/icons/whatsapp.png') }}" alt="WhatsApp" class="h-6 w-6 object-contain group-hover:scale-110 transition-transform">
                    </a>
                    <a href="https://tiktok.com" target="_blank"
                        class="group w-12 h-12 rounded-full flex items-center justify-center shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 hover:scale-110"
                        title="TikTok" style="background-color: var(--fondo-footer);">
                        <img src="{{ asset('storage/icons/tik-tok.png') }}" alt="TikTok" class="h-6 w-6 object-contain group-hover:scale-110 transition-transform">
                    </a>
                </div>
            </div>

            <!-- Imagen con efectos mejorados -->
            <div class="order-1 md:order-2">
                <div class="relative">
                    <!-- Decoración de fondo con animación -->
                    <div class="absolute -inset-6 rounded-3xl opacity-30 blur-xl animate-pulse"
                        style="background: linear-gradient(to bottom right, var(--naranja), var(--azul-claro));">
                    </div>

                    <!-- Contenedor de imagen con proporción fija y efectos -->
                    <div
                        class="relative bg-white rounded-3xl shadow-2xl overflow-hidden transform hover:scale-[1.02] transition duration-500">
                        <div class="aspect-[4/3] relative">
                            <img src="{{ asset('logos/herosection.png') }}" alt="Hermoso arreglo de flores y regalos"
                                class="w-full h-full object-cover lazy-load" loading="eager"
                                onerror="this.src='https://via.placeholder.com/800x600?text=Imagen+no+disponible'">

                            <!-- Overlay con gradiente sutil -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-50">
                            </div>
                        </div>



                        <!-- Elementos decorativos adicionales -->
                        <div class="absolute -top-4 -left-4 w-8 h-8 rounded-full opacity-70"
                            style="background-color: var(--naranja);"></div>
                        <div class="absolute -bottom-4 -right-4 w-6 h-6 rounded-full opacity-70"
                            style="background-color: var(--azul-claro);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ola decorativa al final de la sección -->
    <div class="absolute bottom-0 left-0 w-full">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                fill="white" />
        </svg>
    </div>
</section>
