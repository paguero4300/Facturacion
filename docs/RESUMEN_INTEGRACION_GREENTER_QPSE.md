# 📋 Resumen: Integración Greenter + QPSE

## 🎯 Introducción

Este documento resume el funcionamiento de la integración entre **Laravel Greenter** y **QPSE** (Proveedor de Servicios Electrónicos) para la facturación electrónica SUNAT en el sistema de facturación.

## 🔧 ¿Qué es cada tecnología?

### **Laravel Greenter**
- **Propósito**: Paquete de Laravel para integración completa con SUNAT
- **Funcionalidades**:
  - ✅ Generación y firma digital de documentos electrónicos
  - ✅ Transmisión de documentos a servicios SUNAT
  - ✅ Generación de representaciones HTML y PDF
  - ✅ Manejo de respuestas de SUNAT

### **QPSE (Proveedor de Servicios Electrónicos)**
- **Propósito**: Servicio intermediario para conectar con SUNAT
- **Funcionalidades**:
  - ✅ Firmado digital de XMLs
  - ✅ Envío a SUNAT vía API
  - ✅ Manejo de autenticación y tokens
  - ✅ Procesamiento de CDR (Constancia de Recepción)

## 📊 Tipos de Documentos Soportados

| Código | Tipo | Greenter | QPSE |
|--------|------|----------|------|
| **01** | Factura | ✅ | ✅ |
| **03** | Boleta de Venta | ✅ | ✅ |
| **07** | Nota de Crédito | ✅ | ✅ |
| **08** | Nota de Débito | ✅ | ✅ |
| **09** | Guía de Remisión | ✅ | ✅ |
| **20** | Comprobante de Retención | ✅ | ✅ |
| **40** | Comprobante de Percepción | ✅ | ✅ |

## 🏗️ Arquitectura de la Integración

### **Flujo de Trabajo**
```
📝 Datos Factura
    ↓
🔧 Laravel Greenter (Genera XML)
    ↓
📡 QPSE Service (Firma Digital)
    ↓
🏛️ SUNAT (Validación)
    ↓
📄 CDR + Estado
```

### **Componentes Clave**

#### 1. **Servicios Principales**
- **`QpseService.php`** - Comunicación directa con API QPSE
- **`QpseGreenterAdapter.php`** - Adaptador entre Greenter y QPSE
- **`GreenterXmlService.php`** - Generación XML usando Greenter

#### 2. **Facades Disponibles**
- **`QpseGreenter`** - Integración completa (Recomendado)
- **`Qpse`** - Acceso directo a QPSE
- **`Greenter`** - Greenter nativo

#### 3. **Configuraciones**
- **`config/greenter.php`** - Configuración Greenter
- **`config/qpse.php`** - Configuración QPSE

## ⚙️ Configuración del Sistema

### **Variables de Entorno Requeridas**

#### Greenter (Configuración Base)
```bash
# Modo operación
GREENTER_MODE=beta                    # beta|production

# Datos empresa
GREENTER_COMPANY_RUC=20000000001
GREENTER_COMPANY_NAME="MI EMPRESA SAC"
GREENTER_COMPANY_ADDRESS="Av. Ejemplo 123"

# Credenciales ClaveSOL (SEE)
GREENTER_SOL_USER=MODDATOS
GREENTER_SOL_PASS=MODDATOS

# Credenciales API REST (Guías)
GREENTER_CLIENT_ID=test-85e5b0ae-255c-4891-a595-0b98c65c9854
GREENTER_CLIENT_SECRET=test-Hh/c6QwQakN0F7YOfvsnw==

# Certificado digital (Producción)
GREENTER_CERTIFICATE_PATH=/path/to/certificate.p12
GREENTER_CERTIFICATE_PASS=contraseña_certificado
```

