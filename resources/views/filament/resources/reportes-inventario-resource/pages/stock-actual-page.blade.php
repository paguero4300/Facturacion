<x-filament-panels::page>
    <!-- Tabs de Navegación -->
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('filament.admin.resources.reportes-inventario.stock-actual') }}"
               class="border-orange-500 text-orange-600 whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium flex items-center">
                <span class="mr-2">📦</span>
                Stock Actual
            </a>
            <a href="{{ route('filament.admin.resources.reportes-inventario.stock-minimo') }}"
               class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium flex items-center transition-colors">
                <span class="mr-2">⚠️</span>
                Stock Mínimo
            </a>
            <a href="{{ route('filament.admin.resources.reportes-inventario.kardex') }}"
               class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium flex items-center transition-colors">
                <span class="mr-2">📋</span>
                Kardex
            </a>
        </nav>
    </div>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">{{ $this->getHeading() }}</h2>
                @if($this->getSubheading())
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $this->getSubheading() }}</p>
                @endif
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>