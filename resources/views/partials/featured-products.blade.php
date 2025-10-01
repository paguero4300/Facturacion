<!--
    =============================================
    SECCIÓN 5: PRODUCTOS DESTACADOS (DINÁMICO)
    =============================================
    - Productos cargados dinámicamente desde base de datos
    - Filtrados por campo 'featured = true'
    - Grid estático para ≤4 productos, Swiper para >4
    - Imágenes desde storage con fallback
    - Diseño responsive y consistente con sección de categorías
-->
<!-- Productos Destacados -->
<section class="container mx-auto px-4 py-16" style="background-color: rgba(255, 255, 255, 0.5);">
    <div class="text-center mb-12">
        <p class="text-sm mb-2 font-semibold tracking-wide uppercase" style="color: var(--naranja);">Nuestros Productos</p>
        <h2 class="text-3xl md:text-4xl font-bold" style="color: var(--enlaces-titulos);">Productos Destacados</h2>
    </div>

    @if(($featuredProducts ?? collect())->isNotEmpty())
        @if($featuredProducts->count() <= 4)
            <!-- Grid estático para 4 o menos productos -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-6xl mx-auto">
                @foreach($featuredProducts as $product)
                    <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" style="background-color: var(--fondo-productos);">
                        <div class="relative aspect-square overflow-hidden">
                            <span class="absolute top-3 left-3 text-white text-xs font-bold px-3 py-1 rounded-full z-10" style="background-color: var(--etiqueta-oferta);">DESTACADO</span>

                            @if($product->image_path && \Storage::disk('public')->exists($product->image_path))
                                <img src="{{ asset('storage/' . $product->image_path) }}"
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                                    loading="lazy"
                                    onerror="this.src='{{ asset('images/no-image.png') }}'">
                            @else
                                <img src="{{ asset('images/no-image.png') }}"
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                                    loading="lazy">
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold mb-2 truncate" style="color: var(--enlaces-titulos);">{{ $product->name }}</h3>
                            <p class="font-bold mb-1" style="color: var(--precio-actual);">
                                S/ {{ number_format($product->sale_price ?? $product->unit_price, 2) }}
                            </p>
                            
                            <!-- Indicador de Stock si hay filtro de almacén activo -->
                            @if(request()->has('warehouse') && request()->warehouse)
                                @php
                                    $warehouseStock = $product->stocks->where('warehouse_id', request()->warehouse)->first();
                                    $stockQty = $warehouseStock ? $warehouseStock->qty : 0;
                                @endphp
                                <div class="mb-2">
                                    @if($stockQty > 0)
                                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle"></i> Stock: {{ number_format($stockQty, 0) }}
                                        </span>
                                    @else
                                        <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle"></i> Sin stock
                                        </span>
                                    @endif
                                </div>
                            @endif
                            
                            <p class="text-sm mb-3 line-clamp-2" style="color: var(--texto-principal);">
                                {{ $product->description ?? 'Producto de calidad' }}
                            </p>
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full text-white py-2 rounded-lg transition font-semibold hover:opacity-90" style="background-color: var(--naranja);">
                                    Añadir al Carrito
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Swiper para más de 4 productos -->
            <div class="swiper featuredProductsSwiper max-w-6xl mx-auto">
                <div class="swiper-wrapper">
                    @foreach($featuredProducts as $product)
                        <div class="swiper-slide">
                            <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" style="background-color: var(--fondo-productos);">
                                <div class="relative aspect-square overflow-hidden">
                                    <span class="absolute top-3 left-3 text-white text-xs font-bold px-3 py-1 rounded-full z-10" style="background-color: var(--etiqueta-oferta);">DESTACADO</span>

                                    @if($product->image_path && \Storage::disk('public')->exists($product->image_path))
                                        <img src="{{ asset('storage/' . $product->image_path) }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                                            loading="lazy"
                                            onerror="this.src='{{ asset('images/no-image.png') }}'">
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                                            loading="lazy">
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold mb-2 truncate" style="color: var(--enlaces-titulos);">{{ $product->name }}</h3>
                                    <p class="font-bold mb-1" style="color: var(--precio-actual);">
                                        S/ {{ number_format($product->sale_price ?? $product->unit_price, 2) }}
                                    </p>
                                    
                                    <!-- Indicador de Stock si hay filtro de almacén activo -->
                                    @if(request()->has('warehouse') && request()->warehouse)
                                        @php
                                            $warehouseStock = $product->stocks->where('warehouse_id', request()->warehouse)->first();
                                            $stockQty = $warehouseStock ? $warehouseStock->qty : 0;
                                        @endphp
                                        <div class="mb-2">
                                            @if($stockQty > 0)
                                                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle"></i> Stock: {{ number_format($stockQty, 0) }}
                                                </span>
                                            @else
                                                <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle"></i> Sin stock
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <p class="text-sm mb-3 line-clamp-2" style="color: var(--texto-principal);">
                                        {{ $product->description ?? 'Producto de calidad' }}
                                    </p>
                                    <form action="{{ route('cart.add') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="w-full text-white py-2 rounded-lg transition font-semibold hover:opacity-90" style="background-color: var(--naranja);">
                                            Añadir al Carrito
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- Paginación -->
                <div class="swiper-pagination mt-8"></div>
                <!-- Navegación -->
                <div class="swiper-button-next" style="color: var(--naranja);"></div>
                <div class="swiper-button-prev" style="color: var(--naranja);"></div>
            </div>
        @endif

        <!-- Botón Ver Todos los Productos -->
        <div class="text-center mt-10">
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                @php
                    $currentParams = request()->query();
                    $shopUrl = route('shop.index') . ($currentParams ? '?' . http_build_query($currentParams) : '');
                @endphp
                <a href="{{ $shopUrl }}" class="inline-block border-2 px-8 py-3 rounded-lg transition font-semibold hover:bg-opacity-10 hover:bg-orange-500" style="color: var(--naranja); border-color: var(--naranja);">
                    Ver Todos los Productos
                </a>
                <button id="openWarehouseModal" class="inline-block border-2 px-8 py-3 rounded-lg transition font-semibold hover:bg-opacity-10 hover:bg-blue-500" style="color: #3B82F6; border-color: #3B82F6;">
                    <i class="fas fa-warehouse mr-2"></i>Ver por Almacén
                </button>
            </div>
        </div>
    @else
        <!-- Mensaje cuando no hay productos destacados -->
        <div class="text-center py-12">
            <p class="text-gray-500 mb-4">No hay productos destacados disponibles en este momento</p>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                @php
                    $currentParams = request()->query();
                    $shopUrl = route('shop.index') . ($currentParams ? '?' . http_build_query($currentParams) : '');
                @endphp
                <a href="{{ $shopUrl }}" class="inline-block border-2 px-8 py-3 rounded-lg transition font-semibold hover:bg-opacity-10 hover:bg-orange-500" style="color: var(--naranja); border-color: var(--naranja);">
                    Ver Catálogo Completo
                </a>
                <button id="openWarehouseModalEmpty" class="inline-block border-2 px-8 py-3 rounded-lg transition font-semibold hover:bg-opacity-10 hover:bg-blue-500" style="color: #3B82F6; border-color: #3B82F6;">
                    <i class="fas fa-warehouse mr-2"></i>Ver por Almacén
                </button>
            </div>
        </div>
    @endif
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const totalSlides = {{ ($featuredProducts ?? collect())->count() }};

        // Solo inicializar Swiper si hay más de 4 productos
        if (totalSlides > 4) {
            const swiper = new Swiper('.featuredProductsSwiper', {
                slidesPerView: 2,
                spaceBetween: 20,
                loop: totalSlides > 6,
                centeredSlides: false,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
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
                        slidesPerView: totalSlides >= 3 ? 3 : 2,
                        spaceBetween: 24,
                    },
                    1024: {
                        slidesPerView: totalSlides >= 4 ? 4 : totalSlides,
                        spaceBetween: 24,
                    },
                },
            });
        }
    });
</script>
@endpush