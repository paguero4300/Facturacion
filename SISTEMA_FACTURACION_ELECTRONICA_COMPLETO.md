# 📄 Sistema de Facturación Electrónica - Análisis Completo

## 🎯 Introducción

Este documento detalla la arquitectura completa del sistema de facturación electrónica desarrollado en Laravel con Filament, que integra servicios de QPSE y Greenter para el cumplimiento de normativas SUNAT en Perú.

### 🔗 Punto de Acceso Principal
**Ruta de creación**: `/admin/invoices/create`

## 📋 Tipos de Documentos Soportados

Según la configuración del sistema, se soportan los siguientes tipos de documentos electrónicos:

| Código | Tipo de Documento | Descripción |
|--------|------------------|-------------|
| `01` | **Factura** | Comprobante de pago para empresas con RUC |
| `03` | **Boleta de Venta** | Comprobante de pago para personas naturales |
| `07` | **Nota de Crédito** | Documento que reduce el valor de una factura |
| `08` | **Nota de Débito** | Documento que incrementa el valor de una factura |
| `09` | **Nota de Venta** | Documento interno (no electrónico) |

## 🏗️ Arquitectura del Sistema

### Estructura de Directorios Clave
```
app/
├── Models/                    # Modelos de datos
├── Http/Controllers/          # Controladores HTTP
├── Filament/Resources/        # Recursos de Filament Admin
├── Services/                  # Servicios de lógica de negocio
├── Observers/                 # Observadores de eventos
├── Enums/                     # Enumeraciones
└── Mail/                      # Clases de email

config/
├── greenter.php              # Configuración Greenter
├── qpse.php                  # Configuración QPSE
└── invoice-pdf.php           # Configuración PDF

qpse_ejemplos/                # Ejemplos de documentos QPSE
```

## 🗄️ Modelos de Datos Principales

### 1. **Invoice** (Modelo Principal)
**Ubicación**: `app/Models/Invoice.php`

**Atributos Principales**:
- `company_id` - Empresa emisora
- `client_id` - Cliente receptor
- `document_series_id` - Serie del documento
- `document_type` - Tipo de documento (01, 03, 07, 08, 09)
- `series` - Serie del comprobante
- `number` - Número correlativo
- `full_number` - Número completo (serie-número)
- `issue_date` - Fecha de emisión
- `currency_code` - Moneda (PEN/USD)
- `exchange_rate` - Tipo de cambio
- `subtotal` - Subtotal sin impuestos
- `igv_amount` - Monto del IGV
- `total_amount` - Total del documento
- `payment_condition` - Condición de pago (immediate/credit)
- `sunat_status` - Estado en SUNAT
- `status` - Estado interno

**Relaciones**:
- `company()` - BelongsTo Company
- `client()` - BelongsTo Client
- `documentSeries()` - BelongsTo DocumentSeries
- `details()` - HasMany InvoiceDetail
- `paymentInstallments()` - HasMany PaymentInstallment

### 2. **InvoiceDetail** (Detalle de Productos)
**Ubicación**: `app/Models/InvoiceDetail.php`

**Atributos Principales**:
- `invoice_id` - ID de la factura
- `product_id` - ID del producto
- `line_number` - Número de línea
- `description` - Descripción del producto
- `quantity` - Cantidad
- `unit_price` - Precio unitario
- `net_amount` - Monto neto
- `igv_amount` - IGV de la línea
- `line_total` - Total de la línea
- `tax_type` - Tipo de afectación IGV

### 3. **Company** (Empresa Emisora)
**Ubicación**: `app/Models/Company.php`

**Atributos para Facturación**:
- `ruc` - RUC de la empresa
- `business_name` - Razón social
- `commercial_name` - Nombre comercial
- `address` - Dirección
- `ose_provider` - Proveedor OSE (qpse)
- `ose_username` - Usuario OSE
- `ose_password` - Contraseña OSE

