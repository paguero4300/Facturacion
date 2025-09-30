<!--
    =============================================
    SECCIÓN 5: PRODUCTOS DESTACADOS
    =============================================
    - Cuatro productos seleccionados con precios y botones de compra
    - Incluye etiquetas de "OFERTA" para destacar promociones
    - Diseño de cuadrícula responsiva con imágenes cuadradas
-->
<!-- Productos Destacados -->
<section class="container mx-auto px-4 py-16" style="background-color: rgba(255, 255, 255, 0.5);">
    <div class="text-center mb-12">
        <p class="text-sm mb-2 font-semibold tracking-wide uppercase" style="color: var(--naranja);">Nuestros Productos</p>
        <h2 class="text-3xl md:text-4xl font-bold" style="color: var(--enlaces-titulos);">Productos Destacados</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-6xl mx-auto">
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition" style="background-color: var(--fondo-productos);">
            <div class="relative aspect-square">
                <span
                    class="absolute top-3 left-3 text-white text-xs font-bold px-3 py-1 rounded-full z-10" style="background-color: var(--etiqueta-oferta);">OFERTA</span>
                <img src="https://images.unsplash.com/photo-1530325553241-4f6e7690cf36?w=500&h=500&fit=crop&q=80&auto=format"
                    alt="Peluche 20cm" class="w-full h-full object-cover lazy-load" loading="lazy"
                    onerror="this.src='https://via.placeholder.com/500x500?text=Peluche+20cm'">
            </div>
            <div class="p-4">
                <h3 class="font-bold mb-2" style="color: var(--enlaces-titulos);">Peluche 20 CM</h3>
                <p class="font-bold mb-1" style="color: var(--precio-actual);">S/ 30.00 - S/ 35.00</p>
                <p class="text-sm mb-3" style="color: var(--texto-principal);">Peluches adorables</p>
                <button
                    class="w-full text-white py-2 rounded-lg transition font-semibold" style="background-color: var(--naranja);">Añadir
                    al Carrito</button>
            </div>
        </div>
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition" style="background-color: var(--fondo-productos);">
            <div class="relative aspect-square">
                <span
                    class="absolute top-3 left-3 text-white text-xs font-bold px-3 py-1 rounded-full z-10" style="background-color: var(--etiqueta-oferta);">OFERTA</span>
                <img src="https://images.unsplash.com/photo-1551396832-e2a787e84e4e?w=500&h=500&fit=crop&q=80&auto=format"
                    alt="Peluche 30cm" class="w-full h-full object-cover lazy-load" loading="lazy"
                    onerror="this.src='https://via.placeholder.com/500x500?text=Peluche+30cm'">
            </div>
            <div class="p-4">
                <h3 class="font-bold mb-2" style="color: var(--enlaces-titulos);">Peluche 30 CM</h3>
                <p class="font-bold mb-1" style="color: var(--precio-actual);">S/ 40.00 - S/ 45.00</p>
                <p class="text-sm mb-3" style="color: var(--texto-principal);">Peluches medianos</p>
                <button
                    class="w-full text-white py-2 rounded-lg transition font-semibold" style="background-color: var(--naranja);">Añadir
                    al Carrito</button>
            </div>
        </div>
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition" style="background-color: var(--fondo-productos);">
            <div class="relative aspect-square">
                <span
                    class="absolute top-3 left-3 text-white text-xs font-bold px-3 py-1 rounded-full z-10" style="background-color: var(--etiqueta-oferta);">OFERTA</span>
                <img src="https://images.unsplash.com/photo-1563291074-2bf8677ac0e5?w=500&h=500&fit=crop&q=80&auto=format"
                    alt="Peluche 40cm" class="w-full h-full object-cover lazy-load" loading="lazy"
                    onerror="this.src='https://via.placeholder.com/500x500?text=Peluche+40cm'">
            </div>
            <div class="p-4">
                <h3 class="font-bold mb-2" style="color: var(--enlaces-titulos);">Peluche 40 CM</h3>
                <p class="font-bold mb-1" style="color: var(--precio-actual);">S/ 60.00 - S/ 65.00</p>
                <p class="text-sm mb-3" style="color: var(--texto-principal);">Peluches grandes</p>
                <button
                    class="w-full text-white py-2 rounded-lg transition font-semibold" style="background-color: var(--naranja);">Añadir
                    al Carrito</button>
            </div>
        </div>
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition" style="background-color: var(--fondo-productos);">
            <div class="relative aspect-square">
                <span
                    class="absolute top-3 left-3 text-white text-xs font-bold px-3 py-1 rounded-full z-10" style="background-color: var(--etiqueta-oferta);">OFERTA</span>
                <img src="https://images.unsplash.com/photo-1455659817273-f96807779a8a?w=500&h=500&fit=crop&q=80&auto=format"
                    alt="Rosas Rojas" class="w-full h-full object-cover lazy-load" loading="lazy"
                    onerror="this.src='https://via.placeholder.com/500x500?text=Rosas+Rojas'">
            </div>
            <div class="p-4">
                <h3 class="font-bold mb-2" style="color: var(--enlaces-titulos);">6 Rosas Rojas</h3>
                <p class="font-bold mb-1" style="color: var(--precio-actual);">S/ 75.00 - S/ 80.00</p>
                <p class="text-sm mb-3" style="color: var(--texto-principal);">Flores frescas</p>
                <button
                    class="w-full text-white py-2 rounded-lg transition font-semibold" style="background-color: var(--naranja);">Añadir
                    al Carrito</button>
            </div>
        </div>
    </div>
    <div class="text-center mt-10">
        <button
            class="border-2 px-8 py-3 rounded-lg transition font-semibold" style="color: var(--naranja); border-color: var(--naranja);">Ver
            Todos los Productos</button>
    </div>
</section>