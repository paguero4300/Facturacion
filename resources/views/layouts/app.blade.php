<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Detalles y Más - Flores frescas y regalos únicos para cada ocasión especial. Disponibles 24/7 para tus momentos importantes.">
    <meta name="keywords" content="flores, regalos, arreglos florales, detalles, ocasiones especiales">
    <meta name="author" content="Detalles y Más">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Detalles y Más - Flores y Regalos Únicos')">
    <meta property="og:description" content="Creamos momentos especiales con flores frescas y regalos únicos para cada ocasión importante de tu vida.">
    <meta property="og:image" content="{{ asset('logos/herosection.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="@yield('title', 'Detalles y Más - Flores y Regalos Únicos')">
    <meta property="twitter:description" content="Creamos momentos especiales con flores frescas y regalos únicos para cada ocasión importante de tu vida.">
    <meta property="twitter:image" content="{{ asset('logos/herosection.png') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <title>@yield('title', 'Detalles y Más - Tienda de Regalos')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Critical CSS para Hero (inline para optimizar LCP) -->
    <style>
        /* Variables críticas */
        :root {
            --naranja: #ff9900;
            --azul-claro: #1ea0c3;
            --azul-primario: #007cba;
            --fondo-principal: #fff6f7;
            --texto-principal: #6e6d76;
            --enlaces-titulos: #5b1f1f;
        }
        
        /* Layout crítico del hero */
        .hero-modern {
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, var(--fondo-principal) 0%, #f8f9ff 50%, var(--fondo-principal) 100%);
        }
        
        .hero-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
            position: relative;
            z-index: 10;
        }
        
        .hero-grid {
            display: grid;
            gap: 2rem;
            align-items: center;
            min-height: 100vh;
            padding: 3rem 0;
        }
        
        @media (min-width: 1024px) {
            .hero-container { padding: 0 2rem; }
            .hero-grid {
                grid-template-columns: 1fr 1fr;
                gap: 4rem;
                padding: 0;
            }
        }
        
        .hero-gradient-text {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.1;
            background: linear-gradient(90deg, var(--naranja) 0%, var(--azul-claro) 50%, var(--azul-primario) 100%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            background-size: 200% 100%;
        }
        
        @media (min-width: 768px) { .hero-gradient-text { font-size: 3.5rem; } }
        @media (min-width: 1024px) { .hero-gradient-text { font-size: 4rem; } }
        @media (min-width: 1280px) { .hero-gradient-text { font-size: 5rem; } }
        
        .hero-content, .hero-visual {
            opacity: 0;
            transform: translateY(30px);
        }
        
        .hero-content.animate-fade-in-up, .hero-visual.animate-fade-in-up {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }
    </style>
    
    <!-- CSS no crítico cargado de forma asíncrona -->
    <link rel="preload" href="{{ asset('css/styles.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('css/styles.css') }}"></noscript>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @stack('styles')
</head>

<body style="background-color: var(--fondo-principal);">
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    <!-- Modal de Productos por Almacén -->
    @include('components.warehouse-modal')

    @include('partials.footer')

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- Hero Animations JS -->
    <script src="{{ asset('js/hero-animations.js') }}"></script>
    
    <!-- Warehouse Modal Script -->
    <script src="{{ asset('js/warehouse-modal.js') }}"></script>

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
