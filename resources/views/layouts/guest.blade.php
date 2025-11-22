<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Detalles y Más - Inicio de sesión y registro">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'Iniciar Sesión' }} - Detalles y Más</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
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
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--fondo-principal) 0%, #f8f9ff 50%, var(--fondo-principal) 100%);
            min-height: 100vh;
        }
        
        /* Animaciones suaves */
        @keyframes float-gentle {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        
        @keyframes gradient-flow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .auth-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        
        .auth-header-gradient {
            background: linear-gradient(135deg, var(--naranja) 0%, var(--azul-claro) 50%, var(--azul-primario) 100%);
            background-size: 200% 200%;
            animation: gradient-flow 8s ease-in-out infinite;
        }
        
        /* Floating shapes */
        .shape-bg {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float-gentle 8s ease-in-out infinite;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: var(--naranja);
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 250px;
            height: 250px;
            background: var(--azul-claro);
            bottom: 15%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape-3 {
            width: 200px;
            height: 200px;
            background: var(--azul-primario);
            top: 50%;
            left: 50%;
            animation-delay: 4s;
        }
    </style>
</head>

<body class="bg-white text-gray-900">
    {{-- Header incluido igual que en el index --}}
    @include('partials.header')
    
    <main class="relative min-h-screen flex items-center justify-center py-24 px-4 overflow-hidden">
        <!-- Formas flotantes de fondo -->
        <div class="shape-bg shape-1"></div>
        <div class="shape-bg shape-2"></div>
        <div class="shape-bg shape-3"></div>
        
        <!-- Contenedor principal -->
        <div class="relative z-10 w-full max-w-md">
            <!-- Logo/Marca -->
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ asset('logos/logook.png') }}" alt="Detalles y Más" class="h-16 w-auto mx-auto mb-4 drop-shadow-lg">
                </a>
                <h1 class="text-3xl font-bold mb-2" style="color: var(--enlaces-titulos);">
                    {{ $title ?? 'Iniciar Sesión' }}
                </h1>
                <p class="text-sm" style="color: var(--texto-principal);">
                    {{ $subtitle ?? 'Bienvenido de nuevo' }}
                </p>
            </div>
            
            <!-- Tarjeta de autenticación -->
            <div class="auth-container p-8">
                {{ $slot }}
            </div>
        </div>
    </main>

    <!-- Scripts del header -->
    <script src="{{ asset('js/hero-animations.js') }}"></script>
    <script src="{{ asset('js/warehouse-modal.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
