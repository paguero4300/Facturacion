<!--
    =============================================
    SECCIÓN HERO REDISEÑADA 2024
    =============================================
    - Diseño moderno con gradientes animados y microinteracciones
    - Grid responsive de 2 columnas optimizado para conversión
    - Elementos decorativos avanzados con movimiento orgánico
    - Iconografía SVG moderna con efectos hover
    - Optimización de accesibilidad WCAG 2.1
-->
<!-- Hero Section Rediseñada -->
<section class="hero-modern relative overflow-hidden min-h-screen flex items-center" aria-label="Sección principal de bienvenida">
<?php
// Obtener la configuración web específica para el ID 1
$webConfig = \App\Models\WebConfiguration::find(1);

// Crear array de banners activos
$banners = [];
if ($webConfig) {
    for ($i = 1; $i <= 3; $i++) {
        $imagen = $webConfig->{"banner_{$i}_imagen"} ?? null;
        if ($imagen) {
            $banners[] = [
                "imagen" => $imagen,
                "titulo" => $webConfig->{"banner_{$i}_titulo"} ?? "",
                "texto" => $webConfig->{"banner_{$i}_texto"} ?? "",
                "link" => $webConfig->{"banner_{$i}_link"} ?? "#",
            ];
        }
    }
}

$hasBanners = count($banners) > 0;
$isCarousel = count($banners) > 1;
?>
    <!-- Fondo dinámico con gradiente animado -->
    <div class="hero-background absolute inset-0"
         style="background: linear-gradient(135deg, var(--fondo-principal) 0%, #f8f9ff 50%, var(--fondo-principal) 100%);"></div>

    <!-- Sistema de partículas flotantes -->
    <div class="particles-container absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="particle particle-1 absolute w-4 h-4 rounded-full opacity-40"
             style="background: radial-gradient(circle, var(--naranja), transparent); top: 15%; left: 10%; animation: float-organic 8s ease-in-out infinite;"></div>
        <div class="particle particle-2 absolute w-6 h-6 rounded-full opacity-30"
             style="background: radial-gradient(circle, var(--azul-claro), transparent); top: 25%; right: 15%; animation: float-organic 10s ease-in-out infinite reverse;"></div>
        <div class="particle particle-3 absolute w-3 h-3 rounded-full opacity-50"
             style="background: radial-gradient(circle, var(--rojo-intenso), transparent); top: 60%; left: 20%; animation: float-organic 12s ease-in-out infinite;"></div>
        <div class="particle particle-4 absolute w-5 h-5 rounded-full opacity-35"
             style="background: radial-gradient(circle, var(--azul-primario), transparent); bottom: 30%; right: 25%; animation: float-organic 9s ease-in-out infinite reverse;"></div>
        <div class="particle particle-5 absolute w-2 h-2 rounded-full opacity-60"
             style="background: radial-gradient(circle, var(--naranja), transparent); top: 80%; left: 70%; animation: float-organic 7s ease-in-out infinite;"></div>
        <div class="particle particle-6 absolute w-4 h-4 rounded-full opacity-25"
             style="background: radial-gradient(circle, var(--azul-claro), transparent); top: 40%; right: 40%; animation: float-organic 11s ease-in-out infinite reverse;"></div>
    </div>

    <!-- Formas geométricas abstractas modernas -->
    <div class="geometric-shapes absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="shape-1 absolute opacity-20 blur-sm"
             style="top: 20%; right: 10%; width: 120px; height: 120px; background: conic-gradient(from 45deg, var(--naranja), transparent, var(--azul-claro)); border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; animation: morph-shapes 15s ease-in-out infinite;"></div>
        <div class="shape-2 absolute opacity-15 blur-sm"
             style="bottom: 25%; left: 5%; width: 160px; height: 100px; background: linear-gradient(135deg, var(--azul-claro), var(--azul-primario)); border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; animation: morph-shapes 18s ease-in-out infinite reverse;"></div>
        <div class="shape-3 absolute opacity-25 blur-xs"
             style="top: 50%; left: 15%; width: 80px; height: 80px; background: radial-gradient(ellipse, var(--rojo-intenso), transparent); border-radius: 50% 20% 80% 40%; animation: morph-shapes 12s ease-in-out infinite;"></div>
    </div>

    <!-- Grid principal optimizado -->
    <div class="hero-container container mx-auto px-4 md:px-6 lg:px-8 relative z-10">
        <div class="hero-grid grid lg:grid-cols-2 gap-8 lg:gap-16 items-center max-w-7xl mx-auto min-h-screen py-12 lg:py-0">
            <!-- Columna de contenido -->
            <div class="hero-content order-2 lg:order-1 space-y-8 animate-fade-in-up">
                <!-- Badge interactivo mejorado -->
                <div class="hero-badge">
                    <div class="inline-flex items-center px-6 py-3 rounded-full text-sm font-semibold tracking-wide border transition-all duration-300 hover:scale-105 hover:shadow-lg"
                         style="background: linear-gradient(135deg, rgba(255, 153, 0, 0.1), rgba(30, 160, 195, 0.1));
                                border-color: var(--naranja);
                                color: var(--naranja);
                                backdrop-filter: blur(10px);"
                         role="status" aria-label="Disponibilidad del servicio">
                        <span class="badge-pulse w-3 h-3 rounded-full mr-3 relative" style="background-color: var(--naranja);">
                            <span class="absolute inset-0 rounded-full animate-ping" style="background-color: var(--naranja);"></span>
                        </span>
                        <span class="badge-text">Detalles y Más</span>
                        <span class="ml-2 text-xs font-normal opacity-75">24/7</span>
                    </div>
                </div>

                <!-- Título principal con gradiente animado -->
                <div class="hero-title space-y-2">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight" role="banner">
                        <span class="hero-gradient-text block bg-clip-text text-transparent"
                              style="background-image: linear-gradient(90deg, var(--naranja) 0%, var(--azul-claro) 50%, var(--azul-primario) 100%);
                                     background-size: 200% 100%;
                                     animation: gradient-flow 4s ease-in-out infinite;">
                            Momentos únicos,
                        </span>
                        <span class="block mt-2" style="color: var(--enlaces-titulos);">
                            detalles perfectos
                        </span>
                    </h1>
                </div>

                <!-- Subtítulo dinámico -->
                <div class="hero-subtitle">
                    <p class="subtitle-rotating text-lg md:text-xl lg:text-2xl max-w-2xl leading-relaxed"
                       style="color: var(--texto-principal);"
                       data-texts='["Flores frescas y regalos únicos para cada ocasión especial", "Creamos experiencias inolvidables desde 2020", "Disponibles 24/7 para tus momentos importantes"]'>
                        Flores frescas y regalos únicos para cada ocasión especial
                    </p>
                </div>

                <!-- Tarjeta de contacto moderna -->
                <div class="contact-card bg-white/90 backdrop-blur-lg rounded-2xl p-6 shadow-xl border transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl"
                     style="border-color: var(--naranja); background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,249,240,0.95));">
                    <div class="flex flex-col lg:flex-row items-center gap-6">
                        <!-- Avatar mejorado -->
                        <div class="avatar-container relative group">
                            <div class="w-24 h-24 lg:w-32 lg:h-32 rounded-full overflow-hidden border-4 shadow-xl transition-all duration-300 group-hover:scale-105"
                                 style="border-color: var(--naranja);">
                                <img src="{{ asset('logos/herocontac1.jpg') }}"
                                     alt="Representante de atención al cliente"
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                     loading="lazy">
                            </div>
                            <!-- Indicador de disponibilidad -->
                            <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-3 border-white flex items-center justify-center"
                                 style="background-color: #10b981;"
                                 title="En línea">
                                <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Información de contacto optimizada -->
                        <div class="contact-info text-center lg:text-left flex-1">
                            <div class="mb-4">
                                <p class="text-sm font-medium opacity-75 mb-1" style="color: var(--texto-principal);">
                                    ¿Necesitas ayuda?
                                </p>
                                <a href="tel:+51944492316"
                                   class="contact-phone inline-flex items-center gap-3 text-xl lg:text-2xl font-bold transition-all duration-300 hover:scale-105 group"
                                   style="color: var(--naranja);"
                                   aria-label="Llamar al teléfono (51) 941 492 316">
                                    <span class="phone-icon text-2xl p-2 rounded-full transition-all duration-300 group-hover:rotate-12"
                                          style="background-color: rgba(255, 153, 0, 0.1);">
                                        📞
                                    </span>
                                    <span class="group-hover:underline">(51) 941 492 316</span>
                                </a>
                            </div>

                            <!-- Badges de servicio -->
                            <div class="service-badges flex flex-wrap justify-center lg:justify-start gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                      style="background-color: rgba(16, 185, 129, 0.1); color: #059669;">
                                    <span class="w-2 h-2 rounded-full mr-2 bg-green-500 animate-pulse"></span>
                                    Disponible 24/7
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                      style="background-color: rgba(255, 153, 0, 0.1); color: var(--naranja);">
                                    ✨ Respuesta inmediata
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTAs principales optimizados -->
                <div class="hero-ctas flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('shop.index') }}"
                       class="cta-primary group relative overflow-hidden text-white px-8 py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 hover:scale-105 flex items-center justify-center"
                       style="background: linear-gradient(135deg, var(--naranja), #ff7700); min-width: 200px;"
                       aria-label="Explorar nuestra tienda de productos">
                        <span class="relative z-10 flex items-center">
                            <span class="cta-text">Explorar Tienda</span>
                            <svg class="ml-3 w-5 h-5 transition-transform duration-300 group-hover:translate-x-2"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </span>
                        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                             style="background: linear-gradient(135deg, var(--azul-claro), var(--azul-primario));"></div>
                        <!-- Ripple effect -->
                        <div class="absolute inset-0 opacity-0 group-active:opacity-30 transition-opacity duration-150"
                             style="background: radial-gradient(circle, rgba(255,255,255,0.5) 0%, transparent 70%);"></div>
                    </a>

                    <a href="{{ route('home') }}#contacto"
                       class="cta-secondary group px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-500 border-2 shadow-md hover:shadow-xl transform hover:-translate-y-2 hover:scale-105 flex items-center justify-center"
                       style="color: var(--naranja);
                              background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,249,240,0.9));
                              border-color: var(--naranja);
                              backdrop-filter: blur(10px);
                              min-width: 200px;"
                       aria-label="Obtener consulta personalizada">
                        <span class="flex items-center">
                            <svg class="mr-3 w-5 h-5 transition-transform duration-300 group-hover:rotate-12"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <span class="cta-text">Consulta Personalizada</span>
                        </span>
                    </a>
                </div>

                <!-- Redes sociales con iconos SVG modernos -->
                <div class="social-media">
                    <p class="text-sm font-medium mb-4 opacity-75" style="color: var(--texto-principal);">
                        Síguenos en nuestras redes sociales
                    </p>
                    <div class="flex gap-4 justify-center lg:justify-start">
                        <!-- Instagram -->
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer"
                           class="social-link group w-14 h-14 rounded-xl flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-110"
                           style="background: linear-gradient(135deg, #ff9900, #ff6b6b);"
                           title="Síguenos en Instagram" aria-label="Enlace a Instagram">
                            <svg class="w-7 h-7 text-white transition-transform duration-300 group-hover:scale-110"
                                 fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12.017 0C8.396 0 7.931.013 6.704.074 5.48.135 4.66.323 3.95.609c-.74.296-1.369.69-1.994 1.315C1.33 2.55.936 3.179.64 3.919.354 4.629.166 5.449.105 6.673.044 7.9.031 8.365.031 11.985c0 3.62.013 4.085.074 5.312.061 1.224.249 2.044.535 2.754.296.74.69 1.369 1.315 1.994.625.625 1.254 1.019 1.994 1.315.71.286 1.53.474 2.754.535 1.227.061 1.692.074 5.312.074 3.62 0 4.085-.013 5.312-.074 1.224-.061 2.044-.249 2.754-.535.74-.296 1.369-.69 1.994-1.315.625-.625 1.019-1.254 1.315-1.994.286-.71.474-1.53.535-2.754.061-1.227.074-1.692.074-5.312 0-3.62-.013-4.085-.074-5.312-.061-1.224-.249-2.044-.535-2.754-.296-.74-.69-1.369-1.315-1.994C19.85 1.33 19.221.936 18.481.64 17.771.354 16.951.166 15.727.105 14.5.044 14.035.031 10.415.031c-3.62 0-4.085.013-5.312.074zm-.051 21.751c-3.539 0-3.98-.013-5.176-.072-1.164-.057-1.797-.236-2.217-.391-.558-.217-.957-.477-1.378-.898-.421-.421-.681-.82-.898-1.378-.155-.42-.334-1.053-.391-2.217-.059-1.196-.072-1.637-.072-5.176 0-3.539.013-3.98.072-5.176.057-1.164.236-1.797.391-2.217.217-.558.477-.957.898-1.378.421-.421.82-.681 1.378-.898.42-.155 1.053-.334 2.217-.391 1.196-.059 1.637-.072 5.176-.072 3.539 0 3.98.013 5.176.072 1.164.057 1.797.236 2.217.391.558.217.957.477 1.378.898.421.421.681.82.898 1.378.155.42.334 1.053.391 2.217.059 1.196.072 1.637.072 5.176 0 3.539-.013 3.98-.072 5.176-.057 1.164-.236 1.797-.391 2.217-.217.558-.477.957-.898 1.378-.421.421-.82.681-1.378.898-.42.155-1.053.334-2.217.391-1.196.059-1.637.072-5.176.072zM12.017 5.838a6.147 6.147 0 100 12.294 6.147 6.147 0 000-12.294zM12.017 16a4 4 0 110-8 4 4 0 010 8zm6.408-10.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                            </svg>
                        </a>

                        <!-- Facebook -->
                        <a href="https://facebook.com" target="_blank" rel="noopener noreferrer"
                           class="social-link group w-14 h-14 rounded-xl flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-110"
                           style="background: linear-gradient(135deg, #1877f2, #42a5f5);"
                           title="Síguenos en Facebook" aria-label="Enlace a Facebook">
                            <svg class="w-7 h-7 text-white transition-transform duration-300 group-hover:scale-110"
                                 fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>

                        <!-- WhatsApp -->
                        <a href="https://wa.me/51944492316" target="_blank" rel="noopener noreferrer"
                           class="social-link group w-14 h-14 rounded-xl flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-110"
                           style="background: linear-gradient(135deg, #25d366, #128c7e);"
                           title="Contáctanos por WhatsApp" aria-label="Enlace a WhatsApp">
                            <svg class="w-7 h-7 text-white transition-transform duration-300 group-hover:scale-110"
                                 fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.488"/>
                            </svg>
                        </a>

                        <!-- TikTok -->
                        <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer"
                           class="social-link group w-14 h-14 rounded-xl flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-110"
                           style="background: linear-gradient(135deg, #000000, #ff0050);"
                           title="Síguenos en TikTok" aria-label="Enlace a TikTok">
                            <svg class="w-7 h-7 text-white transition-transform duration-300 group-hover:scale-110"
                                 fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Columna visual mejorada con banners dinámicos -->
            <div class="hero-visual order-1 lg:order-2 animate-fade-in-up animation-delay-300">

                @if($hasBanners)
                    <!-- Slider de Banners -->
                    <div class="banner-slider relative group">
                        <!-- Efectos de fondo dinámicos -->
                        <div class="absolute -inset-8 rounded-3xl opacity-20 blur-2xl transition-all duration-700 group-hover:opacity-30"
                             style="background: conic-gradient(from 0deg, var(--naranja), var(--azul-claro), var(--azul-primario), var(--naranja)); animation: rotate-gradient 10s linear infinite;"></div>

                        <div class="relative bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden transition-all duration-700 group-hover:scale-[1.02] group-hover:shadow-3xl"
                             style="border: 1px solid rgba(255, 153, 0, 0.2);">
                            <div class="aspect-[4/3] relative overflow-hidden">
                                @if($isCarousel)
                                    <!-- Carrusel -->
                                    <div class="carousel-container relative w-full h-full" id="bannerCarousel">
                                        @foreach($banners as $index => $banner)
                                            <div class="carousel-slide absolute inset-0 transition-opacity duration-1000 {{ $index == 0 ? 'opacity-100' : 'opacity-0' }}"
                                                 data-slide="{{ $index }}">
                                                @if($banner['link'] && $banner['link'] != '#')
                                                    <a href="{{ $banner['link'] }}" class="block w-full h-full">
                                                @endif

                                                        <img src="{{ asset('storage/' . $banner['imagen']) }}"
                                                             alt="{{ $banner['titulo'] ?: 'Banner ' . ($index + 1) }}"
                                                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                                             loading="eager"
                                                             onerror="this.src='https://via.placeholder.com/800x600/ff9900/ffffff?text=Banner+{{ $index + 1 }}'">

                                                        <!-- Overlay con contenido del banner -->
                                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent flex items-end">
                                                            <div class="p-6 text-white">
                                                                @if($banner['titulo'])
                                                                    <h3 class="text-xl lg:text-2xl font-bold mb-2">{{ $banner['titulo'] }}</h3>
                                                                @endif
                                                                @if($banner['texto'])
                                                                    <p class="text-sm lg:text-base opacity-90">{{ $banner['texto'] }}</p>
                                                                @endif
                                                            </div>
                                                        </div>

                                                @if($banner['link'] && $banner['link'] != '#')
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach

                                        <!-- Controles del carrusel -->
                                        <button class="carousel-prev absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110"
                                                onclick="changeSlide(-1)">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                            </svg>
                                        </button>

                                        <button class="carousel-next absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 rounded-full w-10 h-10 flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110"
                                                onclick="changeSlide(1)">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>

                                        <!-- Indicadores -->
                                        <div class="carousel-indicators absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                                            @foreach($banners as $index => $banner)
                                                <button class="indicator w-2 h-2 rounded-full transition-all duration-300 {{ $index == 0 ? 'bg-white w-8' : 'bg-white/50' }}"
                                                        onclick="goToSlide({{ $index }})"
                                                        data-indicator="{{ $index }}"></button>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <!-- Banner único -->
                                    @php($banner = $banners[0]) ?>
                                    <div class="single-banner relative w-full h-full">
                                        @if($banner['link'] && $banner['link'] != '#')
                                            <a href="{{ $banner['link'] }}" class="block w-full h-full">
                                        @endif

                                            <img src="{{ asset('storage/' . $banner['imagen']) }}"
                                                 alt="{{ $banner['titulo'] ?: 'Banner Principal' }}"
                                                 class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110"
                                                 loading="eager"
                                                 onerror="this.src='https://via.placeholder.com/800x600/ff9900/ffffff?text=Banner+Principal'">

                                            <!-- Overlay con contenido del banner -->
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent flex items-end">
                                                <div class="p-6 text-white">
                                                    @if($banner['titulo'])
                                                        <h3 class="text-xl lg:text-2xl font-bold mb-2">{{ $banner['titulo'] }}</h3>
                                                    @endif
                                                    @if($banner['texto'])
                                                        <p class="text-sm lg:text-base opacity-90">{{ $banner['texto'] }}</p>
                                                    @endif
                                                </div>
                                            </div>

                                        @if($banner['link'] && $banner['link'] != '#')
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>


                        </div>

                        <!-- Elementos decorativos flotantes -->
                        <div class="floating-elements absolute inset-0 pointer-events-none" aria-hidden="true">
                            <div class="absolute -top-6 -left-6 w-12 h-12 rounded-full opacity-60 transition-all duration-500 group-hover:scale-125"
                                 style="background: radial-gradient(circle, var(--naranja), rgba(255, 153, 0, 0.3)); animation: float-gentle 6s ease-in-out infinite;"></div>
                            <div class="absolute -bottom-8 -right-8 w-16 h-16 rounded-full opacity-40 transition-all duration-500 group-hover:scale-125"
                                 style="background: radial-gradient(circle, var(--azul-claro), rgba(30, 160, 195, 0.3)); animation: float-gentle 8s ease-in-out infinite reverse;"></div>
                            <div class="absolute top-1/4 -right-4 w-8 h-8 rounded-full opacity-50 transition-all duration-500 group-hover:scale-125"
                                 style="background: radial-gradient(circle, var(--azul-primario), rgba(0, 124, 186, 0.3)); animation: float-gentle 10s ease-in-out infinite;"></div>
                        </div>
                    </div>
                @else
                    <!-- Imagen por defecto original cuando no hay banners -->
                    <div class="image-container relative group">
                        <!-- Efectos de fondo dinámicos -->
                        <div class="absolute -inset-8 rounded-3xl opacity-20 blur-2xl transition-all duration-700 group-hover:opacity-30"
                             style="background: conic-gradient(from 0deg, var(--naranja), var(--azul-claro), var(--azul-primario), var(--naranja)); animation: rotate-gradient 10s linear infinite;"></div>

                        <!-- Contenedor principal de imagen -->
                        <div class="relative bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden transition-all duration-700 group-hover:scale-[1.02] group-hover:shadow-3xl"
                             style="border: 1px solid rgba(255, 153, 0, 0.2);">
                            <div class="aspect-[4/3] relative overflow-hidden">
                                <!-- Imagen principal -->
                                <img src="{{ asset('logos/herosection.png') }}"
                                     alt="Hermosos arreglos florales y regalos únicos de Detalles y Más"
                                     class="hero-image w-full h-full object-cover transition-all duration-700 group-hover:scale-110"
                                     loading="eager"
                                     onerror="this.src='https://via.placeholder.com/800x600/ff9900/ffffff?text=Detalles+y+M%C3%A1s'">

                                <!-- Overlay con gradiente sutil -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                                <!-- Efectos de partículas en la imagen -->
                                <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500" style="background: radial-gradient(circle at 30% 70%, rgba(255, 153, 0, 0.1) 0%, transparent 50%), radial-gradient(circle at 70% 30%, rgba(30, 160, 195, 0.1) 0%, transparent 50%);"></div>
                            </div>

                            <!-- Badge flotante de calidad -->
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm rounded-xl px-4 py-2 shadow-lg border transition-all duration-300 hover:scale-105"
                                 style="border-color: var(--naranja);">
                                <div class="flex items-center gap-2">
                                    <span class="text-xl">⭐</span>
                                    <div class="text-sm">
                                        <div class="font-bold" style="color: var(--naranja);">Calidad Premium</div>
                                        <div class="text-xs opacity-75" style="color: var(--texto-principal);">Desde 2020</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Indicador de confianza -->
                            <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-sm rounded-xl px-4 py-2 shadow-lg border transition-all duration-300 hover:scale-105"
                                 style="border-color: var(--azul-claro);">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                                    <span class="text-sm font-semibold" style="color: var(--texto-principal);">500+ clientes felices</span>
                                </div>
                            </div>
                        </div>

                        <!-- Elementos decorativos flotantes -->
                        <div class="floating-elements absolute inset-0 pointer-events-none" aria-hidden="true">
                            <div class="absolute -top-6 -left-6 w-12 h-12 rounded-full opacity-60 transition-all duration-500 group-hover:scale-125"
                                 style="background: radial-gradient(circle, var(--naranja), rgba(255, 153, 0, 0.3)); animation: float-gentle 6s ease-in-out infinite;"></div>
                            <div class="absolute -bottom-8 -right-8 w-16 h-16 rounded-full opacity-40 transition-all duration-500 group-hover:scale-125"
                                 style="background: radial-gradient(circle, var(--azul-claro), rgba(30, 160, 195, 0.3)); animation: float-gentle 8s ease-in-out infinite reverse;"></div>
                            <div class="absolute top-1/4 -right-4 w-8 h-8 rounded-full opacity-50 transition-all duration-500 group-hover:scale-125"
                                 style="background: radial-gradient(circle, var(--azul-primario), rgba(0, 124, 186, 0.3)); animation: float-gentle 10s ease-in-out infinite;"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Ola decorativa moderna con gradiente -->
    <div class="wave-decoration absolute bottom-0 left-0 w-full overflow-hidden" style="height: 120px;">
        <svg class="wave-svg w-full h-full" viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <defs>
                <linearGradient id="waveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color: rgba(255, 255, 255, 0.9); stop-opacity: 1"/>
                    <stop offset="50%" style="stop-color: rgba(255, 249, 240, 0.95); stop-opacity: 1"/>
                    <stop offset="100%" style="stop-color: rgba(255, 255, 255, 1); stop-opacity: 1"/>
                </linearGradient>
            </defs>
            <path class="wave-path"
                  d="M0 120L48 110C96 100 192 80 288 70C384 60 480 60 576 65C672 70 768 80 864 85C960 90 1056 90 1152 85C1248 80 1344 70 1392 65L1440 60V120H1392C1344 120 1248 120 1152 120C1056 120 960 120 864 120C768 120 672 120 576 120C480 120 384 120 288 120C192 120 96 120 48 120H0Z"
                  fill="url(#waveGradient)"/>
        </svg>
    </div>
