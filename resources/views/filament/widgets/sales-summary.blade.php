<div class="space-y-6 p-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Ventas</div>
            <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                S/ {{ number_format($totalVentas, 2) }}
            </div>
        </div>

        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
            <div class="text-sm text-green-600 dark:text-green-400 font-medium">Total IGV</div>
            <div class="text-2xl font-bold text-green-900 dark:text-green-100">
                S/ {{ number_format($totalIGV, 2) }}
            </div>
        </div>

        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
            <div class="text-sm text-purple-600 dark:text-purple-400 font-medium">Items Vendidos</div>
            <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                {{ number_format($totalItems, 0) }}
            </div>
        </div>

        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg">
            <div class="text-sm text-orange-600 dark:text-orange-400 font-medium">Venta Promedio</div>
            <div class="text-2xl font-bold text-orange-900 dark:text-orange-100">
                S/ {{ number_format($ventaPromedio, 2) }}
            </div>
        </div>
    </div>

    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Análisis por Tipo Fiscal</h3>
        <div class="space-y-3">
            @foreach($porTipoFiscal as $tipo)
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full {{ $tipo['tipo'] === 'Gravado' ? 'bg-green-500' : ($tipo['tipo'] === 'Exonerado' ? 'bg-yellow-500' : 'bg-blue-500') }}"></span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $tipo['tipo'] }}</span>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                            S/ {{ number_format($tipo['total'], 2) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $tipo['cantidad'] }} líneas
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
        Resumen generado con los filtros aplicados • {{ now()->format('d/m/Y H:i') }}
    </div>
</div>