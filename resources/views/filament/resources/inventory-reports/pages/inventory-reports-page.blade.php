<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Pestañas -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ request()->url() }}?tab=existencias" 
                   class="{{ $activeTab === 'existencias' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Existencias Actuales
                </a>
                <a href="{{ request()->url() }}?tab=kardex" 
                   class="{{ $activeTab === 'kardex' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Kardex
                </a>
                <a href="{{ request()->url() }}?tab=bajo-stock" 
                   class="{{ $activeTab === 'bajo-stock' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Bajo Stock
                </a>
            </nav>
        </div>

        <!-- Contenido de las pestañas -->
        <div class="mt-6">
            @if($activeTab === 'existencias')
                <div class="mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Existencias Actuales por Almacén</h2>
                    <p class="text-sm text-gray-600">Visualiza el stock actual de todos los productos por almacén</p>
                </div>
                
                <!-- Filtro de almacén -->
                <div class="mb-4">
                    <select wire:model="selectedWarehouse" class="block w-64 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Todos los almacenes</option>
                        @foreach(\App\Models\Warehouse::all() as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{ $this->table }}
                
            @elseif($activeTab === 'kardex')
                <div class="mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Kardex</h2>
                    <p class="text-sm text-gray-600">Historial de movimientos de inventario (Próximamente)</p>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <div class="mx-auto w-12 h-12 text-gray-400 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Kardex en Desarrollo</h3>
                    <p class="text-gray-600">Esta funcionalidad estará disponible próximamente. Aquí podrás ver el historial detallado de movimientos de inventario.</p>
                </div>
                
            @elseif($activeTab === 'bajo-stock')
                <div class="mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Productos con Bajo Stock</h2>
                    <p class="text-sm text-gray-600">Productos que han alcanzado o están por debajo del stock mínimo</p>
                </div>
                
                {{ $this->table }}
            @endif
        </div>
    </div>
</x-filament-panels::page>