<x-guest-layout title="Crear Cuenta" subtitle="Únete a nuestra comunidad">
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-semibold mb-2" style="color: var(--enlaces-titulos);">
                Nombre Completo
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors">
                    <svg class="h-5 w-5 text-gray-400 transition-colors" style="color: var(--texto-principal);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl transition-all bg-white hover:border-gray-400 auth-input"
                    placeholder="Juan Pérez"
                >
            </div>
            @error('name')
                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold mb-2" style="color: var(--enlaces-titulos);">
                Correo Electrónico
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors">
                    <svg class="h-5 w-5 text-gray-400 transition-colors" style="color: var(--texto-principal);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl transition-all bg-white hover:border-gray-400 auth-input"
                    placeholder="tu@email.com"
                >
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-semibold mb-2" style="color: var(--enlaces-titulos);">
                Contraseña
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors">
                    <svg class="h-5 w-5 text-gray-400 transition-colors" style="color: var(--texto-principal);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl transition-all bg-white hover:border-gray-400 auth-input"
                    placeholder="Mínimo 8 caracteres"
                >
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold mb-2" style="color: var(--enlaces-titulos);">
                Confirmar Contraseña
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors">
                    <svg class="h-5 w-5 text-gray-400 transition-colors" style="color: var(--texto-principal);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="block w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl transition-all bg-white hover:border-gray-400 auth-input"
                    placeholder="Repite tu contraseña"
                >
            </div>
            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="w-full text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 active:scale-98 flex items-center justify-center gap-2 group auth-btn"
        >
            <span>Crear Cuenta</span>
            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </form>

    <!-- Login Link -->
    <div class="mt-8 text-center">
        <p class="text-sm text-gray-600">
            ¿Ya tienes una cuenta?
            <a href="{{ route('login') }}"
               class="font-bold hover:underline transition-colors inline-flex items-center gap-1 group"
               style="color: var(--azul-primario);">
                <span>Inicia sesión</span>
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </p>
    </div>

    <!-- Divider -->
    <div class="mt-8 pt-6 border-t border-gray-100">
        <p class="text-xs text-center text-gray-500 leading-relaxed">
            Al registrarte, aceptas nuestros
            <a href="#" class="underline hover:text-gray-700">Términos de Servicio</a> y
            <a href="#" class="underline hover:text-gray-700">Política de Privacidad</a>
        </p>
    </div>
    
    <style>
        .auth-input:focus {
            outline: none;
            border-color: var(--naranja);
            box-shadow: 0 0 0 3px rgba(255, 153, 0, 0.1);
        }
        
        .group:focus-within svg {
            color: var(--naranja) !important;
        }
        
        .auth-btn {
            background: linear-gradient(135deg, var(--naranja) 0%, var(--azul-claro) 100%);
        }
        
        .auth-btn:hover {
            opacity: 0.9;
        }
    </style>
</x-guest-layout>
