<!--
    =============================================
    SECCIÓN 7: PIE DE PÁGINA
    =============================================
    - Información de contacto completa con horario de atención
    - Enlaces organizados por categorías: Arreglos, Ocasiones y Festivos
    - Diseño oscuro con cuatro columnas en desktop y apilado en móvil
-->
<!-- Footer -->
<footer class="py-12" style="background-color: var(--fondo-footer); color: var(--texto-principal);">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8 mb-8 max-w-6xl mx-auto">
            <div>
                <div class="mb-4">
                    <img src="{{ asset('logos/logook.png') }}" alt="Detalles y Más Flores" class="h-16 w-auto object-contain" style="background-color: transparent !important; min-height: auto !important;">
                </div>
                <h3 class="font-bold mb-3" style="color: var(--enlaces-titulos);">Detalles y Más</h3>
                <p class="text-sm mb-2">📞 (+51) 944 492 316</p>
                <p class="text-sm mb-2">✉️ contacto@detalles.com</p>
                <p class="text-sm">🕒 Lun - Dom: 9:00 - 20:00</p>
            </div>
            <div>
                <h3 class="font-bold mb-4" style="color: var(--enlaces-titulos);">Arreglos</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/rosas-flor') }}" class="transition" style="color: var(--texto-principal);">Rosas</a></li>
                    <li><a href="{{ url('/girasoles-flor') }}" class="transition" style="color: var(--texto-principal);">Girasoles</a></li>
                    <li><a href="{{ url('/tulipanes-flor') }}" class="transition" style="color: var(--texto-principal);">Tulipanes</a></li>
                    <li><a href="{{ url('/boxflor') }}" class="transition" style="color: var(--texto-principal);">Box</a></li>
                    <li><a href="{{ url('/matrimonioflor') }}" class="transition" style="color: var(--texto-principal);">Matrimonio</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-bold mb-4" style="color: var(--enlaces-titulos);">Ocasiones</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/amor') }}" class="transition" style="color: var(--texto-principal);">Amor</a></li>
                    <li><a href="{{ url('/aniversario') }}" class="transition" style="color: var(--texto-principal);">Aniversario</a></li>
                    <li><a href="{{ url('/hello-kitty') }}" class="transition" style="color: var(--texto-principal);">Hello Kitty</a></li>
                    <li><a href="{{ url('/gato') }}" class="transition" style="color: var(--texto-principal);">Gato</a></li>
                    <li><a href="{{ url('/perro') }}" class="transition" style="color: var(--texto-principal);">Perro</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-bold mb-4" style="color: var(--enlaces-titulos);">Regalos</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/chocolate') }}" class="transition" style="color: var(--texto-principal);">Chocolates</a></li>
                    <li><a href="{{ url('/pinguino') }}" class="transition" style="color: var(--texto-principal);">Peluches</a></li>
                    <li><a href="{{ url('/stich') }}" class="transition" style="color: var(--texto-principal);">Stitch</a></li>
                    <li><a href="{{ url('/vinera') }}" class="transition" style="color: var(--texto-principal);">Vinera</a></li>
                    <li><a href="{{ url('/taza') }}" class="transition" style="color: var(--texto-principal);">Tazas</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t pt-8 text-center text-sm max-w-6xl mx-auto" style="border-color: var(--borde-categorias);">
            <p>© 2025 Detalles y Más. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>