#### QPSE (Integración OSE)
```bash
# Configuración QPSE
QPSE_MODE=demo                        # demo|production
QPSE_URL=https://demo-cpe.qpse.pe
QPSE_TOKEN=tu_token_configuracion

# Credenciales generadas automáticamente
QPSE_USERNAME=                        # Se genera automáticamente
QPSE_PASSWORD=                        # Se genera automáticamente

# Configuración adicional
QPSE_TIMEOUT=30
QPSE_LOGS_ENABLED=true
QPSE_SAVE_XMLS=false
```

### **Entornos Disponibles**

#### Demo/Desarrollo
- **Greenter**: `https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService`
- **QPSE**: `https://demo-cpe.qpse.pe`
- **Credenciales de prueba**: MODDATOS/MODDATOS

#### Producción
- **Greenter**: `https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService`
- **QPSE**: `https://cpe.qpse.pe`
- **Requiere**: Certificado digital real y credenciales válidas

## 🚀 Configuración Inicial

### **Paso 1: Configuración Automática**
```bash
# Crear empresa en QPSE y obtener credenciales
php artisan qpse:setup 20123456789 --plan=01
```

**Este comando**:
- ✅ Crea la empresa en QPSE
- ✅ Obtiene credenciales automáticamente
- ✅ Actualiza archivo `.env`
- ✅ Prueba la conexión

### **Paso 2: Verificación**
```php
use App\Facades\QpseGreenter;

if (QpseGreenter::isConfigured()) {
    echo "✅ Sistema configurado correctamente";
} else {
    echo "❌ Configuración incompleta";
}
```

## 💻 Uso Práctico

### **Opción 1: Facade QpseGreenter (Recomendado)**
```php
use App\Facades\QpseGreenter;

// Enviar factura completa
$response = QpseGreenter::sendInvoice([
    'serie' => 'F001',
    'numero' => 1,
    'fechaEmision' => now(),
    'tipoMoneda' => 'PEN',
    'cliente' => [
        'tipoDoc' => '6',
        'numDoc' => '20000000001',
        'rznSocial' => 'CLIENTE SAC'
    ],
    'detalles' => [
        [
            'codProducto' => 'P001',
            'descripcion' => 'Producto ejemplo',
            'cantidad' => 1,
            'mtoValorUnitario' => 100.00,
            'igv' => 18.00
        ]
    ],
    'mtoIGV' => 18.00,
    'mtoImpVenta' => 118.00
]);

if ($response['success']) {
    echo "✅ Factura enviada y aceptada";
    $cdr = $response['cdr'];
} else {
    echo "❌ Error: " . $response['error']['message'];
}
```

### **Opción 2: Greenter Nativo**
```php
use Greenter\Facades\Greenter;
use Greenter\Facades\GreenterReport;

// Enviar documento
$response = Greenter::send('invoice', $invoiceData);

if ($response->isSuccess()) {
    // Generar PDF
    $pdf = GreenterReport::generatePdf($invoiceData);
    echo "✅ Documento enviado y PDF generado";
} else {
    echo "❌ Error: " . $response->getError()->getMessage();
}
```

### **Opción 3: QPSE Directo**
```php
use App\Facades\Qpse;

// Procesar XML ya generado
$response = Qpse::procesarDocumento('factura.xml', $xmlContent);

if ($response['envio']['estado'] === 'ACEPTADO') {
    echo "✅ Documento aceptado por SUNAT";
    $cdr = $response['cdr'];
} else {
    echo "❌ Rechazado: " . $response['envio']['mensaje'];
}
```

## 🔄 Flujo Completo en el Sistema

### **Integración con ElectronicInvoiceService**
```php
// app/Services/ElectronicInvoiceService.php

public function sendFactura(Invoice $invoice): array
{
    // 1. Preparar datos
    $documentData = $this->buildDocumentData($invoice);
    
    // 2. Generar XML con Greenter
    $xml = $this->greenterService->generateInvoiceXml($documentData);
    
    // 3. Firmar y enviar con QPSE
    $result = $this->qpseService->procesarDocumento($fileName, $xml);
    
    // 4. Procesar respuesta
    return $this->processResult($invoice, $result);
}
```

