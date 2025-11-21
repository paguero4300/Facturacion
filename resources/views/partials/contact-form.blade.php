<!--
    =============================================
    SECCIÓN: FORMULARIO DE CONTACTO (Rediseño Estilo Rosaliz)
    =============================================
-->
<section id="contacto" class="container mx-auto px-4 py-16">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="mb-2 text-3xl font-bold text-gray-900">Contáctanos</h2>
            <div class="mx-auto mb-4 h-1 w-16 bg-[var(--naranja)]"></div>
            <p class="text-gray-600">
                ¿Tienes alguna duda o pedido especial? Escríbenos.
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 md:p-10">
            <form class="space-y-6" method="POST" action="{{ route('contact.submit') }}">
                @csrf
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo</label>
                        <input type="text" name="name" required
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[var(--naranja)] focus:ring-1 focus:ring-[var(--naranja)] outline-none transition-colors"
                            placeholder="Tu nombre">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                        <input type="tel" name="phone" required
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[var(--naranja)] focus:ring-1 focus:ring-[var(--naranja)] outline-none transition-colors"
                            placeholder="Tu teléfono">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[var(--naranja)] focus:ring-1 focus:ring-[var(--naranja)] outline-none transition-colors"
                        placeholder="tu@email.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mensaje</label>
                    <textarea name="message" rows="4" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:border-[var(--naranja)] focus:ring-1 focus:ring-[var(--naranja)] outline-none transition-colors"
                        placeholder="¿En qué podemos ayudarte?"></textarea>
                    @error('message')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="text-center">
                    <button type="submit"
                        class="inline-block w-full md:w-auto px-8 py-3 bg-[var(--naranja)] text-white font-bold rounded-full hover:opacity-90 transition-opacity shadow-md">
                        ENVIAR MENSAJE
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>