### 4. **Client** (Cliente)
**Ubicación**: `app/Models/Client.php`

**Atributos Principales**:
- `document_type` - Tipo de documento (1=DNI, 6=RUC)
- `document_number` - Número de documento
- `business_name` - Razón social/nombre
- `address` - Dirección
- `email` - Email

### 5. **DocumentSeries** (Series de Documentos)
**Ubicación**: `app/Models/DocumentSeries.php`

**Funcionalidad**:
- Controla la numeración automática
- Asigna series por tipo de documento
- Método `getNextNumber()` para correlativo

## 🔗 Rutas y Controladores

### Rutas Principales
**Archivo**: `routes/web.php`

```php
// Rutas de Filament (automáticas)
/admin/invoices/create     // Página de creación
/admin/invoices/edit/{id}  // Página de edición
/admin/invoices/view/{id}  // Página de visualización

// Rutas de PDF
Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('{invoice}/pdf/download', [InvoicePdfController::class, 'download']);
    Route::get('{invoice}/pdf/view', [InvoicePdfController::class, 'view']);
    Route::get('{invoice}/ticket/download', [InvoicePdfController::class, 'ticket']);
});
```

### Controladores Principales

#### 1. **InvoicePdfController**
**Ubicación**: `app/Http/Controllers/InvoicePdfController.php`

**Métodos**:
- `download()` - Descarga PDF A4
- `view()` - Vista previa PDF
- `ticket()` - Impresión ticket 80mm
- `store()` - Guardar PDF en storage

#### 2. **CheckoutController**
**Ubicación**: `app/Http/Controllers/CheckoutController.php`

**Responsabilidad**:
- Manejo de pedidos web
- Creación de Notas de Venta (09)
- Validación de pagos

## 🎛️ Filament Resources

### InvoiceResource
**Ubicación**: `app/Filament/Resources/InvoiceResource.php`

#### Formulario de Creación
**Secciones del Formulario**:

1. **Datos Básicos**:
   - Selección de empresa
   - Selección de cliente (con validación por tipo de documento)
   - Tipo de documento
   - Serie automática
   - Fecha de emisión
   - Moneda y tipo de cambio
   - Condición de pago

2. **Detalle de Productos**:
   - Repeater con líneas de productos
   - Cálculo automático de totales
   - Soporte para IGV y exonerados

3. **Resumen**:
   - Totales calculados dinámicamente
   - Conversión a letras

#### Páginas del Resource

##### CreateInvoice
**Ubicación**: `app/Filament/Resources/InvoiceResource/Pages/CreateInvoice.php`

**Funcionalidades**:
- Asignación automática de empresa activa
- Snapshot de datos del cliente
- Asignación atómica de serie y correlativo
- Manejo de transacciones

**Método Clave**:
```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    // Asignación de empresa activa
    // Snapshot de datos del cliente
    // Asignación de serie y número
    // Control de concurrencia
}
```

## ⚙️ Servicios de Lógica de Negocio

### 1. **ElectronicInvoiceService**
**Ubicación**: `app/Services/ElectronicInvoiceService.php`

**Responsabilidades**:
- Coordinación del envío a SUNAT
- Transformación de datos del modelo a formato QPSE
- Manejo de respuestas y errores
- Actualización de estados

**Métodos Principales**:
- `sendFactura()` - Envío de facturas
- `sendBoleta()` - Envío de boletas
- `sendNotaCredito()` - Envío de notas de crédito
- `sendNotaDebito()` - Envío de notas de débito
- `resendDocument()` - Reenvío de documentos

### 2. **QpseService**
**Ubicación**: `app/Services/QpseService.php`

**Responsabilidades**:
- Comunicación directa con API de QPSE
- Manejo de autenticación
- Firmado de XML
- Envío a SUNAT vía QPSE

**Métodos Principales**:
- `obtenerToken()` - Autenticación con QPSE
- `firmarXml()` - Firmado digital
- `enviarXmlFirmado()` - Envío a SUNAT
- `procesarDocumento()` - Proceso completo

