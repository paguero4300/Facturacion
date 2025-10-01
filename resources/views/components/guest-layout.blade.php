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

        <style>
            .login-background {
                background: linear-gradient(135deg, #fff6f7 0%, #ffffff 50%, #fff6f7 100%);
                position: relative;
                overflow: hidden;
            }

            .login-background::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -10%;
                width: 500px;
                height: 500px;
                background: radial-gradient(circle, rgba(255, 153, 0, 0.08), transparent);
                border-radius: 50%;
                animation: float 8s ease-in-out infinite;
            }

            .login-background::after {
                content: '';
                position: absolute;
                bottom: -30%;
                left: -10%;
                width: 400px;
                height: 400px;
                background: radial-gradient(circle, rgba(91, 31, 31, 0.06), transparent);
                border-radius: 50%;
                animation: float 10s ease-in-out infinite reverse;
            }

            @keyframes float {
                0%, 100% { transform: translate(0, 0) scale(1); }
                50% { transform: translate(20px, 20px) scale(1.1); }
            }

            .login-card {
                backdrop-filter: blur(10px);
                background: rgba(255, 255, 255, 0.95);
                border: 1px solid rgba(91, 31, 31, 0.08);
            }

            .login-logo {
                animation: fadeInDown 0.8s ease-out;
            }

            .login-form {
                animation: fadeInUp 0.8s ease-out;
            }

            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex items-center justify-center p-4 login-background guest-layout">
            <div class="w-full max-w-md relative z-10">
                <!-- Header -->
                <div class="text-center mb-8 login-logo">
                    <h1 class="text-3xl font-bold mb-2" style="color: var(--enlaces-titulos);">
                        {{ $title ?? 'Bienvenido de vuelta' }}
                    </h1>
                    <p class="text-sm" style="color: var(--texto-principal);">
                        {{ $subtitle ?? 'Inicia sesión para continuar' }}
                    </p>
                </div>

                <!-- Login Form Card -->
                <div class="login-card rounded-2xl shadow-2xl p-8 login-form">
                    {{ $slot }}
                </div>

                <!-- Back to home -->
                <div class="text-center mt-6">
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 text-sm font-medium hover:gap-3 transition-all duration-300"
                       style="color: var(--enlaces-titulos);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver al inicio
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="mt-8 flex justify-center items-center gap-6 text-xs" style="color: var(--texto-principal);">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color: var(--naranja);" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>100% Seguro</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color: var(--azul-claro);" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                        <span>Soporte 24/7</span>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
