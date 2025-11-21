<!--
    =============================================
    SECCIÓN: PIE DE PÁGINA (Rediseño Estilo Rosaliz)
    =============================================
-->
<footer class="bg-gray-50 pt-16 pb-8 text-gray-600">
    <div class="container mx-auto px-4">
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4 mb-12">
            <!-- Columna 1: Acerca de -->
            <div>
                <div class="mb-6">
                    <img src="{{ asset('logos/logook.png') }}" alt="Detalles y Más Flores" class="h-16 w-auto object-contain mix-blend-multiply">
                </div>
                <h4 class="mb-4 text-lg font-bold text-gray-900">Acerca de Nosotros</h4>
                <p class="text-sm leading-relaxed mb-4">
                    Somos especialistas en transmitir emociones a través de detalles únicos y flores frescas. Creamos momentos inolvidables para ti y tus seres queridos.
                </p>
                <div class="space-y-2 text-sm">
                    <p><i class="fas fa-phone mr-2 text-[var(--naranja)]"></i> {{ $webConfig->telefono_huancayo ?? '(+51) 944 492 316' }}</p>
                    <p><i class="fas fa-envelope mr-2 text-[var(--naranja)]"></i> {{ $webConfig->email ?? 'contacto@detalles.com' }}</p>
                </div>
            </div>

            <!-- Columna 2: Enlaces Rápidos -->
            <div>
                <h4 class="mb-4 text-lg font-bold text-gray-900">Enlaces Rápidos</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-[var(--naranja)] transition-colors">Inicio</a></li>
                    <li><a href="{{ route('shop.index') }}" class="hover:text-[var(--naranja)] transition-colors">Catálogo</a></li>
                    <li><a href="#" class="hover:text-[var(--naranja)] transition-colors">Nosotros</a></li>
                    <li><a href="#" class="hover:text-[var(--naranja)] transition-colors">Contacto</a></li>
                </ul>
            </div>

            <!-- Columna 3: Categorías -->
            <div>
                <h4 class="mb-4 text-lg font-bold text-gray-900">Categorías Populares</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/rosas-flor') }}" class="hover:text-[var(--naranja)] transition-colors">Rosas</a></li>
                    <li><a href="{{ url('/girasoles-flor') }}" class="hover:text-[var(--naranja)] transition-colors">Girasoles</a></li>
                    <li><a href="{{ url('/tulipanes-flor') }}" class="hover:text-[var(--naranja)] transition-colors">Tulipanes</a></li>
                    <li><a href="{{ url('/boxflor') }}" class="hover:text-[var(--naranja)] transition-colors">Box de Flores</a></li>
                    <li><a href="{{ url('/amor') }}" class="hover:text-[var(--naranja)] transition-colors">Ocasión: Amor</a></li>
                </ul>
            </div>

            <!-- Columna 4: Síguenos -->
            <div>
                <h4 class="mb-4 text-lg font-bold text-gray-900">Síguenos</h4>
                <div class="flex gap-4">
                    <a href="{{ $webConfig->facebook ?? '#' }}" target="_blank" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm hover:bg-[#1877F2] hover:text-white transition-colors text-gray-600">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="{{ $webConfig->instagram ?? '#' }}" target="_blank" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm hover:bg-[#E4405F] hover:text-white transition-colors text-gray-600">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="{{ $webConfig->tiktok ?? '#' }}" target="_blank" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm hover:bg-[#000000] hover:text-white transition-colors text-gray-600">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-gray-200 pt-8 text-center text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} Detalles y Más. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $webConfig->telefono_huancayo ?? '51944492316') }}" target="_blank" rel="noopener noreferrer" class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition-transform hover:scale-110 hover:bg-[#20bd5a]" aria-label="Chat on WhatsApp">
    <i class="fab fa-whatsapp text-3xl"></i>
</a>