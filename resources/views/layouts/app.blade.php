<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Detalles - Tienda de Regalos')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @stack('styles')
</head>

<body style="background-color: var(--fondo-principal);">
    @include('partials.header')
    
    <main>
        @yield('content')
    </main>
    
    @include('partials.footer')
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    @stack('scripts')
    
    <!-- Script para manejar la carga diferida de imágenes -->
    @verbatim
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Función para cargar imágenes de forma diferida
            const lazyImages = document.querySelectorAll('.lazy-load');

            // Configurar el Intersection Observer para detectar cuando las imágenes entran en el viewport
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });

            // Observar todas las imágenes con la clase lazy-load
            lazyImages.forEach(img => {
                imageObserver.observe(img);
            });

            // Manejar errores de carga de imágenes
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('error', function () {
                    // Si la imagen no carga, mostrar un placeholder con el texto del alt
                    if (!this.src.includes('via.placeholder.com')) {
                        const altText = this.alt || 'Imagen no disponible';
                        const width = this.getAttribute('width') || '600';
                        const height = this.getAttribute('height') || '400';
                        this.src = `https://via.placeholder.com/${width}x${height}?text=${encodeURIComponent(altText)}`;
                    }
                });
            });
        });
    </script>
    @endverbatim
</body>

</html>