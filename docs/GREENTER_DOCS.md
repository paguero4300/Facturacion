# Documentación Laravel Greenter

Esta documentación proporciona una guía básica para usar Laravel Greenter en tu proyecto de facturación electrónica SUNAT.

## ¿Qué es Laravel Greenter?

Laravel Greenter es un paquete de Laravel que proporciona integración completa con el sistema de facturación electrónica de SUNAT (Perú), permitiendo:

- ✅ Generación y firma digital de documentos electrónicos
- ✅ Transmisión de documentos a servicios SUNAT
- ✅ Generación de representaciones HTML y PDF
- ✅ Manejo de respuestas de SUNAT

## Tipos de Documentos Soportados

- **Facturas** - Documentos de venta
- **Notas de Crédito/Débito** - Modificaciones a facturas
- **Guías de Remisión** - Documentos de traslado
- **Documentos de Retención** - Retenciones aplicadas
- **Documentos de Percepción** - Percepciones aplicadas

## Instalación

El paquete ya está instalado en tu proyecto. Si necesitas instalarlo en otro proyecto:

```bash
composer require codersfree/laravel-greenter
```

## Configuración

### 1. Variables de Entorno

Añade estas variables a tu archivo `.env`:

```bash
# Modo: 'beta' para pruebas, 'production' para producción
GREENTER_MODE=beta

# Datos de la empresa
GREENTER_COMPANY_RUC=20000000001
GREENTER_COMPANY_NAME="MI EMPRESA SAC"
GREENTER_COMPANY_ADDRESS="Av. Ejemplo 123"

# Credenciales ClaveSOL (para servicios SEE)
GREENTER_SOL_USER=MODDATOS
GREENTER_SOL_PASS=MODDATOS

# Credenciales API REST (para guías de remisión)
GREENTER_CLIENT_ID=test-85e5b0ae-255c-4891-a595-0b98c65c9854
GREENTER_CLIENT_SECRET=test-Hh/c6QwQakN0F7YOfvsnw==

# Certificado digital (opcional)
GREENTER_CERTIFICATE_PATH=
GREENTER_CERTIFICATE_PASS=
```

### 2. Configuración del Certificado (Producción)

Para producción necesitarás un certificado digital:

```bash
# Ruta al certificado .p12
GREENTER_CERTIFICATE_PATH=/path/to/certificate.p12
GREENTER_CERTIFICATE_PASS=tu_contraseña_certificado
```

## Uso Básico

### 1. Enviar una Factura

```php
<?php

use Greenter\Facades\Greenter;

// Estructura básica de una factura
$invoice = [
    'serie' => 'F001',
    'numero' => 1,
    'fechaEmision' => now(),
    'tipoMoneda' => 'PEN',
    'cliente' => [
        'tipoDoc' => '6',
        'numDoc' => '20000000001',
        'rznSocial' => 'EMPRESA CLIENTE SAC'
    ],
    'detalles' => [
        [
            'codProducto' => 'P001',
            'unidad' => 'NIU',
            'descripcion' => 'Producto de ejemplo',
            'cantidad' => 1,
            'mtoValorUnitario' => 100.00,
            'mtoValorVenta' => 100.00,
            'mtoBaseIgv' => 100.00,
            'porcentajeIgv' => 18.00,
            'igv' => 18.00,
            'tipAfeIgv' => '10',
            'totalImpuestos' => 18.00,
            'mtoPrecioUnitario' => 118.00
        ]
    ],
    'mtoOperGravadas' => 100.00,
    'mtoIGV' => 18.00,
    'totalImpuestos' => 18.00,
    'mtoImpVenta' => 118.00
];

// Enviar factura a SUNAT
$response = Greenter::send('invoice', $invoice);

if ($response->isSuccess()) {
    echo "Factura enviada correctamente";
    echo "CDR: " . $response->getCdrResponse();
} else {
    echo "Error: " . $response->getError()->getMessage();
}
```

### 2. Generar PDF de Factura

```php
use Greenter\Facades\GreenterReport;

// Generar PDF de la factura
$pdfBinary = GreenterReport::generatePdf($invoice);

// Guardar PDF
file_put_contents('factura_F001-1.pdf', $pdfBinary);

// O devolver como respuesta HTTP
return response($pdfBinary, 200, [
    'Content-Type' => 'application/pdf',
    'Content-Disposition' => 'inline; filename="factura.pdf"'
]);
```

### 3. Enviar Nota de Crédito

