<!--
    =============================================
    SECCIÓN HERO FULL-WIDTH (Estilo Referencia)
    =============================================
    - Carrusel de ancho completo
    - Texto superpuesto con tipografía de impacto
    - Indicadores numéricos y flechas de navegación
-->
<section class="relative w-full h-[calc(100vh-64px)] md:h-[calc(100vh-80px)] mt-16 md:mt-20 overflow-hidden bg-gray-100">
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
        "imagen" => "logos/herosection.png", // Asegúrate de que esta imagen exista o usa un placeholder
        "titulo" => "DETALLES Y MÁS",
        "texto" => "Amor y delicadeza en cada detalle",
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
                         class="w-full h-full object-cover object-center transform transition-transform duration-[10s] hover:scale-105"
                         onerror="this.src='https://via.placeholder.com/1920x1080/f3f4f6/9ca3af?text=Banner+{{ $index + 1 }}'">
                    
                    <!-- Overlay Gradiente para legibilidad -->
                    <div class="absolute inset-0 bg-gradient-to-r from-black/50 via-black/20 to-transparent"></div>
                </div>

                <!-- Contenido -->
                <div class="relative z-20 container mx-auto px-4 h-full flex items-center">
                    <div class="max-w-3xl text-white pl-4 md:pl-12 border-l-4 border-naranja/80">
                        @if($banner['titulo'])
                            <h2 class="text-5xl font-bold tracking-wider sm:text-6xl md:text-7xl mb-4 leading-none drop-shadow-lg">
                                {!! nl2br(e($banner['titulo'])) !!}
                            </h2>
                        @endif
                        
                        @if($banner['texto'])
                            <p class="text-xl font-light tracking-wide sm:text-2xl mb-8 text-gray-100 drop-shadow-md">
                                {{ $banner['texto'] }}
                            </p>
                        @endif

                        @if($banner['link'] && $banner['link'] != '#')
                            <a href="{{ $banner['link'] }}" 
                               class="inline-block px-8 py-3 bg-naranja text-white font-bold text-base rounded-full hover:bg-white hover:text-naranja transition-all duration-300 shadow-lg transform hover:-translate-y-1">
                                Ver Colección
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($isCarousel)
            <!-- Navegación (Flechas) -->
            <button onclick="prevHeroSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 z-30 p-3 text-white/70 hover:text-white transition-colors hidden md:block">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <button onclick="nextHeroSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 z-30 p-3 text-white/70 hover:text-white transition-colors hidden md:block">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <!-- Indicadores Numéricos (Bottom) -->
            <div class="absolute bottom-10 left-0 w-full z-30">
                <div class="container mx-auto px-4 flex justify-center md:justify-start md:pl-16 items-center gap-4 text-white/80 font-mono text-sm">
                    @foreach($banners as $index => $banner)
                        <button onclick="showHeroSlide({{ $index }})" 
                                class="indicator-btn flex items-center gap-2 transition-all duration-300 {{ $index == 0 ? 'text-white font-bold' : 'text-white/50' }}"
                                data-index="{{ $index }}">
                            <span>0{{ $index + 1 }}</span>
                            <span class="h-[2px] bg-current transition-all duration-300 {{ $index == 0 ? 'w-12' : 'w-4' }}"></span>
                        </button>
                    @endforeach
                </div>
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