### 3. **QpseGreenterAdapter**
**Ubicación**: `app/Services/QpseGreenterAdapter.php`

**Responsabilidad**:
- Adaptador entre Greenter y QPSE
- Transformación de formatos
- Abstracción de la integración

### 4. **GreenterXmlService**
**Ubicación**: `app/Services/GreenterXmlService.php`

**Responsabilidad**:
- Generación de XML usando Greenter
- Construcción de objetos Greenter
- Conversión de datos del sistema

### 5. **FactilizaService**
**Ubicación**: `app/Services/FactilizaService.php`

**Responsabilidad**:
- Integración con API de Factiliza
- Consulta de datos de RUC/DNI
- Obtención de tipos de cambio

## 🔧 Archivos de Configuración

### 1. **config/greenter.php**
```php
return [
    'mode' => env('GREENTER_MODE', 'beta'),
    'company' => [
        'ruc' => env('GREENTER_COMPANY_RUC'),
        'razonSocial' => env('GREENTER_COMPANY_NAME'),
        // Configuración de empresa
    ],
    'endpoints' => [
        // URLs de SUNAT para beta y producción
    ],
];
```

### 2. **config/qpse.php**
```php
return [
    'mode' => env('QPSE_MODE', 'demo'),
    'url' => env('QPSE_URL', 'https://demo-cpe.qpse.pe'),
    'token' => env('QPSE_TOKEN'),
    'integration' => [
        'use_pse' => env('QPSE_USE_PSE', true),
        'timeout' => env('QPSE_TIMEOUT', 30),
    ],
    'document_types' => [
        'invoice' => '01',
        'credit' => '07',
        'debit' => '08',
    ],
];
```

### 3. **config/invoice-pdf.php**
```php
return [
    'format' => 'A4',
    'margins' => [
        'top' => 10, 'right' => 10,
        'bottom' => 10, 'left' => 10,
    ],
    'company' => [
        'logo_path' => 'images/logo.png',
        'show_logo' => true,
    ],
    'document' => [
        'show_qr_code' => true,
        'show_footer' => true,
    ],
];
```

## 👁️ Observers (Automatización)

### 1. **InvoiceObserver**
**Ubicación**: `app/Observers/InvoiceObserver.php`

**Eventos Manejados**:
- `saved()` - Recálculo de totales
- `updated()` - Actualización de totales

**Funcionalidades**:
```php
protected function calculateTotals(Invoice $invoice)
{
    // Cálculo de subtotal, IGV y total
    // Generación automática de cuotas para crédito
    // Prevención de recursión
}

protected function generateInstallments(Invoice $invoice)
{
    // Generación automática de cuotas
    // Distribución proporcional de montos
    // Cálculo de fechas de vencimiento
}
```

### 2. **InvoiceDetailObserver**
**Ubicación**: `app/Observers/InvoiceDetailObserver.php`

**Responsabilidad**:
- Recálculo cuando se modifican detalles
- Actualización de inventario
- Validaciones de stock

## 🔄 Flujo Completo de Creación

### 1. **Acceso Inicial**
```
Usuario accede a: /admin/invoices/create
↓
Se carga: InvoiceResource::form()
↓
Se presenta: Formulario con 3 secciones
```

### 2. **Selección de Datos Básicos**
```
Usuario selecciona:
- Empresa (automática si solo hay una activa)
- Tipo de documento (01, 03, 07, 08, 09)
- Serie (se filtra por empresa y tipo)
- Cliente (se valida según tipo de documento)
```

### 3. **Configuración de Documento**
```
Sistema configura automáticamente:
- Número correlativo (siguiente disponible)
- Fecha de emisión (hoy)
- Moneda y tipo de cambio
- Condición de pago
```

### 4. **Captura de Detalle**
```
Usuario agrega productos:
- Selección de producto
- Cantidad y precio
- Cálculo automático de IGV
- Totales dinámicos
```

