<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QpseGreenterAdapter
{
    protected QpseService $qpseService;
    protected GreenterXmlService $greenterService;

    public function __construct(QpseService $qpseService, GreenterXmlService $greenterService)
    {
        $this->qpseService = $qpseService;
        $this->greenterService = $greenterService;
    }

    /**
     * Enviar factura usando QPse
     */
    public function sendInvoice(array $invoiceData): array
    {
        return $this->processDocument('invoice', $invoiceData);
    }

    /**
     * Enviar nota de crédito usando QPse
     */
    public function sendCreditNote(array $creditData): array
    {
        return $this->processDocument('credit', $creditData);
    }

    /**
     * Enviar nota de débito usando QPse
     */
    public function sendDebitNote(array $debitData): array
    {
        return $this->processDocument('debit', $debitData);
    }

    /**
     * Enviar guía de remisión usando QPse
     */
    public function sendDespatch(array $despatchData): array
    {
        return $this->processDocument('despatch', $despatchData);
    }

    /**
     * Procesar documento genérico
     */
    protected function processDocument(string $type, array $documentData): array
    {
        try {
            // Paso 1: Generar XML usando Greenter (sin enviar a SUNAT)
            $xml = $this->generateXmlWithGreenter($type, $documentData);
            
            // Paso 2: Crear nombre de archivo basado en el documento
            $fileName = $this->generateFileName($type, $documentData);
            
            // Paso 3: Guardar XML si está habilitado (para debug)
            if (config('qpse.integration.save_xmls', false)) {
                $this->saveXmlFile($fileName, $xml);
            }
            
            // Paso 4: Procesar a través de QPse
            $qpseResult = $this->qpseService->procesarDocumento($fileName, $xml);
            
            // Paso 5: Formatear respuesta compatible con Greenter
            return $this->formatGreenterResponse($qpseResult);
            
        } catch (\Exception $e) {
            Log::error('Error procesando documento con QPse', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
     * Generar XML usando Greenter (sin enviar a SUNAT)
     */
    protected function generateXmlWithGreenter(string $type, array $data): string
    {
        // Obtener la empresa desde los datos si está disponible
        $companyModel = null;
        if (isset($data['company']['ruc'])) {
            $companyModel = \App\Models\Company::where('ruc', $data['company']['ruc'])->first();
        }
        
        switch ($type) {
            case 'invoice':
                return $this->greenterService->generateInvoiceXml($data, $companyModel);
            case 'credit':
                return $this->greenterService->generateCreditNoteXml($data);
            case 'debit':
                return $this->greenterService->generateDebitNoteXml($data);
            case 'despatch':
                throw new \Exception("Guías de remisión requieren implementación especial");
            default:
                throw new \Exception("Tipo de documento no soportado: $type");
        }
    }

    /**
     * Generar XML de factura básica
     */
    protected function generateInvoiceXml(array $data, string $ruc, string $company): string
    {
        $serie = $data['serie'] ?? 'F001';
        $numero = $data['numero'] ?? 1;
        $fecha = $data['fechaEmision'] ?? now()->format('Y-m-d');
        $moneda = $data['tipoMoneda'] ?? 'PEN';
        
        // XML básico de factura (en producción usarías Greenter para esto)
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Invoice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2\"
         xmlns:cac=\"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2\"
         xmlns:cbc=\"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2\">
    <cbc:ID>$serie-$numero</cbc:ID>
    <cbc:IssueDate>$fecha</cbc:IssueDate>
    <cbc:DocumentCurrencyCode>$moneda</cbc:DocumentCurrencyCode>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID=\"6\">$ruc</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>$company</cbc:Name>
            </cac:PartyName>
        </cac:Party>
    </cac:AccountingSupplierParty>
</Invoice>";
    }

    /**
     * Generar XML de nota de crédito básica
     */
    protected function generateCreditNoteXml(array $data, string $ruc, string $company): string
    {
        $serie = $data['serie'] ?? 'FC01';
        $numero = $data['numero'] ?? 1;
        $fecha = $data['fechaEmision'] ?? now()->format('Y-m-d');
        
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<CreditNote xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2\">
    <cbc:ID>$serie-$numero</cbc:ID>
    <cbc:IssueDate>$fecha</cbc:IssueDate>
    <!-- Estructura básica de nota de crédito -->
</CreditNote>";
    }

    /**
     * Generar XML de nota de débito básica
     */
    protected function generateDebitNoteXml(array $data, string $ruc, string $company): string
    {
        $serie = $data['serie'] ?? 'FD01';
        $numero = $data['numero'] ?? 1;
        $fecha = $data['fechaEmision'] ?? now()->format('Y-m-d');
        
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<DebitNote xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2\">
    <cbc:ID>$serie-$numero</cbc:ID>
    <cbc:IssueDate>$fecha</cbc:IssueDate>
    <!-- Estructura básica de nota de débito -->
</DebitNote>";
    }

    /**
     * Generar XML de guía de remisión básica
     */
    protected function generateDespatchXml(array $data, string $ruc, string $company): string
    {
        $serie = $data['serie'] ?? 'T001';
        $numero = $data['numero'] ?? 1;
        $fecha = $data['fechaEmision'] ?? now()->format('Y-m-d');
        
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<DespatchAdvice xmlns=\"urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2\">
    <cbc:ID>$serie-$numero</cbc:ID>
    <cbc:IssueDate>$fecha</cbc:IssueDate>
    <!-- Estructura básica de guía de remisión -->
</DespatchAdvice>";
    }

    /**
     * Generar nombre de archivo
     */
    protected function generateFileName(string $type, array $data): string
    {
        // Obtener la empresa desde los datos si está disponible
        $companyModel = null;
        if (isset($data['company']['ruc'])) {
            $companyModel = \App\Models\Company::where('ruc', $data['company']['ruc'])->first();
        }
        
        if ($type === 'invoice') {
            return $this->greenterService->getInvoiceFileName($data, $companyModel);
        }
        
        // Para otros tipos de documento, usar el RUC de la empresa actual
        if (!$companyModel) {
            $companyModel = \App\Models\Company::where('status', 'active')->first();
            
            if (!$companyModel) {
                throw new \Exception('No se encontró ninguna empresa activa para generar el nombre del archivo');
            }
        }
        
        $ruc = $companyModel->ruc;
        $serie = $data['serie'] ?? 'DOC001';
        $numero = $data['correlativo'] ?? $data['numero'] ?? 1; // Sin padding, como en Postman
        $typeCode = config("qpse.document_types.$type", '01');
        
        // QPse espera formato: RUC-TIPODOC-SERIE-CORRELATIVO (sin .xml)
        return "{$ruc}-{$typeCode}-{$serie}-{$numero}";
    }

    /**
     * Guardar archivo XML para debug
     */
    protected function saveXmlFile(string $fileName, string $xmlContent): void
    {
        $path = config('qpse.integration.xmls_path', 'qpse/xmls');
        Storage::put("{$path}/original/{$fileName}", $xmlContent);
    }

    /**
     * Formatear respuesta compatible con Greenter
     */
    protected function formatGreenterResponse(array $qpseResult): array
    {
        Log::info('Formateando respuesta de QPse', [
            'qpse_result_keys' => array_keys($qpseResult),
            'qpse_result' => $qpseResult
        ]);

        $envioResult = $qpseResult['envio'] ?? [];
        $estado = $envioResult['estado'] ?? null;
        
        Log::info('Analizando estado de respuesta QPse', [
            'estado' => $estado,
            'estado_type' => gettype($estado),
            'envio_mensaje' => $envioResult['mensaje'] ?? 'Sin mensaje'
        ]);
        
        // Determinar si fue exitoso - QPse devuelve 200 cuando SUNAT acepta
        $success = $estado === 'ACEPTADO' || $estado === 0 || $estado === '0' || $estado === 200 || $estado === '200';
        
        Log::info('Determinación de éxito', [
            'success' => $success,
            'estado_original' => $estado,
            'comparisons' => [
                'es_ACEPTADO' => $estado === 'ACEPTADO',
                'es_0_int' => $estado === 0,
                'es_0_str' => $estado === '0',
                'es_200_int' => $estado === 200,
                'es_200_str' => $estado === '200'
            ]
        ]);

        $response = [
            'success' => $success,
            'qpse_raw' => $qpseResult, // Datos completos de QPse para debug
        ];
        
        if ($success) {
            $response['cdr'] = $qpseResult['cdr'] ?? null;
            $response['xml_firmado'] = $qpseResult['xml_firmado'] ?? null;
            $response['message'] = $envioResult['mensaje'] ?? 'Documento procesado correctamente';
            
            Log::info('Respuesta exitosa formateada', [
                'has_cdr' => isset($qpseResult['cdr']),
                'has_xml_firmado' => isset($qpseResult['xml_firmado']),
                'message' => $response['message']
            ]);
        } else {
            $response['error'] = [
                'code' => $estado,
                'message' => $envioResult['mensaje'] ?? 'Error desconocido al procesar documento'
            ];
            
            Log::warning('Respuesta con error formateada', [
                'error_code' => $estado,
                'error_message' => $response['error']['message'],
                'envio_completo' => $envioResult
            ]);
        }
        
        Log::info('Respuesta final formateada', [
            'final_success' => $response['success'],
            'response_keys' => array_keys($response)
        ]);
        
        return $response;
    }

    /**
     * Verificar si QPse está configurado correctamente
     */
    public function isConfigured(): bool
    {
        return $this->qpseService->tieneCredenciales() || !empty(config('qpse.token'));
    }
}