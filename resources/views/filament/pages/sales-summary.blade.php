<div class="space-y-6">
    {{-- Resumen General --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-success-50 dark:bg-success-900/20 p-4 rounded-lg border border-success-200 dark:border-success-800">
            <div class="flex items-center">
                <div class="p-2 bg-success-100 dark:bg-success-800 rounded-lg">
                    <x-heroicon-o-currency-dollar class="w-6 h-6 text-success-600 dark:text-success-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-success-600 dark:text-success-400">Total de Ventas</p>
                    <p class="text-2xl font-bold text-success-900 dark:text-success-100">
                        S/ {{ number_format($totalVentas, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-info-50 dark:bg-info-900/20 p-4 rounded-lg border border-info-200 dark:border-info-800">
            <div class="flex items-center">
                <div class="p-2 bg-info-100 dark:bg-info-800 rounded-lg">
                    <x-heroicon-o-document-text class="w-6 h-6 text-info-600 dark:text-info-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-info-600 dark:text-info-400">Cantidad de Comprobantes</p>
                    <p class="text-2xl font-bold text-info-900 dark:text-info-100">
                        {{ number_format($cantidadComprobantes) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ventas por Tipo de Comprobante --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Ventas por Tipo de Comprobante
        </h3>
        <div class="space-y-3">
            @foreach($ventasPorTipo as $tipo)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($tipo->document_type === 'factura') bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200
                            @elseif($tipo->document_type === 'boleta') bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200
                            @elseif($tipo->document_type === 'nota_credito') bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $tipo->document_type)) }}
                        </span>
                        <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $tipo->cantidad }} comprobantes
                        </span>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        S/ {{ number_format($tipo->total, 2) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Ventas por Método de Pago --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Ventas por Método de Pago
        </h3>
        <div class="space-y-3">
            @foreach($ventasPorMetodo as $metodo)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($metodo->payment_method === 'efectivo') bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200
                            @elseif($metodo->payment_method === 'tarjeta') bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200
                            @elseif($metodo->payment_method === 'transferencia') bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200
                            @elseif($metodo->payment_method === 'credito') bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200
                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @endif">
                            {{ ucfirst($metodo->payment_method) }}
                        </span>
                        <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $metodo->cantidad }} comprobantes
                        </span>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        S/ {{ number_format($metodo->total, 2) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>