```php
$creditNote = [
    'serie' => 'FC01',
    'numero' => 1,
    'fechaEmision' => now(),
    'tipDocAfectado' => '01', // Factura
    'numDocfectado' => 'F001-1',
    'codMotivo' => '07', // Devolución por defecto
    'desMotivo' => 'Devolución de productos defectuosos',
    'tipoMoneda' => 'PEN',
    'cliente' => [
        'tipoDoc' => '6',
        'numDoc' => '20000000001',
        'rznSocial' => 'EMPRESA CLIENTE SAC'
    ],
    // ... más detalles similar a factura
];

$response = Greenter::send('credit', $creditNote);
```

### 4. Enviar Guía de Remisión

```php
use Greenter\Facades\GreenterDespatch;

$despatch = [
    'serie' => 'T001',
    'numero' => 1,
    'fechaEmision' => now(),
    'tipoGuia' => '09', // Guía de remisión remitente
    'motivoTraslado' => [
        'cod' => '01',
        'desc' => 'Venta'
    ],
    'shipment' => [
        'codTraslado' => '01', // Transporte público
        'modTraslado' => '01', // Transporte público
        'fecTraslado' => now()->addDay(),
        'ubigeoPartida' => '150101',
        'direccionPartida' => 'Av. Origen 123',
        'ubigeoLlegada' => '150102',
        'direccionLlegada' => 'Av. Destino 456'
    ]
    // ... más detalles
];

$response = GreenterDespatch::send($despatch);
```

## Métodos Principales

### Facades Disponibles

1. **Greenter** - Envío de documentos principales
   - `Greenter::send('invoice', $data)` - Enviar factura
   - `Greenter::send('credit', $data)` - Enviar nota de crédito
   - `Greenter::send('debit', $data)` - Enviar nota de débito

2. **GreenterReport** - Generación de reportes
   - `GreenterReport::generatePdf($document)` - Generar PDF
   - `GreenterReport::generateHtml($document)` - Generar HTML

3. **GreenterDespatch** - Guías de remisión
   - `GreenterDespatch::send($data)` - Enviar guía

## Manejo de Respuestas

```php
$response = Greenter::send('invoice', $invoiceData);

if ($response->isSuccess()) {
    // Éxito
    $cdr = $response->getCdrResponse();
    $ticket = $response->getTicket(); // Para documentos asincrónicos
    
    echo "Documento aceptado por SUNAT";
} else {
    // Error
    $error = $response->getError();
    
    echo "Código: " . $error->getCode();
    echo "Mensaje: " . $error->getMessage();
}
```

## Códigos de Error Comunes

- **2324**: RUC del emisor no existe o no está activo
- **2335**: Número de documento ya existe
- **3033**: El documento no cumple con el formato establecido
- **4001**: Serie inválida

## Entornos

### Beta (Pruebas)
- URL SEE: `https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService`
- Usuario de prueba: `MODDATOS`
- Contraseña de prueba: `MODDATOS`

### Producción
- URL SEE: `https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService`
- Requiere credenciales reales y certificado digital

## Consejos de Implementación

1. **Usa colas** para envío de documentos en producción
2. **Guarda los CDR** (Constancia de Recepción) como respaldo
3. **Maneja errores** apropiadamente y almacena logs
4. **Valida datos** antes de enviar a SUNAT
5. **Implementa reintentos** para documentos fallidos

## Estructura de Controlador de Ejemplo

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Greenter\Facades\Greenter;
use Greenter\Facades\GreenterReport;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        // Validar datos
        $validated = $request->validate([
            'cliente_ruc' => 'required|size:11',
            'cliente_nombre' => 'required|string',
            'productos' => 'required|array'
        ]);

        // Generar estructura de factura
        $invoice = $this->buildInvoiceData($validated);

        // Enviar a SUNAT
        $response = Greenter::send('invoice', $invoice);

        if ($response->isSuccess()) {
            // Generar PDF
            $pdf = GreenterReport::generatePdf($invoice);
            
            // Guardar en storage
            $filename = "factura_{$invoice['serie']}-{$invoice['numero']}.pdf";
            Storage::put("invoices/{$filename}", $pdf);
            
            return response()->json([
                'success' => true,
                'message' => 'Factura enviada correctamente',
                'pdf_url' => Storage::url("invoices/{$filename}")
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => $response->getError()->getMessage()
            ], 400);
        }
    }
    
    private function buildInvoiceData($data)
    {
        // Construir estructura de factura
        // ... lógica de construcción
    }
}
```

¡Con esta documentación tienes una base sólida para implementar facturación electrónica en tu aplicación Laravel!