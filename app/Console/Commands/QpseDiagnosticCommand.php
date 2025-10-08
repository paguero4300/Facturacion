<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QpseService;
use App\Services\ElectronicInvoiceService;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class QpseDiagnosticCommand extends Command
{
    protected $signature = 'qpse:diagnostico {--company= : ID de la empresa a probar}';
    protected $description = 'Diagnóstico completo de la integración con QPSE';

    public function handle()
    {
        $this->info('🔍 === DIAGNÓSTICO QPSE ===');
        $this->newLine();

        // 1. Verificar configuración básica
        $this->checkBasicConfiguration();
        
        // 2. Verificar empresa
        $company = $this->getTestCompany();
        if (!$company) {
            $this->error('❌ No se pudo obtener empresa para pruebas');
            return 1;
        }
        
        // 3. Probar autenticación
        $this->testAuthentication($company);
        
        // 4. Probar generación de XML
        $this->testXmlGeneration($company);
        
        // 5. Probar envío completo si hay facturas
        $this->testCompleteProcess($company);

        $this->newLine();
        $this->info('✅ Diagnóstico completado. Revisa el log: storage/logs/envioqpse.log');
        
        return 0;
    }

    protected function checkBasicConfiguration()
    {
        $this->info('📋 1. Verificando configuración básica...');
        
        $configs = [
            'QPSE URL' => config('qpse.url'),
            'QPSE Token' => config('qpse.token') ? 'SET' : 'NULL',
            'QPSE Username' => config('qpse.username') ? 'SET' : 'NULL',
            'QPSE Password' => config('qpse.password') ? 'SET' : 'NULL',
            'QPSE Mode' => config('qpse.mode'),
        ];
        
        foreach ($configs as $key => $value) {
            $status = $value ? '✅' : '❌';
            $this->line("  {$status} {$key}: {$value}");
        }
        
        Log::channel('envioqpse')->info('🔍 Configuración básica verificada', $configs);
        $this->newLine();
    }

    protected function getTestCompany(): ?Company
    {
        $this->info('🏢 2. Verificando empresa...');
        
        $companyId = $this->option('company');
        $company = $companyId 
            ? Company::find($companyId)
            : Company::where('ose_provider', 'qpse')->first() ?? Company::where('status', 'active')->first();
        
        if (!$company) {
            $this->error('❌ No se encontró empresa para pruebas');
            return null;
        }
        
        $this->info("✅ Empresa: {$company->business_name} (RUC: {$company->ruc})");
        $this->line("  📍 Provider: {$company->ose_provider}");
        $this->line("  📍 Endpoint: " . ($company->ose_endpoint ?: 'DEFAULT'));
        $this->line("  📍 Username: " . ($company->ose_username ? 'SET' : 'NULL'));
        $this->line("  📍 Password: " . ($company->ose_password ? 'SET' : 'NULL'));
        $this->line("  📍 Token: " . ($company->qpse_access_token ? 'SET' : 'NULL'));
        $this->line("  📍 Token expira: " . ($company->qpse_token_expires_at ?: 'NULL'));
        
        Log::channel('envioqpse')->info('🏢 Empresa para diagnóstico', [
            'company_id' => $company->id,
            'ruc' => $company->ruc,
            'ose_provider' => $company->ose_provider,
            'has_endpoint' => !empty($company->ose_endpoint),
            'has_username' => !empty($company->ose_username),
            'has_password' => !empty($company->ose_password),
            'has_token' => !empty($company->qpse_access_token),
            'token_expires' => $company->qpse_token_expires_at,
        ]);
        
        $this->newLine();
        return $company;
    }

    protected function testAuthentication(Company $company)
    {
        $this->info('🔐 3. Probando autenticación...');
        
        try {
            $qpseService = new QpseService();
            $qpseService->setCompany($company);
            
            $this->line('  🔄 Obteniendo token...');
            $token = $qpseService->obtenerToken();
            
            $this->info("  ✅ Token obtenido exitosamente");
            $this->line("  📍 Token length: " . strlen($token));
            $this->line("  📍 Token preview: " . substr($token, 0, 20) . '...');
            
        } catch (\Exception $e) {
            $this->error("  ❌ Error de autenticación: " . $e->getMessage());
            
            Log::channel('envioqpse')->error('❌ Error en prueba de autenticación', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        $this->newLine();
    }

    protected function testXmlGeneration(Company $company)
    {
        $this->info('📄 4. Probando generación de XML...');
        
        try {
            // Crear datos de prueba
            $testData = [
                'ublVersion' => '2.1',
                'tipoOperacion' => '0101',
                'tipoDoc' => '01',
                'serie' => 'F001',
                'correlativo' => '1',
                'fechaEmision' => now()->format('Y-m-d'),
                'tipoMoneda' => 'PEN',
                'company' => [
                    'ruc' => $company->ruc,
                    'razonSocial' => $company->business_name,
                    'nombreComercial' => $company->commercial_name ?: $company->business_name,
                    'address' => [
                        'direccion' => $company->address ?: 'Dirección de prueba',
                        'distrito' => $company->district ?: 'Lima',
                        'provincia' => $company->province ?: 'Lima',
                        'departamento' => $company->department ?: 'Lima',
                        'ubigeo' => $company->ubigeo ?: '150101'
                    ]
                ],
                'client' => [
                    'tipoDoc' => '6',
                    'numDoc' => '20000000001',
                    'rznSocial' => 'CLIENTE DE PRUEBA',
                    'direccion' => 'Dirección del cliente',
                    'email' => 'cliente@test.com',
                    'telephone' => '123456789'
                ],
                'details' => [
                    [
                        'codProducto' => 'PROD001',
                        'unidad' => 'NIU',
                        'descripcion' => 'Producto de prueba',
                        'cantidad' => 1.0,
                        'mtoValorUnitario' => 100.0,
                        'mtoValorVenta' => 100.0,
                        'mtoBaseIgv' => 100.0,
                        'porcentajeIgv' => 18.0,
                        'igv' => 18.0,
                        'tipAfeIgv' => '10',
                        'totalImpuestos' => 18.0,
                        'mtoPrecioUnitario' => 118.0
                    ]
                ],
                'mtoOperGravadas' => 100.0,
                'mtoIGV' => 18.0,
                'totalImpuestos' => 18.0,
                'valorVenta' => 100.0,
                'subTotal' => 118.0,
                'mtoImpVenta' => 118.0,
                'legends' => [
                    [
                        'code' => '1000',
                        'value' => 'CIENTO DIECIOCHO CON 00/100 SOLES'
                    ]
                ],
                'formaPago' => [
                    'tipo' => 'Contado'
                ]
            ];
            
            $this->line('  🔄 Generando XML de prueba...');
            
            $qpseService = new QpseService();
            $qpseService->setCompany($company);
            
            // Solo probar firmado de XML con datos básicos
            $xmlBasico = '<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    <cbc:ID>F001-1</cbc:ID>
    <cbc:IssueDate>' . now()->format('Y-m-d') . '</cbc:IssueDate>
    <cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="6">' . $company->ruc . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>' . htmlspecialchars($company->business_name) . '</cbc:Name>
            </cac:PartyName>
        </cac:Party>
    </cac:AccountingSupplierParty>
</Invoice>';
            
            $fileName = "{$company->ruc}-01-F001-1";
            
            $this->line("  📍 Archivo: {$fileName}");
            $this->line("  📍 XML length: " . strlen($xmlBasico));
            
            // Probar firma del XML
            $this->line('  🔄 Probando firma de XML...');
            $resultado = $qpseService->firmarXml($fileName, $xmlBasico);
            
            $this->info("  ✅ XML firmado exitosamente");
            $this->line("  📍 Has XML: " . (isset($resultado['xml']) ? 'SI' : 'NO'));
            $this->line("  📍 Has Hash: " . (isset($resultado['codigo_hash']) ? 'SI' : 'NO'));
            
        } catch (\Exception $e) {
            $this->error("  ❌ Error generando/firmando XML: " . $e->getMessage());
            
            Log::channel('envioqpse')->error('❌ Error en prueba de XML', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        $this->newLine();
    }

    protected function testCompleteProcess(Company $company)
    {
        $this->info('🚀 5. Probando proceso completo (si hay facturas)...');
        
        // Buscar una factura pendiente de la empresa
        $invoice = Invoice::where('company_id', $company->id)
            ->whereIn('sunat_status', ['pending', 'rejected'])
            ->first();
        
        if (!$invoice) {
            $this->line('  ℹ️ No hay facturas pendientes para probar envío completo');
            $this->newLine();
            return;
        }
        
        $this->line("  📄 Probando con factura: {$invoice->full_number}");
        $this->line("  📍 Cliente: {$invoice->client_business_name}");
        $this->line("  📍 Total: {$invoice->currency_code} {$invoice->total_amount}");
        
        try {
            $this->line('  🔄 Enviando factura a QPSE...');
            
            $service = new ElectronicInvoiceService();
            $result = $service->sendFactura($invoice);
            
            if ($result['success']) {
                $this->info("  ✅ Factura enviada exitosamente");
                $this->line("  📍 Mensaje: " . ($result['message'] ?? 'Sin mensaje'));
            } else {
                $this->error("  ❌ Error enviando factura: " . ($result['error']['message'] ?? 'Error desconocido'));
                $this->line("  📍 Código: " . ($result['error']['code'] ?? 'Sin código'));
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ Excepción enviando factura: " . $e->getMessage());
            
            Log::channel('envioqpse')->error('❌ Error en prueba completa', [
                'invoice_id' => $invoice->id,
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        $this->newLine();
    }
}