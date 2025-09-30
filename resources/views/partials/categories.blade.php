<!--
    =============================================
    SECCIÓN 4: CATÁLOGO DE CATEGORÍAS
    =============================================
    - Seis categorías principales de productos con imágenes y descripciones
    - Cada categoría tiene efecto hover que amplía la imagen suavemente
    - Diseño de cuadrícula responsiva (2 columnas en móvil, 3 en tablet/desktop)
-->
<!-- Categorías -->
<section id="productos" class="container mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <p class="text-sm mb-2 font-semibold tracking-wide uppercase" style="color: var(--naranja);">Nuestras Categorías</p>
        <h2 class="text-3xl md:text-4xl font-bold" style="color: var(--enlaces-titulos);">Explora Nuestros Productos</h2>
    </div>
    
    @if(($mainCategories ?? collect())->isNotEmpty())
        <!-- Swiper -->
        <div class="swiper categoriesSwiper max-w-6xl mx-auto">
            <div class="swiper-wrapper">
                @foreach($mainCategories as $category)
                    <div class="swiper-slide">
                        <a href="{{ url('/' . $category->slug) }}" 
                           class="block rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" 
                           style="background-color: var(--fondo-categorias); border: 1px solid var(--borde-categorias);">
                            <div class="aspect-[4/3] overflow-hidden">
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}"
                                        alt="{{ $category->name }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                                        loading="lazy" 
                                        onerror="this.src='{{ asset('images/no-image.png') }}'">
                                @else
                                    <img src="{{ asset('images/no-image.png') }}"
                                        alt="{{ $category->name }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                                        loading="lazy">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-lg" style="color: var(--texto-categorias);">{{ $category->name }}</h3>
                                <p class="text-sm mt-1" style="color: var(--texto-principal);">{{ $category->description ?? 'Ver productos' }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <!-- Paginación -->
            <div class="swiper-pagination mt-8"></div>
            <!-- Navegación -->
            <div class="swiper-button-next" style="color: var(--naranja);"></div>
            <div class="swiper-button-prev" style="color: var(--naranja);"></div>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-500">No hay categorías disponibles</p>
        </div>
    @endif
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const totalSlides = {{ ($mainCategories ?? collect())->count() }};
        
        const swiper = new Swiper('.categoriesSwiper', {
            slidesPerView: 2,
            spaceBetween: 20,
            loop: totalSlides > 6,
            centeredSlides: totalSlides <= 4,
            autoplay: totalSlides > 4 ? {
                delay: 3000,
                disableOnInteraction: false,
            } : false,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 3,
                    spaceBetween: 24,
                },
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 24,
                },
            },
        });
    });
</script>
@endpush