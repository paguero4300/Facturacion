<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-sm">
    <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
            <div class="flex items-center gap-1">
                <span class="font-medium text-gray-600 dark:text-gray-400">Código:</span>
                <span class="text-gray-900 dark:text-gray-100 font-mono text-xs">{{ $record->code }}</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="font-medium text-gray-600 dark:text-gray-400">Categoría:</span>
                <span class="text-gray-900 dark:text-gray-100 text-xs">{{ $record->category?->name ?? 'Sin categoría' }}</span>
            </div>
        </div>
        
        <div class="space-y-1">
            <div class="flex items-center gap-1">
                <span class="font-medium text-gray-600 dark:text-gray-400">Precio:</span>
                <span class="text-green-600 dark:text-green-400 font-semibold text-xs">S/ {{ number_format($record->sale_price, 2) }}</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="font-medium text-gray-600 dark:text-gray-400">Stock:</span>
                <span class="text-gray-900 dark:text-gray-100 text-xs">
                    {{ $record->track_inventory ? number_format($record->current_stock, 2) : 'No controlado' }}
                </span>
            </div>
        </div>
    </div>
</div>