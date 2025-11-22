<!--
    =============================================
    SECCIÓN: PRODUCTOS DESTACADOS (Rediseño Estilo Rosaliz)
    =============================================
-->
<section class="container mx-auto px-4 py-16">
    <div class="mb-12 text-center">
        <h2 class="mb-2 text-3xl font-bold text-gray-900">Lo nuevo</h2>
        <div class="mx-auto mb-4 h-1 w-16 bg-[var(--naranja)]"></div>
        <p class="text-gray-600">
            En Detalles y Más Flores encuentra el arreglo perfecto para cada ocasión.
        </p>
    </div>

    @if(($featuredProducts ?? collect())->isNotEmpty())
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($featuredProducts as $product)
                <div class="group relative flex flex-col overflow-hidden rounded-lg bg-white shadow-sm transition-shadow hover:shadow-md border border-gray-100">
                    <!-- Imagen y Overlay -->
                    <div class="relative aspect-square overflow-hidden bg-gray-100">
                        <!-- Etiqueta Oferta (Opcional, lógica ejemplo) -->
                        @if($product->sale_price && $product->sale_price < $product->unit_price)
                            <span class="absolute left-2 top-2 z-10 rounded bg-red-500 px-2 py-1 text-xs font-bold text-white">
                                -{{ round((($product->unit_price - $product->sale_price) / $product->unit_price) * 100) }}%
                            </span>
                        @endif

                        @if($product->image_path && \Storage::disk('public')->exists($product->image_path))
                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                 alt="{{ $product->name }}"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('images/no-image.png') }}'">
                        @else
                            <img src="{{ asset('images/no-image.png') }}"
                                 alt="{{ $product->name }}"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                 loading="lazy">
                        @endif

                        <!-- Botones Flotantes (Hover) -->
                        <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            <!-- Vista Rápida (Placeholder) -->
                            <button class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-900 hover:text-[var(--naranja)] transition-colors shadow-lg">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Info Producto -->
                    <div class="flex flex-1 flex-col p-4">
                        <h3 class="mb-2 text-lg font-medium text-gray-900 line-clamp-1">
                            <a href="#" class="hover:text-[var(--naranja)] transition-colors">{{ $product->name }}</a>
                        </h3>
                        
                        <div class="mb-4 flex items-center gap-2">
                            @if($product->sale_price && $product->sale_price < $product->unit_price)
                                <span class="text-sm text-gray-400 line-through">S/ {{ number_format($product->unit_price, 2) }}</span>
                                <span class="text-lg font-bold text-[var(--naranja)]">S/ {{ number_format($product->sale_price, 2) }}</span>
                            @else
                                <span class="text-lg font-bold text-[var(--naranja)]">S/ {{ number_format($product->unit_price, 2) }}</span>
                            @endif
                        </div>

                        <!-- Stock Info (Sutil) -->
                        @php
                            $warehouseStock = $product->stocks->where('warehouse_id', $warehouseId ?? null)->first();
                            $stockQty = $warehouseStock ? $warehouseStock->qty : 0;
                            $isOutOfStock = $stockQty <= 0;
                        @endphp
                        <div class="mb-3 text-xs">
                            @if($stockQty > 0)
                                <span class="text-green-600 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Stock: {{ number_format($stockQty, 0) }}</span>
                            @else
                                <span class="text-red-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Agotado</span>
                            @endif
                        </div>

                        <!-- Botón Añadir -->
                        @if($isOutOfStock)
                            <button type="button" disabled class="flex w-full items-center justify-center gap-2 rounded-md bg-gray-300 py-2 text-sm font-medium text-gray-500 cursor-not-allowed">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Agotado
                            </button>
                        @else
                            <form action="{{ route('cart.add') }}" method="POST" class="mt-auto">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-md bg-gray-900 py-2 text-sm font-medium text-white hover:bg-[var(--naranja)] transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Añadir al carrito
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Botón Ver Todos -->
        <div class="text-center mt-12">
            <a href="{{ route('shop.index') }}" class="inline-block rounded-full border-2 border-gray-900 px-8 py-3 text-sm font-bold text-gray-900 hover:bg-gray-900 hover:text-white transition-colors">
                VER TODOS LOS PRODUCTOS
            </a>
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500">No hay productos destacados disponibles.</p>
        </div>
    @endif
</section>