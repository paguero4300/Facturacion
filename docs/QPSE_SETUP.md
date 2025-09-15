# Configuración QPse + Greenter

Esta guía te ayuda a configurar la integración de Laravel Greenter con QPse (Proveedor de Servicios Electrónicos) en modo demo/desarrollo.

## 🔧 Archivos Creados

La configuración ha creado los siguientes archivos:

```
app/
├── Services/
│   ├── QpseService.php              # Servicio principal QPse
│   └── QpseGreenterAdapter.php      # Adaptador Greenter <-> QPse
├── Providers/
│   └── QpseServiceProvider.php      # Service Provider
├── Facades/
│   ├── Qpse.php                     # Facade QPse directo
│   └── QpseGreenter.php             # Facade Greenter + QPse
└── Console/Commands/
    └── QpseSetupCommand.php         # Comando de configuración

config/
└── qpse.php                         # Configuración QPse

.env                                 # Variables añadidas
```

## 🚀 Configuración Inicial

### 1. Variables de Entorno

Las siguientes variables se han añadido a tu `.env`:

```bash
# Configuración QPse (PSE) para Facturación Electrónica
QPSE_MODE=demo
QPSE_URL=https://demo-cpe.qpse.pe
QPSE_TOKEN=tu_token_de_configuracion_aqui
QPSE_USERNAME=
QPSE_PASSWORD=

# Datos de la empresa
COMPANY_RUC=20000000001
COMPANY_NAME="MI EMPRESA DEMO SAC"
COMPANY_ADDRESS="Av. Ejemplo 123, Lima, Perú"
```

### 2. Obtener Token de QPse

Debes obtener tu token de configuración desde el panel de QPse y actualizarlo en `QPSE_TOKEN`.

### 3. Ejecutar Configuración Automática

```bash
php artisan qpse:setup [RUC] [--plan=01]
```

Este comando:
- ✅ Crea tu empresa en QPse
- ✅ Obtiene credenciales automáticamente
- ✅ Actualiza tu `.env` con las credenciales
- ✅ Prueba la conexión

**Ejemplo:**
```bash
php artisan qpse:setup 20123456789 --plan=01
```

## 📝 Uso Básico

### Opción 1: Usando el Facade QpseGreenter (Recomendado)

```php
<?php

use App\Facades\QpseGreenter;

// Enviar factura
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
            'descripcion' => 'Producto de ejemplo',
            'cantidad' => 1,
            'mtoValorUnitario' => 100.00,
            'igv' => 18.00
        ]
    ],
    'mtoIGV' => 18.00,
    'mtoImpVenta' => 118.00
]);

if ($response['success']) {
    echo "✅ Factura enviada correctamente";
    // $cdr contiene la respuesta de SUNAT
    $cdr = $response['cdr'];
} else {
    echo "❌ Error: " . $response['error']['message'];
}
```

### Opción 2: Usando el Facade QPse (Directo)

```php
<?php

use App\Facades\Qpse;

// Generar XML (con Greenter u otra librería)
$xml = "<?xml version='1.0' encoding='UTF-8'?>...";

// Procesar documento completo
$response = Qpse::procesarDocumento('20123456789-01-F001-00000001.xml', $xml);

if ($response['envio']['estado'] === 'ACEPTADO') {
    echo "✅ Documento aceptado por SUNAT";
    $cdr = $response['cdr']; // CDR en formato binario
} else {
    echo "❌ Error: " . $response['envio']['mensaje'];
}
```

### Opción 3: Usando el Servicio Directamente

```php
<?php

use App\Services\QpseService;

$qpse = new QpseService();

// Configurar credenciales si no están en .env
// $qpse->setCredenciales('usuario', 'contraseña');

// Procesar documento paso a paso
$firma = $qpse->firmarXml('factura.xml', $xmlContent);
$envio = $qpse->enviarXmlFirmado('factura_firmado.xml', $xmlFirmado);
```

## 🎯 Ejemplo de Controlador

```php
<?php

namespace App\Http\Controllers;

use App\Facades\QpseGreenter;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_ruc' => 'required|size:11',
            'cliente_nombre' => 'required|string',
            'productos' => 'required|array'
        ]);

        $invoiceData = [
            'serie' => 'F001',
            'numero' => $this->getNextNumber(),
            'fechaEmision' => now(),
            'tipoMoneda' => 'PEN',
            'cliente' => [
                'tipoDoc' => '6',
                'numDoc' => $validated['cliente_ruc'],
                'rznSocial' => $validated['cliente_nombre']
            ],
            // ... más datos de la factura
        ];

        $response = QpseGreenter::sendInvoice($invoiceData);

        if ($response['success']) {
            // Guardar en base de datos
            // Generar PDF
            // Enviar por email
            
            return response()->json([
                'success' => true,
                'message' => 'Factura enviada correctamente a SUNAT',
                'data' => $response
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => $response['error']['message']
            ], 400);
        }
    }
}
```

## 🔍 Debugging

### Habilitar Logs y XMLs

En `config/qpse.php` o `.env`:

```bash
QPSE_LOGS_ENABLED=true
QPSE_SAVE_XMLS=true
```

Los XMLs se guardarán en `storage/app/qpse/xmls/`.

### Verificar Configuración

```php
use App\Facades\QpseGreenter;

if (QpseGreenter::isConfigured()) {
    echo "✅ QPse configurado correctamente";
} else {
    echo "❌ QPse no configurado";
}
```

## 🌍 Entornos

### Demo/Desarrollo (Actual)
- URL: `https://demo-cpe.qpse.pe`
- Para pruebas y desarrollo
- No requiere certificados reales

### Producción (Futuro)
- URL: `https://cpe.qpse.pe`
- Cambiar `QPSE_MODE=production` en `.env`
- Actualizar `QPSE_URL=https://cpe.qpse.pe`

## ⚠️ Notas Importantes

1. **Token de configuración**: Debes obtenerlo desde tu panel QPse
2. **Credenciales automáticas**: Se generan al ejecutar `qpse:setup`
3. **XMLs básicos**: Los XMLs actuales son ejemplos, debes integrar con Greenter real
4. **Renovación de tokens**: Los tokens de acceso expiran, el servicio los renueva automáticamente
5. **Manejo de errores**: Siempre verifica `$response['success']` antes de procesar

## 🚨 Solución de Problemas

### Error: "QPSE_TOKEN no configurado"
```bash
# Añade tu token en .env
QPSE_TOKEN=tu_token_real_de_qpse
```

### Error: "Credenciales QPse no configuradas"
```bash
# Ejecuta el comando de configuración
php artisan qpse:setup
```

### Error: "Error al crear empresa"
- Verifica que tu token sea válido
- Asegúrate de estar usando el entorno correcto (demo/producción)
- Revisa que el RUC sea válido

Con esta configuración tienes una integración completa de Laravel Greenter con QPse lista para usar en modo demo. ¡Solo necesitas tu token de QPse para comenzar! 🚀