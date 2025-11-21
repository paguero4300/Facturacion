<!--
    =============================================
    SECCIÓN HERO FULL-WIDTH (Estilo Referencia)
    =============================================
    - Carrusel de ancho completo
    - Texto superpuesto con tipografía de impacto
    - Indicadores numéricos y flechas de navegación
-->
<section class="relative h-[600px] w-full overflow-hidden bg-gray-900 mt-16 md:mt-20">
<?php
// Obtener configuración
$webConfig = \App\Models\WebConfiguration::find(1);
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
// Fallback si no hay banners
if (empty($banners)) {
    $banners[] = [
        "imagen" => "logos/herosection.png",
        "titulo" => "ROSALIZ DELUXE",
        "texto" => "Amor y delicadeza",
        "link" => route('shop.index')
    ];
}
$hasBanners = count($banners) > 0;
$isCarousel = count($banners) > 1;
?>

    <div id="hero-carousel" class="relative w-full h-full group">
        @foreach($banners as $index => $banner)
            <div class="carousel-slide absolute inset-0 transition-opacity duration-1000 ease-in-out {{ $index == 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}"
                 data-index="{{ $index }}">
                
                <!-- Imagen de Fondo -->
                <div class="absolute inset-0">
                    <img src="{{ str_starts_with($banner['imagen'], 'logos/') ? asset($banner['imagen']) : asset('storage/' . $banner['imagen']) }}" 
                         alt="{{ $banner['titulo'] }}" 
                         class="w-full h-full object-cover object-center opacity-80"
                         onerror="this.src='https://via.placeholder.com/1920x1080/f3f4f6/9ca3af?text=Banner+{{ $index + 1 }}'">
                    
                    <!-- Overlay Simple -->
                    <div class="absolute inset-0 bg-black/20"></div>
                </div>

                <!-- Contenido Centrado -->
                <div class="absolute inset-0 flex items-center justify-center z-20">
                    <div class="container mx-auto px-4 flex flex-col items-center justify-center text-center text-white max-w-4xl">
                        @if($banner['titulo'])
                            <h1 class="mb-4 text-5xl font-black uppercase tracking-wider sm:text-6xl md:text-7xl drop-shadow-lg leading-none">
                                {!! nl2br(e($banner['titulo'])) !!}
                            </h1>
                        @endif
                        
                        @if($banner['texto'])
                            <p class="mb-8 text-xl font-light tracking-wide sm:text-2xl drop-shadow-md">
                                {{ $banner['texto'] }}
                            </p>
                        @endif

                        @if($banner['link'] && $banner['link'] != '#')
                            <a href="{{ $banner['link'] }}" 
                               class="h-12 flex items-center justify-center rounded-full bg-[var(--naranja)] px-8 text-base font-bold text-white hover:bg-white hover:text-[var(--naranja)] transition-colors shadow-lg">
                                NUEVA COLECCIÓN
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($isCarousel)
            <!-- Navegación (Flechas) -->
            <button onclick="prevHeroSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 z-30 p-2 rounded-full bg-black/20 text-white hover:bg-black/40 hover:scale-110 transition-all hidden md:flex items-center justify-center group">
                <svg class="w-8 h-8 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <button onclick="nextHeroSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 z-30 p-2 rounded-full bg-black/20 text-white hover:bg-black/40 hover:scale-110 transition-all hidden md:flex items-center justify-center group">
                <svg class="w-8 h-8 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <!-- Indicadores Centrados Abajo -->
            <div class="absolute bottom-8 left-1/2 flex -translate-x-1/2 items-center gap-4 text-white/80 z-30">
                @foreach($banners as $index => $banner)
                    <button onclick="showHeroSlide({{ $index }})" 
                            class="indicator-btn flex items-center gap-2 transition-all duration-300 {{ $index == 0 ? 'text-white font-bold' : 'text-white/50' }}">
                        <span class="text-sm font-medium">0{{ $index + 1 }}</span>
                        <div class="h-[2px] bg-white transition-all duration-300 {{ $index == 0 ? 'w-12' : 'w-4' }}"></div>
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</section>

@if($isCarousel)
<script>
    let currentHeroSlide = 0;
    const heroSlides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.indicator-btn');
    const totalHeroSlides = heroSlides.length;
    let heroInterval;

    function showHeroSlide(index) {
        // Update Slides
        heroSlides.forEach(s => {
            s.classList.remove('opacity-100', 'z-10');
            s.classList.add('opacity-0', 'z-0');
        });
        heroSlides[index].classList.remove('opacity-0', 'z-0');
        heroSlides[index].classList.add('opacity-100', 'z-10');
        
        // Update Indicators
        indicators.forEach((btn, i) => {
            const line = btn.querySelector('span:last-child');
            if (i === index) {
                btn.classList.remove('text-white/50');
                btn.classList.add('text-white', 'font-bold');
                line.classList.remove('w-4');
                line.classList.add('w-12');
            } else {
                btn.classList.add('text-white/50');
                btn.classList.remove('text-white', 'font-bold');
                line.classList.add('w-4');
                line.classList.remove('w-12');
            }
        });

        currentHeroSlide = index;
    }

    function nextHeroSlide() {
        showHeroSlide((currentHeroSlide + 1) % totalHeroSlides);
    }

    function prevHeroSlide() {
        showHeroSlide((currentHeroSlide - 1 + totalHeroSlides) % totalHeroSlides);
    }

    function startHeroAuto() {
        heroInterval = setInterval(nextHeroSlide, 6000);
    }
    
    function stopHeroAuto() {
        clearInterval(heroInterval);
    }

    const carousel = document.getElementById('hero-carousel');
    if(carousel) {
        carousel.addEventListener('mouseenter', stopHeroAuto);
        carousel.addEventListener('mouseleave', startHeroAuto);
    }
    
    startHeroAuto();
</script>
@endif
