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
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" style="background-color: var(--fondo-categorias); border: 1px solid var(--borde-categorias);">
            <div class="aspect-[4/3] overflow-hidden">
                <img src="https://images.unsplash.com/photo-1495147466023-ac5c588e2e94?w=600&h=450&fit=crop&q=80&auto=format"
                    alt="Desayunos sorpresa"
                    class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                    loading="lazy" onerror="this.src='https://via.placeholder.com/600x450?text=Desayunos'">
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg" style="color: var(--texto-categorias);">Desayunos</h3>
                <p class="text-sm mt-1" style="color: var(--texto-principal);">Sorpresas especiales</p>
            </div>
        </div>
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" style="background-color: var(--fondo-categorias); border: 1px solid var(--borde-categorias);">
            <div class="aspect-[4/3] overflow-hidden">
                <img src="https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=600&h=450&fit=crop&q=80&auto=format"
                    alt="Arreglos florales"
                    class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                    loading="lazy" onerror="this.src='https://via.placeholder.com/600x450?text=Arreglos+Florales'">
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg" style="color: var(--texto-categorias);">Arreglos</h3>
                <p class="text-sm mt-1" style="color: var(--texto-principal);">Flores frescas</p>
            </div>
        </div>
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" style="background-color: var(--fondo-categorias); border: 1px solid var(--borde-categorias);">
            <div class="aspect-[4/3] overflow-hidden">
                <img src="https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&h=450&fit=crop&q=80&auto=format"
                    alt="Regalos y peluches"
                    class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                    loading="lazy" onerror="this.src='https://via.placeholder.com/600x450?text=Regalos+y+Peluches'">
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg" style="color: var(--texto-categorias);">Regalos</h3>
                <p class="text-sm mt-1" style="color: var(--texto-principal);">Peluches y más</p>
            </div>
        </div>
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" style="background-color: var(--fondo-categorias); border: 1px solid var(--borde-categorias);">
            <div class="aspect-[4/3] overflow-hidden">
                <img src="https://images.unsplash.com/photo-1464349095431-e9a21285b5f3?w=600&h=450&fit=crop&q=80&auto=format"
                    alt="Ocasiones especiales"
                    class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                    loading="lazy"
                    onerror="this.src='https://via.placeholder.com/600x450?text=Ocasiones+Especiales'">
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg" style="color: var(--texto-categorias);">Ocasiones</h3>
                <p class="text-sm mt-1" style="color: var(--texto-principal);">Para cada momento</p>
            </div>
        </div>
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" style="background-color: var(--fondo-categorias); border: 1px solid var(--borde-categorias);">
            <div class="aspect-[4/3] overflow-hidden">
                <img src="https://images.unsplash.com/photo-1512909006721-3d6018887383?w=600&h=450&fit=crop&q=80&auto=format"
                    alt="Fechas festivas"
                    class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                    loading="lazy" onerror="this.src='https://via.placeholder.com/600x450?text=Fechas+Festivas'">
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg" style="color: var(--texto-categorias);">Festivos</h3>
                <p class="text-sm mt-1" style="color: var(--texto-principal);">Fechas especiales</p>
            </div>
        </div>
        <div class="rounded-xl overflow-hidden shadow-md hover:shadow-xl transition group" style="background-color: var(--fondo-categorias); border: 1px solid var(--borde-categorias);">
            <div class="aspect-[4/3] overflow-hidden">
                <img src="https://images.unsplash.com/photo-1511381939415-e44015466834?w=600&h=450&fit=crop&q=80&auto=format"
                    alt="Chocolates premium"
                    class="w-full h-full object-cover group-hover:scale-110 transition duration-300 lazy-load"
                    loading="lazy" onerror="this.src='https://via.placeholder.com/600x450?text=Chocolates+Premium'">
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg" style="color: var(--texto-categorias);">Chocolates</h3>
                <p class="text-sm mt-1" style="color: var(--texto-principal);">Dulzura premium</p>
            </div>
        </div>
    </div>
</section>