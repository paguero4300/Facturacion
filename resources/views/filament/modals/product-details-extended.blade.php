<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-sm">
    <div class="grid grid-cols-2 gap-3 mb-3">
        <div class="space-y-1">
            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Información</div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Código:</span>
                <span class="text-gray-900 dark:text-gray-100 font-mono text-xs">{{ $record->code }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Tipo:</span>
                <span class="text-xs px-1 py-0.5 rounded {{ $record->product_type === 'product' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ $record->product_type === 'product' ? 'Producto' : 'Servicio' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Categoría:</span>
                <span class="text-gray-900 dark:text-gray-100 text-xs">{{ $record->category?->name ?? 'Sin categoría' }}</span>
            </div>
        </div>
        
        <div class="space-y-1">
            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Precios & Stock</div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Precio:</span>
                <span class="text-green-600 dark:text-green-400 font-semibold text-xs">S/ {{ number_format($record->sale_price, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Stock:</span>
                <span class="text-gray-900 dark:text-gray-100 text-xs {{ $record->track_inventory && $record->current_stock <= $record->minimum_stock ? 'text-red-600' : '' }}">
                    {{ $record->track_inventory ? number_format($record->current_stock, 2) : 'No controlado' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Estado:</span>
                <span class="text-xs px-1 py-0.5 rounded {{ $record->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $record->status === 'active' ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>
    
    @if($record->description)
        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Descripción</div>
            <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">{{ Str::limit($record->description, 120) }}</p>
        </div>
    @endif
</div>