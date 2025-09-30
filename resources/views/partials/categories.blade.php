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
    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
        @if(isset($mainCategories))
        @forelse($mainCategories as $category)
            <a href="{{ url('/' . $category->slug) }}" class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" style="background-color: var(--fondo-categorias); border: 1px solid var(--borde-categorias);">
                <div class="aspect-[4/3] overflow-hidden">
                    @if($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}"
                            alt="{{ $category->name }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                            loading="lazy" 
                            onerror="this.src='{{ asset('images/no-image.png') }}'">
                    @else
                        <img src="{{ asset('images/no-image.png') }}"
                            alt="{{ $category->name }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                            loading="lazy">
                    @endif
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg" style="color: var(--texto-categorias);">{{ $category->name }}</h3>
                    <p class="text-sm mt-1" style="color: var(--texto-principal);">{{ $category->description ?? 'Ver productos' }}</p>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">No hay categorías disponibles</p>
            </div>
        @endforelse
        @else
            <div class="col-span-full text-center py-8">
                <p class="text-red-500">Error: Variable mainCategories no está definida</p>
            </div>
        @endif
    </div>
</section>