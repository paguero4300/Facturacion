<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Company;
use App\Services\QpseGreenterAdapter;
use App\Services\QpseService;
use App\Services\GreenterXmlService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ElectronicInvoiceService
{
    protected QpseGreenterAdapter $adapter;

    public function __construct()
    {
        $qpseService = new QpseService();
        $greenterService = new GreenterXmlService();
        $this->adapter = new QpseGreenterAdapter($qpseService, $greenterService);
    }

    /**
     * Enviar factura electrónica (tipo 01) a SUNAT vía QPse
     */
    public function sendFactura(Invoice $invoice): array
    {
        return $this->processElectronicDocument($invoice, 'invoice');
    }

    /**
     * Enviar boleta de venta (tipo 03) a SUNAT vía QPse
     */
    public function sendBoleta(Invoice $invoice): array
    {
        return $this->processElectronicDocument($invoice, 'invoice');
    }

    /**
     * Enviar nota de crédito (tipo 07) a SUNAT vía QPse
     */
    public function sendNotaCredito(Invoice $invoice): array
    {
        return $this->processElectronicDocument($invoice, 'credit');
    }

    /**
     * Enviar nota de débito (tipo 08) a SUNAT vía QPse
     */
    public function sendNotaDebito(Invoice $invoice): array
    {
        return $this->processElectronicDocument($invoice, 'debit');
    }

    /**
     * Procesar documento electrónico
     */
    protected function processElectronicDocument(Invoice $invoice, string $documentType): array
    {
        try {
            // Verificar que la factura no haya sido enviada ya
            if ($invoice->sunat_status === 'accepted') {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'ALREADY_SENT',
                        'message' => 'El documento ya fue enviado y aceptado por SUNAT'
                    ]
                ];
            }

            // Configurar QPse con credenciales de la empresa
            $this->configureQpseForCompany($invoice->company);

            // Convertir Invoice model a formato compatible con QPse
            $documentData = $this->buildDocumentData($invoice);

            // Log de los datos que se van a enviar
            Log::info('Datos preparados para envío a QPse', [
                'invoice_id' => $invoice->id,
                'full_number' => $invoice->full_number,
                'document_data_keys' => array_keys($documentData),
                'serie' => $documentData['serie'] ?? null,
                'correlativo' => $documentData['correlativo'] ?? null,
                'client_doc' => $documentData['client']['numDoc'] ?? null,
                'total' => $documentData['mtoImpVenta'] ?? null
            ]);

            // Registrar inicio del envío
            Log::info('Iniciando envío de documento electrónico', [
                'invoice_id' => $invoice->id,
                'document_type' => $invoice->document_type,
                'full_number' => $invoice->full_number,
                'qpse_type' => $documentType
            ]);

            // Actualizar estado a "enviado"
            $invoice->update(['sunat_status' => 'sent']);

            // Enviar a QPse
            $result = match($documentType) {
                'invoice' => $this->adapter->sendInvoice($documentData),
                'credit' => $this->adapter->sendCreditNote($documentData),
                'debit' => $this->adapter->sendDebitNote($documentData),
                default => throw new \Exception("Tipo de documento no soportado: $documentType")
            };

            // Log detallado del resultado de QPse
            Log::info('Resultado de QPse para documento', [
                'invoice_id' => $invoice->id,
                'full_number' => $invoice->full_number,
                'result' => $result,
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? null,
                'error' => $result['error'] ?? null,
                'qpse_raw' => $result['qpse_raw'] ?? null
            ]);

            // Procesar resultado
            return $this->processResult($invoice, $result);

        } catch (\Exception $e) {
            Log::error('Error al enviar documento electrónico', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Actualizar estado a error
            $invoice->update([
                'sunat_status' => 'rejected',
                'additional_data' => array_merge($invoice->additional_data ?? [], [
                    'last_error' => $e->getMessage(),
                    'last_error_at' => now()->toISOString()
                ])
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => $e->getCode() ?: 500,
                    'message' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Configurar QPse con credenciales de la empresa
     */
    protected function configureQpseForCompany(Company $company): void
    {
        // Configurar la empresa en el servicio QPse
        $qpseService = app(QpseService::class);
        $qpseService->setCompany($company);
        
        // También configurar en el adaptador
        $this->adapter = new QpseGreenterAdapter($qpseService, new GreenterXmlService());
    }

    /**
     * Convertir Invoice model a formato compatible con QPse
     */
    protected function buildDocumentData(Invoice $invoice): array
    {
        $company = $invoice->company;
        $client = $invoice->client;
        
        // Construir datos básicos del documento
        $data = [
            // Emisor
            'company' => [
                'ruc' => $company->ruc,
                'razonSocial' => $company->business_name,
                'nombreComercial' => $company->commercial_name,
                'address' => [
                    'direccion' => $company->address,
                    'distrito' => $company->district,
                    'provincia' => $company->province,
                    'departamento' => $company->department,
                    'ubigeo' => $company->ubigeo
                ]
            ],

            // Documento
            'tipoDoc' => $invoice->document_type,
            'serie' => $invoice->series,
            'correlativo' => (string) $invoice->number, // Sin padding, como esperado por QPse
            'fechaEmision' => $invoice->issue_date->format('Y-m-d'),
            'horaEmision' => $invoice->issue_time ?? now()->format('H:i:s'),
            'fechaVencimiento' => $invoice->due_date?->format('Y-m-d'),
            'tipoMoneda' => $invoice->currency_code,
            'tipoCambio' => (float) $invoice->exchange_rate,
            'tipoOperacion' => $invoice->operation_type ?? '0101',

            // Cliente
            'client' => [
                'tipoDoc' => $client->document_type,
                'numDoc' => $client->document_number,
                'rznSocial' => $client->business_name,
                'direccion' => $client->address,
                'email' => $client->email,
                'telephone' => $client->phone
            ],

            // Detalle de productos/servicios
            'details' => $this->buildInvoiceDetails($invoice),

            // Totales
            'mtoOperGravadas' => (float) $invoice->subtotal,
            'mtoIGV' => (float) $invoice->igv_amount,
            'valorVenta' => (float) $invoice->subtotal,
            'totalImpuestos' => (float) $invoice->igv_amount,
            'subTotal' => (float) $invoice->subtotal,
            'mtoImpVenta' => (float) $invoice->total_amount,

            // Leyendas
            'legends' => [
                [
                    'code' => '1000',
                    'value' => $this->numberToWords($invoice->total_amount, $invoice->currency_code)
                ]
            ],

            // Condición de pago
            'condicionPago' => $invoice->payment_condition,
            'metodoPago' => $invoice->payment_method
        ];

        // Agregar cuotas si es crédito
        if ($invoice->payment_condition === 'credit' && $invoice->paymentInstallments->count() > 0) {
            $data['cuotas'] = $invoice->paymentInstallments->map(function ($installment) {
                return [
                    'moneda' => $installment->invoice->currency_code,
                    'monto' => (float) $installment->amount,
                    'fechaPago' => $installment->due_date
                ];
            })->toArray();
        }

        return $data;
    }

    /**
     * Construir detalle de la factura
     */
    protected function buildInvoiceDetails(Invoice $invoice): array
    {
        return $invoice->details->map(function ($detail, $index) {
            return [
                'codProducto' => $detail->product_code ?: 'PROD' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'unidad' => $detail->unit_code ?: 'NIU',
                'descripcion' => $detail->description,
                'cantidad' => (float) $detail->quantity,
                'mtoValorUnitario' => (float) $detail->unit_value,
                'mtoValorVenta' => (float) $detail->net_amount,
                'mtoBaseIgv' => (float) $detail->igv_base_amount,
                'porcentajeIgv' => (float) $detail->igv_rate * 100, // Convertir a porcentaje
                'igv' => (float) $detail->igv_amount,
                'tipAfeIgv' => $detail->tax_type ?: '10',
                'totalImpuestos' => (float) $detail->total_taxes,
                'mtoPrecioUnitario' => (float) $detail->unit_price
            ];
        })->toArray();
    }

    /**
     * Procesar resultado de QPse
     */
    protected function processResult(Invoice $invoice, array $result): array
    {
        Log::info('Procesando resultado de QPse', [
            'invoice_id' => $invoice->id,
            'full_number' => $invoice->full_number,
            'result_success' => $result['success'] ?? false,
            'result_keys' => array_keys($result)
        ]);

        if ($result['success']) {
            // Documento aceptado por SUNAT
            $updateData = [
                'sunat_status' => 'accepted',
                'sunat_processed_at' => now(),
                'additional_data' => array_merge($invoice->additional_data ?? [], [
                    'qpse_response' => $result,
                    'processed_at' => now()->toISOString()
                ])
            ];

            // Guardar CDR si está disponible
            if (isset($result['cdr'])) {
                $updateData['additional_data']['cdr_content'] = base64_encode($result['cdr']);
                Log::info('CDR guardado para documento', [
                    'invoice_id' => $invoice->id,
                    'cdr_length' => strlen($result['cdr'])
                ]);
            }

            $invoice->update($updateData);

            Log::info('Documento electrónico aceptado por SUNAT', [
                'invoice_id' => $invoice->id,
                'full_number' => $invoice->full_number,
                'message' => $result['message'] ?? 'Sin mensaje específico'
            ]);

            return [
                'success' => true,
                'message' => $result['message'] ?? 'Documento enviado y aceptado por SUNAT',
                'cdr' => $result['cdr'] ?? null,
                'xml_firmado' => $result['xml_firmado'] ?? null
            ];

        } else {
            // Error al procesar documento
            $errorMessage = $result['error']['message'] ?? 'Error desconocido';
            $errorCode = $result['error']['code'] ?? 'UNKNOWN';
            
            $updateData = [
                'sunat_status' => 'rejected',
                'additional_data' => array_merge($invoice->additional_data ?? [], [
                    'qpse_error' => $result,
                    'error_at' => now()->toISOString(),
                    'last_error_message' => $errorMessage,
                    'last_error_code' => $errorCode
                ])
            ];

            $invoice->update($updateData);

            Log::warning('Documento electrónico rechazado', [
                'invoice_id' => $invoice->id,
                'full_number' => $invoice->full_number,
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'qpse_raw_response' => $result['qpse_raw'] ?? null
            ]);

            return $result;
        }
    }

    /**
     * Verificar si el servicio está configurado
     */
    public function isConfigured(): bool
    {
        return $this->adapter->isConfigured();
    }

    /**
     * Convertir número a palabras
     */
    protected function numberToWords(float $amount, string $currency): string
    {
        $currencyWord = $currency === 'USD' ? 'DÓLARES AMERICANOS' : 'SOLES';
        
        try {
            $formatter = new \NumberFormatter('es_PE', \NumberFormatter::SPELLOUT);
            $integerPart = (int) floor($amount);
            $decimalPart = (int) round(($amount - $integerPart) * 100);
            
            $words = strtoupper($formatter->format($integerPart));
            $decimals = str_pad((string) $decimalPart, 2, '0', STR_PAD_LEFT);
            
            return "SON {$words} CON {$decimals}/100 {$currencyWord}";
        } catch (\Exception $e) {
            return 'SON ' . number_format($amount, 2, '.', ',') . ' ' . $currencyWord;
        }
    }

    /**
     * Reenviar documento a SUNAT (en caso de errores temporales)
     */
    public function resendDocument(Invoice $invoice): array
    {
        // Permitir reenvío solo si está en estado de error o pendiente
        if (!in_array($invoice->sunat_status, ['pending', 'rejected', 'observed'])) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'INVALID_STATUS',
                    'message' => 'El documento no puede ser reenviado en su estado actual'
                ]
            ];
        }

        // Determinar tipo de documento para reenvío
        $documentType = match($invoice->document_type) {
            '01', '03' => 'invoice',
            '07' => 'credit',
            '08' => 'debit',
            default => throw new \Exception("Tipo de documento no soportado para reenvío: {$invoice->document_type}")
        };

        return $this->processElectronicDocument($invoice, $documentType);
    }

    /**
     * Obtener estado de documento en QPse (para seguimiento)
     */
    public function getDocumentStatus(Invoice $invoice): array
    {
        try {
            $company = $invoice->company;
            $this->configureQpseForCompany($company);

            $fileName = "{$company->ruc}-{$invoice->document_type}-{$invoice->series}-{$invoice->number}";
            
            $qpseService = app(QpseService::class);
            $result = $qpseService->consultarTicket($fileName);

            return [
                'success' => true,
                'status' => $result
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => [
                    'code' => $e->getCode() ?: 500,
                    'message' => $e->getMessage()
                ]
            ];
        }
    }
}