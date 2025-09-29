<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Reporte de Canales de Venta
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Tipo Documento
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Método Pago
                        </th>
                        <th scope="col" class="px-6 py-3 text-right">
                            Cantidad
                        </th>
                        <th scope="col" class="px-6 py-3 text-right">
                            Total
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getReportData() as $row)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $this->getDocumentTypeLabel($row->tipo_documento) }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $this->getPaymentMethodLabel($row->payment_method) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                {{ number_format($row->total_cantidad) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                S/ {{ number_format($row->total_venta, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No hay datos disponibles
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>