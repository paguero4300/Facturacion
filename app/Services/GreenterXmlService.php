<?php

namespace App\Services;

use Greenter\Factory\FeFactory;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Xml\Builder\InvoiceBuilder;
use DateTime;

class GreenterXmlService
{
    protected FeFactory $factory;

    public function __construct()
    {
        $this->factory = new FeFactory();
        $this->configureFactory();
    }

    protected function configureFactory(): void
    {
        // Para la generación de XML no necesitamos configurar endpoints
        // Solo necesitamos el generador
    }

    protected function getCompany(\App\Models\Company $companyModel = null): Company
    {
        // Si no se proporciona una empresa específica, usar la primera activa
        if (!$companyModel) {
            $companyModel = \App\Models\Company::where('status', 'active')->first();
            
            if (!$companyModel) {
                throw new \Exception('No se encontró ninguna empresa activa en la base de datos');
            }
        }
        
        $address = new Address();
        $address->setUbigueo($companyModel->ubigeo ?: '150101')
               ->setDepartamento($companyModel->department ?: 'LIMA')
               ->setProvincia($companyModel->province ?: 'LIMA')
               ->setDistrito($companyModel->district ?: 'LIMA')
               ->setDireccion($companyModel->address ?: 'Sin dirección');

        $company = new Company();
        $company->setRuc($companyModel->ruc)
               ->setRazonSocial($companyModel->business_name)
               ->setNombreComercial($companyModel->commercial_name ?: $companyModel->business_name)
               ->setAddress($address);

        return $company;
    }

    public function generateInvoiceXml(array $data, \App\Models\Company $companyModel = null): string
    {
        $invoice = $this->buildInvoice($data);
        
        // Configurar la empresa en el invoice
        $invoice->setCompany($this->getCompany($companyModel));
        
        // Generar XML usando Greenter
        $builder = new InvoiceBuilder();
        
        return $builder->build($invoice);
    }

    protected function buildInvoice(array $data): Invoice
    {
        // Crear cliente
        $client = new Client();
        $client->setTipoDoc($data['client']['tipoDoc'])
               ->setNumDoc($data['client']['numDoc'])
               ->setRznSocial($data['client']['rznSocial']);

        // Crear detalles de la factura
        $details = [];
        foreach ($data['details'] as $detail) {
            $item = new SaleDetail();
            $item->setCodProducto($detail['codProducto'])
                 ->setUnidad($detail['unidad'])
                 ->setCantidad($detail['cantidad'])
                 ->setMtoValorUnitario($detail['mtoValorUnitario'])
                 ->setDescripcion($detail['descripcion'])
                 ->setMtoBaseIgv($detail['mtoBaseIgv'])
                 ->setPorcentajeIgv($detail['porcentajeIgv'])
                 ->setIgv($detail['igv'])
                 ->setTipAfeIgv($detail['tipAfeIgv'])
                 ->setTotalImpuestos($detail['totalImpuestos'])
                 ->setMtoValorVenta($detail['mtoValorVenta'])
                 ->setMtoPrecioUnitario($detail['mtoPrecioUnitario']);
            
            $details[] = $item;
        }

        // Crear leyendas
        $legends = [];
        if (isset($data['legends'])) {
            foreach ($data['legends'] as $legendData) {
                $legend = new Legend();
                $legend->setCode($legendData['code'])
                       ->setValue($legendData['value']);
                $legends[] = $legend;
            }
        }

        // Crear forma de pago
        $formaPago = null;
        if (isset($data['formaPago']) && $data['formaPago']['tipo'] === 'Contado') {
            $formaPago = new FormaPagoContado();
        }

        // Crear factura
        $invoice = new Invoice();
        $invoice->setUblVersion($data['ublVersion'] ?? '2.1')
                ->setTipoOperacion($data['tipoOperacion'] ?? '0101')
                ->setTipoDoc($data['tipoDoc'])
                ->setSerie($data['serie'])
                ->setCorrelativo($data['correlativo'])
                ->setFechaEmision(new DateTime(date('Y-m-d', strtotime($data['fechaEmision']))))
                ->setTipoMoneda($data['tipoMoneda'])
                ->setClient($client)
                ->setMtoOperGravadas($data['mtoOperGravadas'])
                ->setMtoIGV($data['mtoIGV'])
                ->setTotalImpuestos($data['totalImpuestos'])
                ->setValorVenta($data['valorVenta'])
                ->setSubTotal($data['subTotal'])
                ->setMtoImpVenta($data['mtoImpVenta'])
                ->setDetails($details)
                ->setLegends($legends);

        if ($formaPago) {
            $invoice->setFormaPago($formaPago);
        }

        return $invoice;
    }

    public function generateCreditNoteXml(array $data): string
    {
        // Implementar generación de nota de crédito
        throw new \Exception('Generación de notas de crédito no implementada aún');
    }

    public function generateDebitNoteXml(array $data): string
    {
        // Implementar generación de nota de débito
        throw new \Exception('Generación de notas de débito no implementada aún');
    }

    public function getInvoiceFileName(array $data, \App\Models\Company $companyModel = null): string
    {
        // Usar el RUC de la empresa actual, no la configuración hardcodeada
        if (!$companyModel) {
            $companyModel = \App\Models\Company::where('status', 'active')->first();
            
            if (!$companyModel) {
                throw new \Exception('No se encontró ninguna empresa activa para generar el nombre del archivo');
            }
        }
        
        $ruc = $companyModel->ruc;
        $serie = $data['serie'];
        $correlativo = $data['correlativo']; // Sin padding, como en Postman: "10417844398-01-F001-17"
        
        // QPse espera el formato: RUC-TIPODOC-SERIE-CORRELATIVO (sin .xml)
        return "{$ruc}-01-{$serie}-{$correlativo}";
    }

    public function getExampleInvoiceData(): array
    {
        return [
            "ublVersion" => "2.1",
            "tipoOperacion" => "0101", // Catálogo 51
            "tipoDoc" => "01", // Catálogo 01 - Factura
            "serie" => "F001",
            "correlativo" => "1",
            "fechaEmision" => now()->format('Y-m-d H:i:s'),
            "formaPago" => [
                'tipo' => 'Contado',
            ],
            "tipoMoneda" => "PEN", // Catálogo 02
            "client" => [
                "tipoDoc" => "6", // Catálogo 06 - RUC
                "numDoc" => "20000000001",
                "rznSocial" => "EMPRESA CLIENTE SAC",
            ],
            "mtoOperGravadas" => 100.00,
            "mtoIGV" => 18.00,
            "totalImpuestos" => 18.00,
            "valorVenta" => 100.00,
            "subTotal" => 118.00,
            "mtoImpVenta" => 118.00,
            "details" => [
                [
                    "codProducto" => "P001",
                    "unidad" => "NIU", // Catálogo 03
                    "cantidad" => 2,
                    "mtoValorUnitario" => 50.00,
                    "descripcion" => "PRODUCTO DE EJEMPLO",
                    "mtoBaseIgv" => 100.00,
                    "porcentajeIgv" => 18.00,
                    "igv" => 18.00,
                    "tipAfeIgv" => "10", // Gravado - Operación Onerosa
                    "totalImpuestos" => 18.00,
                    "mtoValorVenta" => 100.00,
                    "mtoPrecioUnitario" => 59.00,
                ],
            ],
            "legends" => [
                [
                    "code" => "1000", // Catálogo 15
                    "value" => "SON CIENTO DIECIOCHO CON 00/100 SOLES",
                ],
            ],
        ];
    }
}