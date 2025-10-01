<!--
    =============================================
    SECCIÓN 6: FORMULARIO DE CONTACTO
    =============================================
    - Formulario completo para que los clientes se comuniquen
    - Campos para nombre, teléfono, correo electrónico y mensaje
    - Diseño centrado con fondo blanco y sombras suaves
-->
<!-- Formulario de Contacto -->
<section id="contacto" class="container mx-auto px-4 py-16">
    <div class="max-w-2xl mx-auto rounded-2xl shadow-xl p-8 md:p-12 relative overflow-hidden" style="background-color: var(--fondo-footer);">
        <!-- Imagen de fondo para el formulario de contacto -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('logos/contact_form.jpg') }}" alt="Fondo de formulario de contacto" class="w-full h-full object-cover opacity-20">
        </div>
        
        <div class="relative z-10">
            <div class="text-center mb-8">
                <p class="text-sm mb-2 font-semibold tracking-wide uppercase" style="color: var(--naranja);">Contacta Con Nosotros</p>
                <h2 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--enlaces-titulos);">¿Tienes dudas? Estamos aquí para ayudarte
                </h2>
                <p class="" style="color: var(--texto-principal);">Completa el formulario y te responderemos lo más pronto posible</p>
            </div>
        <form class="space-y-5 relative z-10" method="POST" action="{{ route('contact.submit') }}">
            @csrf
            <div>
                <label class="block font-semibold mb-2" style="color: var(--texto-principal);">Nombre *</label>
                <input type="text" name="name" placeholder="Tu nombre completo" required
                    class="w-full px-4 py-3 border rounded-lg focus:outline-none transition resize-none" style="border-color: var(--borde-input); background-color: var(--fondo-input); color: var(--texto-input);">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block font-semibold mb-2" style="color: var(--texto-principal);">Teléfono *</label>
                <input type="tel" name="phone" placeholder="Tu número de teléfono" required
                    class="w-full px-4 py-3 border rounded-lg focus:outline-none transition resize-none" style="border-color: var(--borde-input); background-color: var(--fondo-input); color: var(--texto-input);">
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block font-semibold mb-2" style="color: var(--texto-principal);">Correo Electrónico *</label>
                <input type="email" name="email" placeholder="tu@email.com" required
                    class="w-full px-4 py-3 border rounded-lg focus:outline-none transition resize-none" style="border-color: var(--borde-input); background-color: var(--fondo-input); color: var(--texto-input);">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block font-semibold mb-2" style="color: var(--texto-principal);">Tu Mensaje *</label>
                <textarea name="message" placeholder="Escríbenos tu mensaje aquí..." rows="5" required
                    class="w-full px-4 py-3 border rounded-lg focus:outline-none transition resize-none" style="border-color: var(--borde-input); background-color: var(--fondo-input); color: var(--texto-input);"></textarea>
                @error('message')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                class="w-full text-white py-4 rounded-lg transition font-semibold text-lg shadow-md hover:shadow-lg" style="background-color: var(--naranja);">Enviar
                Mensaje</button>
        </form>
    </div>
</section>