</section>

<!-- JavaScript para el carrusel de banners -->
@if($isCarousel)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.indicator');
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        // Ocultar todos los slides
        slides.forEach(slide => slide.classList.remove('opacity-100'));
        slides.forEach(slide => slide.classList.add('opacity-0'));

        // Mostrar slide actual
        slides[index].classList.remove('opacity-0');
        slides[index].classList.add('opacity-100');

        // Actualizar indicadores
        indicators.forEach(indicator => {
            indicator.classList.remove('bg-white', 'w-8');
            indicator.classList.add('bg-white/50');
        });
        indicators[index].classList.remove('bg-white/50');
        indicators[index].classList.add('bg-white', 'w-8');

        currentSlide = index;
    }

    function nextSlide() {
        const nextIndex = (currentSlide + 1) % slides.length;
        showSlide(nextIndex);
    }

    function prevSlide() {
        const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(prevIndex);
    }

    function startAutoSlide() {
        slideInterval = setInterval(nextSlide, 5000); // Cambiar cada 5 segundos
    }

    function stopAutoSlide() {
        clearInterval(slideInterval);
    }

    // Hacer funciones globales para los botones
    window.changeSlide = function(direction) {
        stopAutoSlide();
        if (direction === 1) {
            nextSlide();
        } else {
            prevSlide();
        }
        startAutoSlide();
    };

    window.goToSlide = function(index) {
        stopAutoSlide();
        showSlide(index);
        startAutoSlide();
    };

    // Iniciar carrusel automático
    startAutoSlide();

    // Pausar al pasar el mouse
    const carousel = document.getElementById('bannerCarousel');
    if (carousel) {
        carousel.addEventListener('mouseenter', stopAutoSlide);
        carousel.addEventListener('mouseleave', startAutoSlide);
    }
});
</script>
@endif
