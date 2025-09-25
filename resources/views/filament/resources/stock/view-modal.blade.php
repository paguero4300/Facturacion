<div class="p-6">
    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    {{ __('Información del Producto') }}
                </h3>
                <div class="space-y-2">
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Código:') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $record->product?->code ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Nombre:') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $record->product?->name ?? 'Sin producto' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Descripción:') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $record->product?->description ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    {{ __('Información del Stock') }}
                </h3>
                <div class="space-y-2">
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Almacén:') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $record->warehouse?->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Cantidad Actual:') }}</span>
                        <span class="text-gray-900 dark:text-white font-bold">{{ number_format($record->qty, 2) }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Stock Mínimo:') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ number_format($record->min_qty, 2) }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Estado:') }}</span>
                        @if($record->isLowStock())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                {{ __('Stock Bajo') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                {{ __('OK') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="border-t pt-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                {{ __('Información de la Empresa') }}
            </h3>
            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('Empresa:') }}</span>
                <span class="text-gray-900 dark:text-white">{{ $record->company?->business_name ?? 'N/A' }}</span>
            </div>
        </div>
        
        <div class="border-t pt-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <div>{{ __('Última actualización:') }} {{ $record->updated_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>