### 5. **Procesamiento Backend**
```
Al enviar formulario:
↓
CreateInvoice::mutateFormDataBeforeCreate()
├── Asigna empresa activa
├── Captura datos del cliente (snapshot)
├── Asigna serie y correlativo (transacción)
└── Valida datos
↓
Se crea Invoice en base de datos
↓
InvoiceObserver::saved()
├── Calcula totales automáticamente
├── Genera cuotas si es crédito
└── Actualiza campos calculados
```

### 6. **Post-Creación**
```
Documento creado exitosamente:
├── Estado inicial: 'issued'
├── SUNAT status: 'pending'
├── Totales calculados
├── Cuotas generadas (si aplica)
└── Listo para envío a SUNAT
```

## 🚀 Integración SUNAT/QPSE

### Proceso de Envío Electrónico

#### 1. **Preparación de Datos**
```php
ElectronicInvoiceService::buildDocumentData()
├── Convierte Invoice model a formato QPSE
├── Estructura datos de empresa
├── Estructura datos de cliente
├── Procesa detalles de productos
└── Calcula totales y leyendas
```

#### 2. **Generación XML**
```php
GreenterXmlService::generateInvoiceXml()
├── Construye objetos Greenter
├── Aplica configuraciones
├── Genera XML estructurado
└── Retorna XML sin firmar
```

#### 3. **Firmado Digital**
```php
QpseService::firmarXml()
├── Autenticación con QPSE
├── Envío de XML para firmado
├── Recepción de XML firmado
└── Preparación para envío
```

#### 4. **Envío a SUNAT**
```php
QpseService::enviarXmlFirmado()
├── Envío vía QPSE a SUNAT
├── Recepción de CDR
├── Procesamiento de respuesta
└── Actualización de estado
```

#### 5. **Manejo de Respuesta**
```php
ElectronicInvoiceService::processResult()
├── Análisis de respuesta SUNAT
├── Actualización de Invoice
├── Guardado de CDR
└── Notificaciones de estado
```

## 📊 Estados de Documentos

### Estados Internos (status)
- `draft` - Borrador
- `issued` - Emitido
- `paid` - Pagado
- `partial_paid` - Pago parcial
- `cancelled` - Anulado

### Estados SUNAT (sunat_status)
- `pending` - Pendiente de envío
- `sent` - Enviado a SUNAT
- `accepted` - Aceptado por SUNAT
- `rejected` - Rechazado por SUNAT
- `observed` - Observado por SUNAT

## 🔍 Características Avanzadas

### 1. **Validación de Documentos**
- Validación de RUC/DNI vía Factiliza
- Verificación de tipos de documento por cliente
- Control de series y correlativos únicos

### 2. **Manejo de Monedas**
- Soporte para PEN y USD
- Tipo de cambio automático
- Cálculos multi-moneda

### 3. **Condiciones de Pago**
- Pago inmediato (contado)
- Pago a crédito con cuotas
- Generación automática de cronograma

### 4. **Generación de PDFs**
- PDF A4 para impresión formal
- Tickets 80mm para POS
- Configuración personalizable
- Múltiples formatos de descarga

### 5. **Sistema de Entrega**
- Programación de entregas
- Estados de entrega
- Notificaciones automáticas
- Validación de pagos

## 🚧 Conclusiones

Este sistema de facturación electrónica está construido con una arquitectura robusta que:

1. **Cumple normativas SUNAT** mediante integración QPSE
2. **Automatiza procesos** con Observers y eventos
3. **Facilita la operación** con interfaz Filament intuitiva
4. **Maneja múltiples escenarios** de negocio
5. **Escala eficientemente** con servicios modulares
6. **Mantiene integridad** con validaciones y transacciones

La arquitectura modular permite extensibilidad y mantenimiento, mientras que la integración con QPSE asegura el cumplimiento de las regulaciones peruanas de facturación electrónica.