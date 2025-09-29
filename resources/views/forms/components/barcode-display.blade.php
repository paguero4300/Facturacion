<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="flex flex-col items-center space-y-4 p-4 bg-white border rounded-lg">
        @if($getBarcodeCode())
            <!-- Código de barras SVG -->
            <div class="bg-white p-4 rounded border">
                {!! $getBarcode() !!}
            </div>

            <!-- Código legible -->
            <div class="text-center">
                <p class="text-sm text-gray-600">Código de Barras:</p>
                <p class="text-lg font-mono font-bold">{{ $getBarcodeCode() }}</p>
            </div>

            <!-- Información adicional -->
            <div class="text-xs text-gray-500 text-center">
                <p>Escanea este código con cualquier lector de códigos de barras</p>
                <p>Formato: Code 128</p>
            </div>
        @else
            <div class="text-center text-gray-500">
                <p>No hay código de barras generado</p>
                <p class="text-xs">Se generará automáticamente al guardar el producto</p>
            </div>
        @endif
    </div>
</x-dynamic-component>