### **Estados de Procesamiento**
```php
// Estados internos del sistema
'pending'    → Pendiente de envío
'sent'       → Enviado a SUNAT
'accepted'   → Aceptado por SUNAT
'rejected'   → Rechazado por SUNAT
'observed'   → Observado por SUNAT
```

## 🔍 Ventajas de Cada Enfoque

### **Greenter Directo**
✅ **Ventajas**:
- Control total del proceso
- Generación de PDFs integrada
- Amplia documentación
- Soporte completo de tipos de documentos

❌ **Desventajas**:
- Requiere certificado digital (producción)
- Configuración más compleja
- Manejo directo de errores SUNAT

### **QPSE como OSE**
✅ **Ventajas**:
- No requiere certificado digital propio
- Configuración simplificada
- Manejo automático de firmas
- Soporte técnico especializado

❌ **Desventajas**:
- Dependencia de servicio externo
- Costos adicionales
- Menor control del proceso

### **Integración Híbrida (Actual)**
✅ **Ventajas Combinadas**:
- Greenter para generación XML
- QPSE para firmado y envío
- Flexibilidad de configuración
- Respaldo en ambos sistemas

## 🛠️ Comandos Útiles

### **Configuración QPSE**
```bash
# Configuración inicial
php artisan qpse:setup [RUC] [--plan=01]

# Verificar estado
php artisan qpse:status

# Probar conexión
php artisan qpse:test-connection
```

### **Debugging**
```bash
# Habilitar logs detallados
QPSE_LOGS_ENABLED=true

# Guardar XMLs para revisión
QPSE_SAVE_XMLS=true

# Ver logs del sistema
tail -f storage/logs/laravel.log
```

## ⚠️ Consideraciones Importantes

### **Seguridad**
- 🔐 Tokens y credenciales en variables de entorno
- 🔐 Certificados digitales seguros (producción)
- 🔐 Logs sin información sensible

### **Performance**
- ⚡ Uso de colas para envío masivo
- ⚡ Cache de tokens de acceso
- ⚡ Reintentos automáticos en errores temporales

### **Monitoreo**
- 📊 Estados de documentos en tiempo real
- 📊 Logs de errores y éxitos
- 📊 Métricas de envío a SUNAT

## 🚨 Códigos de Error Comunes

| Código | Descripción | Solución |
|--------|-------------|----------|
| **2324** | RUC emisor no existe/inactivo | Verificar RUC en SUNAT |
| **2335** | Número de documento duplicado | Verificar numeración |
| **3033** | Formato de documento inválido | Revisar estructura XML |
| **4001** | Serie inválida | Verificar configuración series |

## 📚 Recursos Adicionales

### **Documentación Oficial**
- [SUNAT - Facturación Electrónica](https://cpe.sunat.gob.pe/)
- [Greenter Documentation](https://greenter.dev/)
- [QPSE API Reference](https://qpse.pe/docs)

### **Archivos de Configuración**
- `config/greenter.php` - Configuración Greenter
- `config/qpse.php` - Configuración QPSE
- `app/Services/ElectronicInvoiceService.php` - Servicio principal

## 🎯 Conclusión

La integración **Greenter + QPSE** en el sistema proporciona:

1. **🔧 Flexibilidad**: Múltiples opciones de envío según necesidades
2. **🛡️ Robustez**: Respaldo en dos sistemas de facturación
3. **⚡ Eficiencia**: Automatización completa del proceso
4. **📋 Cumplimiento**: Total adherencia a normativas SUNAT
5. **🚀 Escalabilidad**: Preparado para crecimiento del negocio

Esta arquitectura híbrida garantiza la continuidad operativa y el cumplimiento normativo del sistema de facturación electrónica.