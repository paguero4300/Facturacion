<!--
    =============================================
    SECCIÓN HERO PROFESIONAL (UX/UI Optimizado)
    =============================================
    - Jerarquía tipográfica clara (32-56px desktop, 22-34px móvil)
    - Animaciones suaves con autoplay 6s
    - Accesibilidad completa (ARIA, teclado)
    - Diseño responsive optimizado
-->
<section class="relative h-[600px] w-full overflow-hidden bg-gray-900 mt-16 md:mt-20" 
         role="region" 
         aria-roledescription="carousel" 
         aria-label="Hero Slider">
<?php
// Obtener configuración
$webConfig = \App\Models\WebConfiguration::find(1);
$banners = [];
if ($webConfig) {
    for ($i = 1; $i <= 3; $i++) {
        $type = $webConfig->{"banner_{$i}_type"} ?? 'image';
        $imagen = $webConfig->{"banner_{$i}_imagen"} ?? null;
        $video = $webConfig->{"banner_{$i}_video"} ?? null;
        
        // Solo agregar si tiene contenido (imagen o video)
        if (($type === 'image' && $imagen) || ($type === 'video' && $video)) {
            $banners[] = [
                "type" => $type,
                "imagen" => $imagen,
                "video" => $video,
                "titulo" => $webConfig->{"banner_{$i}_titulo"} ?? "",
                "texto" => $webConfig->{"banner_{$i}_texto"} ?? "",
                "link" => $webConfig->{"banner_{$i}_link"} ?? "#",
            ];
        }
    }
}
// Fallback si no hay banners
if (empty($banners)) {
    $banners[] = [
        "type" => "image",
        "imagen" => "logos/herosection.png",
        "video" => null,
        "titulo" => "El regalo perfecto para enamorar",
        "texto" => "Más de 200 opciones de arreglos premium",
        "link" => route('shop.index')
    ];
}
$hasBanners = count($banners) > 0;
$isCarousel = count($banners) > 1;
?>

    <div id="hero-carousel" class="relative w-full h-full group">
        @foreach($banners as $index => $banner)
            <div class="carousel-slide absolute inset-0 transition-opacity duration-700 ease-in-out {{ $index == 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}"
                 data-index="{{ $index }}"
                 role="group"
                 aria-roledescription="slide"
                 aria-label="Slide {{ $index + 1 }} de {{ count($banners) }}">
                
                <!-- Fondo de Media (Imagen o Video) -->
                <div class="absolute inset-0">
                    @if($banner['type'] === 'video' && $banner['video'])
                        <!-- Video Background -->
                        <video 
                            class="w-full h-full object-cover object-center"
                            autoplay 
                            muted 
                            loop 
                            playsinline
                            {{ $index == 0 ? '' : 'preload="none"' }}
                            aria-label="{{ $banner['titulo'] }}"
                            onloadedmetadata="this.muted = true; this.play().catch(e => console.log('Video autoplay prevented'))">
                            <source src="{{ asset('storage/' . $banner['video']) }}" type="video/{{ pathinfo($banner['video'], PATHINFO_EXTENSION) === 'webm' ? 'webm' : 'mp4' }}">
                            <!-- Fallback para navegadores sin soporte -->
                            Tu navegador no soporta videos HTML5.
                        </video>
                    @else
                        <!-- Image Background -->
                        <img src="{{ str_starts_with($banner['imagen'], 'logos/') ? asset($banner['imagen']) : asset('storage/' . $banner['imagen']) }}" 
                             alt="{{ $banner['titulo'] }}" 
                             class="w-full h-full object-cover object-center"
                             loading="{{ $index == 0 ? 'eager' : 'lazy' }}"
                             onerror="this.src='https://via.placeholder.com/1920x1080/f3f4f6/9ca3af?text=Banner+{{ $index + 1 }}'">
                    @endif
                    
                    <!-- Overlay Sutil para Contraste -->
                    <div class="absolute inset-0 bg-black/30"></div>
                </div>

                <!-- Contenido Centrado con Padding Interno -->
                <div class="absolute inset-0 flex items-center justify-center z-20 px-8 py-12">
                    <div class="container mx-auto flex flex-col items-center justify-center text-center text-white max-w-4xl space-y-6">
                        <!-- Título Principal: 32-56px Desktop, 22-34px Móvil -->
                        @if($banner['titulo'])
                            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black uppercase tracking-wide leading-tight drop-shadow-2xl animate-fadeIn">
                                {!! nl2br(e($banner['titulo'])) !!}
                            </h1>
                        @endif
                        
                        <!-- Subtítulo: 20-28px Desktop, 16-20px Móvil -->
                        @if($banner['texto'])
                            <p class="text-lg sm:text-xl md:text-2xl font-light tracking-wide max-w-2xl drop-shadow-lg animate-fadeIn animation-delay-200">
                                {{ $banner['texto'] }}
                            </p>
                        @endif

                        <!-- CTA Button: Grande y Visible -->
                        @if($banner['link'] && $banner['link'] != '#')
                            <a href="{{ $banner['link'] }}" 
                               class="inline-flex items-center justify-center px-10 py-4 text-lg font-bold text-white bg-[var(--naranja)] rounded-full hover:bg-white hover:text-[var(--naranja)] transition-all duration-300 shadow-2xl hover:shadow-3xl hover:scale-105 animate-fadeIn animation-delay-400"
                               aria-label="Ver Nueva Colección">
                                VER COLECCIÓN
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($isCarousel)
            <!-- Navegación Lateral (Flechas) con Accesibilidad -->
            <button onclick="prevHeroSlide()" 
                    class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 z-30 p-3 md:p-4 rounded-full bg-black/30 text-white hover:bg-black/50 hover:scale-110 transition-all duration-300 hidden md:flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-transparent"
                    aria-label="Slide anterior">
                <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button onclick="nextHeroSlide()" 
                    class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 z-30 p-3 md:p-4 rounded-full bg-black/30 text-white hover:bg-black/50 hover:scale-110 transition-all duration-300 hidden md:flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-transparent"
                    aria-label="Slide siguiente">
                <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Indicadores (Dots) Centrados -->
            <div class="absolute bottom-8 left-1/2 flex -translate-x-1/2 items-center gap-3 z-30" role="tablist" aria-label="Navegación de slides">
                @foreach($banners as $index => $banner)
                    <button onclick="showHeroSlide({{ $index }})" 
                            class="indicator-btn group flex items-center gap-2 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-transparent rounded-full px-2 py-1 {{ $index == 0 ? 'text-white' : 'text-white/50' }}"
                            role="tab"
                            aria-label="Ir a slide {{ $index + 1 }}"
                            aria-selected="{{ $index == 0 ? 'true' : 'false' }}">
                        <span class="text-sm font-medium">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="h-[2px] bg-white transition-all duration-300 {{ $index == 0 ? 'w-12' : 'w-4 group-hover:w-8' }}"></div>
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</section>

