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
                    <img src="{{ asset('logos/logo_horizontal.png') }}" alt="Detalles y Más" class="h-10 w-auto object-contain" style="color: var(--naranja);">
                </div>
                <h3 class="font-bold mb-3" style="color: var(--enlaces-titulos);">Detalles</h3>
                <p class="text-sm mb-2">📞 (+51) 944 492 316</p>
                <p class="text-sm mb-2">✉️ contacto@detalles.com</p>
                <p class="text-sm">🕒 Lun - Dom: 9:00 - 20:00</p>
            </div>
            <div>
                <h3 class="font-bold mb-4" style="color: var(--enlaces-titulos);">Arreglos</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('rosas') }}" class="transition" style="color: var(--texto-principal);">Rosas</a></li>
                    <li><a href="{{ route('girasoles') }}" class="transition" style="color: var(--texto-principal);">Girasoles</a></li>
                    <li><a href="{{ route('flores-mixtas') }}" class="transition" style="color: var(--texto-principal);">Flores Mixtas</a></li>
                    <li><a href="{{ route('lirios') }}" class="transition" style="color: var(--texto-principal);">Lirios</a></li>
                    <li><a href="{{ route('tulipanes') }}" class="transition" style="color: var(--texto-principal);">Tulipanes</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-bold mb-4" style="color: var(--enlaces-titulos);">Ocasiones</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('amor') }}" class="transition" style="color: var(--texto-principal);">Amor</a></li>
                    <li><a href="{{ route('aniversario') }}" class="transition" style="color: var(--texto-principal);">Aniversario</a></li>
                    <li><a href="{{ route('cumpleanos') }}" class="transition" style="color: var(--texto-principal);">Cumpleaños</a></li>
                    <li><a href="{{ route('graduacion') }}" class="transition" style="color: var(--texto-principal);">Graduación</a></li>
                    <li><a href="{{ route('nacimiento') }}" class="transition" style="color: var(--texto-principal);">Nacimiento</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-bold mb-4" style="color: var(--enlaces-titulos);">Festivos</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('san-valentin') }}" class="transition" style="color: var(--texto-principal);">San Valentín</a></li>
                    <li><a href="{{ route('dia-madre') }}" class="transition" style="color: var(--texto-principal);">Día de la Madre</a></li>
                    <li><a href="{{ route('dia-padre') }}" class="transition" style="color: var(--texto-principal);">Día del Padre</a></li>
                    <li><a href="{{ route('navidad') }}" class="transition" style="color: var(--texto-principal);">Navidad</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t pt-8 text-center text-sm max-w-6xl mx-auto" style="border-color: var(--borde-categorias);">
            <p>© 2025 Detalles y Más. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>