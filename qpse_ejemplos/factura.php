<?php

declare(strict_types=1);

use Greenter\Model\Response\BillResult;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;

require __DIR__ . '/../vendor/autoload.php';

$util = Util::getInstance();

$invoice = new Invoice();
$invoice
    ->setUblVersion('2.1')
    ->setFecVencimiento(new DateTime())
    ->setTipoOperacion('0101')
    ->setTipoDoc('01')
    ->setSerie('F001')
    ->setCorrelativo('123')
    ->setFechaEmision(new DateTime())
    ->setFormaPago(new FormaPagoContado())
    ->setTipoMoneda('PEN')
    ->setCompany($util->shared->getCompany())
    ->setClient($util->shared->getClient())
    ->setMtoOperGravadas(200)
    ->setMtoOperExoneradas(100)
    ->setMtoIGV(36)
    ->setTotalImpuestos(36)
    ->setValorVenta(300)
    ->setSubTotal(336)
    ->setMtoImpVenta(336)
    ;

$items = [];

// Detalle gravado
$items[] = (new SaleDetail())
    ->setCodProducto('P001')
    ->setUnidad('NIU')
    ->setDescripcion('PROD 1')
    ->setCantidad(2)
    ->setMtoValorUnitario(100)
    ->setMtoValorVenta(200)
    ->setMtoBaseIgv(200)
    ->setPorcentajeIgv(18)
    ->setIgv(36)
    ->setTipAfeIgv('10') // Catalog: 07
    ->setTotalImpuestos(36)
    ->setMtoPrecioUnitario(118)
;

// Detalle Exonerado
$items[] = (new SaleDetail())
    ->setCodProducto('P002')
    ->setUnidad('KG')
    ->setDescripcion('PROD 2')
    ->setCantidad(2)
    ->setMtoValorUnitario(50)
    ->setMtoValorVenta(100)
    ->setMtoBaseIgv(100)
    ->setPorcentajeIgv(0)
    ->setIgv(0)
    ->setTipAfeIgv('20') // Catalog: 07
    ->setTotalImpuestos(0)
    ->setMtoPrecioUnitario(50)
;

$invoice->setDetails($items)
    ->setLegends([
        (new Legend())
            ->setCode('1000')
            ->setValue('SON TRESCIENTOS TREINTA Y SEIS CON OO/100 SOLES')
    ]);

// Envio a SUNAT.
$see = $util->getSee(SunatEndpoints::FE_BETA);

/** Si solo desea enviar un XML ya generado utilice esta función**/
//$res = $see->sendXml(get_class($invoice), $invoice->getName(), file_get_contents($ruta_XML));

$res = $see->send($invoice);
$util->writeXml($invoice, $see->getFactory()->getLastXml());

if (!$res->isSuccess()) {
    echo $util->getErrorResponse($res->getError());

    exit();
}

/**@var $res BillResult*/
$cdr = $res->getCdrResponse();
$util->writeCdr($invoice, $res->getCdrZip());

$util->showResponse($invoice, $cdr);