<!-- Estilos para Animaciones -->
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.8s ease-out forwards;
    }
    
    .animation-delay-200 {
        animation-delay: 0.2s;
        opacity: 0;
    }
    
    .animation-delay-400 {
        animation-delay: 0.4s;
        opacity: 0;
    }
</style>

@if($isCarousel)
<script>
    let currentHeroSlide = 0;
    const heroSlides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.indicator-btn');
    const totalHeroSlides = heroSlides.length;
    let heroInterval;
    let isPaused = false;

    function showHeroSlide(index) {
        // Validar índice
        if (index < 0) index = totalHeroSlides - 1;
        if (index >= totalHeroSlides) index = 0;
        
        // Update Slides
        heroSlides.forEach((s, i) => {
            const video = s.querySelector('video');
            
            if (i === index) {
                // Mostrar slide actual
                s.classList.remove('opacity-0', 'z-0');
                s.classList.add('opacity-100', 'z-10');
                
                // Reproducir video si existe
                if (video) {
                    video.muted = true; // Asegurar que esté muteado
                    video.play().catch(e => console.log('Video autoplay prevented:', e));
                }
            } else {
                // Ocultar otros slides
                s.classList.remove('opacity-100', 'z-10');
                s.classList.add('opacity-0', 'z-0');
                
                // Pausar video si existe
                if (video) {
                    video.pause();
                }
            }
        });

        // Update Indicators
        indicators.forEach((ind, i) => {
            const line = ind.querySelector('div');
            if (i === index) {
                ind.classList.remove('text-white/50');
                ind.classList.add('text-white');
                ind.setAttribute('aria-selected', 'true');
                line.classList.remove('w-4');
                line.classList.add('w-12');
            } else {
                ind.classList.add('text-white/50');
                ind.classList.remove('text-white');
                ind.setAttribute('aria-selected', 'false');
                line.classList.add('w-4');
                line.classList.remove('w-12');
            }
        });

        currentHeroSlide = index;
    }

    function nextHeroSlide() {
        showHeroSlide(currentHeroSlide + 1);
        resetAutoplay();
    }

    function prevHeroSlide() {
        showHeroSlide(currentHeroSlide - 1);
        resetAutoplay();
    }

    function startAutoplay() {
        heroInterval = setInterval(() => {
            if (!isPaused) {
                nextHeroSlide();
            }
        }, 6000); // 6 segundos por slide
    }

    function resetAutoplay() {
        clearInterval(heroInterval);
        startAutoplay();
    }

    // Navegación por Teclado
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            prevHeroSlide();
        } else if (e.key === 'ArrowRight') {
            nextHeroSlide();
        }
    });

    // Pausar en Hover (Opcional para UX)
    const carousel = document.getElementById('hero-carousel');
    carousel.addEventListener('mouseenter', () => {
        isPaused = true;
    });
    carousel.addEventListener('mouseleave', () => {
        isPaused = false;
    });

    // Iniciar Autoplay
    if (totalHeroSlides > 1) {
        startAutoplay();
    }
</script>
@endif
