<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Iniciar Sesión</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            <!-- Left Side - Image/Brand -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-pink-500 via-rose-500 to-pink-600 relative overflow-hidden">
                <div class="absolute inset-0 bg-black opacity-10"></div>
                <div class="relative z-10 flex flex-col justify-center items-center text-white p-12">
                    <div class="mb-8">
                        <img src="{{ asset('logos/logo_horizontal.png') }}" alt="Logo" class="h-24 w-auto filter brightness-0 invert">
                    </div>
                    <h1 class="text-4xl font-bold mb-4 text-center">Bienvenido de vuelta</h1>
                    <p class="text-xl text-center text-pink-100 max-w-md">
                        Accede a tu cuenta para gestionar tus pedidos y disfrutar de una experiencia personalizada
                    </p>
                    <div class="mt-12 grid grid-cols-3 gap-8 text-center">
                        <div>
                            <div class="text-3xl font-bold">500+</div>
                            <div class="text-sm text-pink-100">Productos</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold">1000+</div>
                            <div class="text-sm text-pink-100">Clientes</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold">24/7</div>
                            <div class="text-sm text-pink-100">Soporte</div>
                        </div>
                    </div>
                </div>
                <!-- Decorative circles -->
                <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-5 rounded-full -mr-48 -mt-48"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-white opacity-5 rounded-full -ml-32 -mb-32"></div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50">
                <div class="w-full max-w-md">
                    <!-- Logo for mobile -->
                    <div class="lg:hidden text-center mb-8">
                        <img src="{{ asset('logos/logo_horizontal.png') }}" alt="Logo" class="h-16 w-auto mx-auto mb-4">
                    </div>

                    <div class="bg-white rounded-2xl shadow-xl p-8">
                        {{ $slot }}
                    </div>

                    <!-- Back to home -->
                    <div class="text-center mt-6">
                        <a href="{{ route('home') }}" class="text-sm hover:underline transition" style="color: var(--enlaces-titulos);">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
