<div class="space-y-4">
    @if($getRecord()->hasPaymentEvidence())
        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Comprobante de Pago</h4>
                <div class="flex gap-2">
                    <a href="{{ route('payment.evidence.view', $getRecord()) }}" 
                       target="_blank"
                       class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full hover:bg-blue-200 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Ver Completo
                    </a>
                    <a href="{{ route('payment.evidence.download', $getRecord()) }}"
                       class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full hover:bg-green-200 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Descargar
                    </a>
                </div>
            </div>
            
            @php
                $evidencePath = $getRecord()->payment_evidence_path;
                $fileExtension = pathinfo($evidencePath, PATHINFO_EXTENSION);
                $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif']);
                $isPdf = strtolower($fileExtension) === 'pdf';
            @endphp
            
            @if($isImage)
                <div class="relative">
                    <img src="{{ route('payment.evidence.view', $getRecord()) }}" 
                         alt="Comprobante de pago"
                         class="max-w-full h-auto max-h-96 mx-auto border border-gray-300 rounded-lg shadow-sm">
                </div>
            @elseif($isPdf)
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-red-500 mb-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-gray-600 mb-2">Archivo PDF del comprobante</p>
                    <p class="text-xs text-gray-500">Haz clic en "Ver Completo" para abrir el archivo</p>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-sm text-gray-600 mb-2">Archivo de comprobante ({{ strtoupper($fileExtension) }})</p>
                    <p class="text-xs text-gray-500">Haz clic en "Ver Completo" para abrir el archivo</p>
                </div>
            @endif
            
            <div class="mt-3 text-xs text-gray-500">
                <p><strong>Archivo:</strong> {{ basename($evidencePath) }}</p>
                <p><strong>Subido:</strong> {{ $getRecord()->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <p class="text-sm text-gray-500">No hay comprobante de pago disponible</p>
        </div>
    @endif
</div>