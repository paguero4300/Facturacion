<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Guardar Configuración
            </x-filament::button>
        </div>
    </form>
    
    <!-- Categorías Principales -->
    <x-filament::section heading="Categorías Principales">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories as $category)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold">{{ $category['name'] }}</h3>
                        <div class="flex space-x-2">
                            @if($category['show_on_web'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                                    Visible
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                                    Oculto
                                </span>
                            @endif
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-3">{{ $category['description'] }}</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-2" style="background-color: {{ $category['color'] }}"></div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $category['web_group'] }}</span>
                        </div>
                        <div class="flex space-x-1">
                            <button wire:click="toggleCategoryVisibility({{ $category['id'] }})" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                <x-heroicon-o-eye class="h-5 w-5" />
                            </button>
                            <button wire:click="deleteCategory({{ $category['id'] }})" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                <x-heroicon-o-trash class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-4">
            <x-filament::button wire:click="addNewCategory('principales')">
                <x-heroicon-o-plus class="h-5 w-5 -ml-1 mr-2" />
                Agregar Categoría Principal
            </x-filament::button>
        </div>
        
        <!-- Formulario para nuevas categorías principales -->
        @if(!empty($new_categories))
            <div class="mt-6 space-y-4">
                @foreach($new_categories as $index => $category)
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                <input type="text" wire:model="new_categories.{{ $index }}.name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                                <input type="text" wire:model="new_categories.{{ $index }}.description" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                                <input type="color" wire:model="new_categories.{{ $index }}.color" class="w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icono</label>
                                <input type="text" wire:model="new_categories.{{ $index }}.icon" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Orden</label>
                                <input type="number" wire:model="new_categories.{{ $index }}.web_order" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="new_categories.{{ $index }}.status" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-offset-gray-800">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="new_categories.{{ $index }}.show_on_web" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-offset-gray-800">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Mostrar en web</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button wire:click="removeNewCategory('principales', {{ $index }})" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:text-red-200 dark:bg-red-900 dark:hover:bg-red-800">
                                Eliminar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
    
    <!-- Categorías Secundarias -->
    <x-filament::section heading="Categorías Secundarias">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($secondary_categories as $category)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold">{{ $category['name'] }}</h3>
                        <div class="flex space-x-2">
                            @if($category['show_on_web'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                                    Visible
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                                    Oculto
                                </span>
                            @endif
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-3">{{ $category['description'] }}</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-2" style="background-color: {{ $category['color'] }}"></div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $category['web_group'] }}</span>
                        </div>
                        <div class="flex space-x-1">
                            <button wire:click="toggleCategoryVisibility({{ $category['id'] }})" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                <x-heroicon-o-eye class="h-5 w-5" />
                            </button>
                            <button wire:click="deleteCategory({{ $category['id'] }})" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                <x-heroicon-o-trash class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-4">
            <x-filament::button wire:click="addNewCategory('secundarias')">
                <x-heroicon-o-plus class="h-5 w-5 -ml-1 mr-2" />
                Agregar Categoría Secundaria
            </x-filament::button>
        </div>
        
        <!-- Formulario para nuevas categorías secundarias -->
        @if(!empty($new_secondary_categories))
            <div class="mt-6 space-y-4">
                @foreach($new_secondary_categories as $index => $category)
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                <input type="text" wire:model="new_secondary_categories.{{ $index }}.name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                                <input type="text" wire:model="new_secondary_categories.{{ $index }}.description" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                                <input type="color" wire:model="new_secondary_categories.{{ $index }}.color" class="w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icono</label>
                                <input type="text" wire:model="new_secondary_categories.{{ $index }}.icon" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Orden</label>
                                <input type="number" wire:model="new_secondary_categories.{{ $index }}.web_order" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="new_secondary_categories.{{ $index }}.status" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-offset-gray-800">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="new_secondary_categories.{{ $index }}.show_on_web" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-offset-gray-800">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Mostrar en web</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button wire:click="removeNewCategory('secundarias', {{ $index }})" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:text-red-200 dark:bg-red-900 dark:hover:bg-red-800">
                                Eliminar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
    
    <!-- Categorías Especiales -->
    <x-filament::section heading="Categorías Especiales">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($special_categories as $category)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold">{{ $category['name'] }}</h3>
                        <div class="flex space-x-2">
                            @if($category['show_on_web'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                                    Visible
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100">
                                    Oculto
                                </span>
                            @endif
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-3">{{ $category['description'] }}</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-2" style="background-color: {{ $category['color'] }}"></div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $category['web_group'] }}</span>
                        </div>
                        <div class="flex space-x-1">
                            <button wire:click="toggleCategoryVisibility({{ $category['id'] }})" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                <x-heroicon-o-eye class="h-5 w-5" />
                            </button>
                            <button wire:click="deleteCategory({{ $category['id'] }})" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                <x-heroicon-o-trash class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-4">
            <x-filament::button wire:click="addNewCategory('especiales')">
                <x-heroicon-o-plus class="h-5 w-5 -ml-1 mr-2" />
                Agregar Categoría Especial
            </x-filament::button>
        </div>
        
        <!-- Formulario para nuevas categorías especiales -->
        @if(!empty($new_special_categories))
            <div class="mt-6 space-y-4">
                @foreach($new_special_categories as $index => $category)
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                                <input type="text" wire:model="new_special_categories.{{ $index }}.name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                                <input type="text" wire:model="new_special_categories.{{ $index }}.description" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                                <input type="color" wire:model="new_special_categories.{{ $index }}.color" class="w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icono</label>
                                <input type="text" wire:model="new_special_categories.{{ $index }}.icon" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Orden</label>
                                <input type="number" wire:model="new_special_categories.{{ $index }}.web_order" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="new_special_categories.{{ $index }}.status" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-offset-gray-800">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="new_special_categories.{{ $index }}.show_on_web" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-offset-0 focus:ring-indigo-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-offset-gray-800">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Mostrar en web</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button wire:click="removeNewCategory('especiales', {{ $index }})" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-full text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:text-red-200 dark:bg-red-900 dark:hover:bg-red-800">
                                Eliminar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>