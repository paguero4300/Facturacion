<!--
    =============================================
    SECCIÓN: CATEGORÍAS (Rediseño Estilo Rosaliz - "Lo más vendido")
    =============================================
-->
<section class="bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <div class="mb-12 text-center">
            <h2 class="mb-2 text-3xl font-bold text-gray-900">Nuestras Categorías</h2>
            <div class="mx-auto mb-4 h-1 w-16 bg-[var(--naranja)]"></div>
            <p class="text-gray-600">
                Explora nuestra variedad de flores y arreglos para cada momento especial.
            </p>
        </div>

        @if(($mainCategories ?? collect())->isNotEmpty())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($mainCategories as $category)
                    <div class="group relative flex flex-col overflow-hidden rounded-lg bg-white shadow-sm transition-shadow hover:shadow-md">
                        <!-- Imagen y Overlay -->
                        <div class="relative aspect-square overflow-hidden bg-gray-100">
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}"
                                     alt="{{ $category->name }}"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                     loading="lazy"
                                     onerror="this.src='{{ asset('images/no-image.png') }}'">
                            @else
                                <img src="{{ asset('images/no-image.png') }}"
                                     alt="{{ $category->name }}"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                     loading="lazy">
                            @endif

                            <!-- Overlay Hover -->
                            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                <a href="{{ url('/' . $category->slug) }}" class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-gray-900 hover:text-[var(--naranja)] transition-colors shadow-lg" title="Ver Categoría">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </div>

                        <!-- Info Categoría -->
                        <div class="flex flex-1 flex-col p-4 text-center">
                            <h3 class="mb-2 text-lg font-medium text-gray-900">
                                <a href="{{ url('/' . $category->slug) }}" class="hover:text-[var(--naranja)] transition-colors">{{ $category->name }}</a>
                            </h3>
                            <p class="text-sm text-gray-500 line-clamp-2 mb-4">
                                {{ $category->description ?? 'Descubre nuestros arreglos' }}
                            </p>
                            
                            <a href="{{ url('/' . $category->slug) }}" class="mt-auto inline-flex items-center justify-center gap-2 text-sm font-bold text-[var(--naranja)] hover:underline">
                                Ver Productos
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-gray-500">No hay categorías disponibles.</p>
            </div>
        @endif
    </div>
</section>