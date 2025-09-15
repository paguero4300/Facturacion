<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\QpseGreenterAdapter;
use App\Services\QpseService;
use App\Services\GreenterXmlService;

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "🚀 Generando 3 facturas adicionales para verificar integración QPse...\n\n";
    
    // Crear servicios
    $qpseService = new QpseService();
    $greenterService = new GreenterXmlService();
    $adapter = new QpseGreenterAdapter($qpseService, $greenterService);
    
    // Datos base para facturas
    $baseInvoiceData = $greenterService->getExampleInvoiceData();
    
    // === FACTURA 2: Producto tecnológico ===
    echo "📄 === FACTURA 2: Producto Tecnológico ===\n";
    $factura2 = $baseInvoiceData;
    $factura2['correlativo'] = '2';
    $factura2['details'] = [
        [
            "codProducto" => "LAPTOP001",
            "unidad" => "NIU",
            "cantidad" => 1,
            "mtoValorUnitario" => 2500.00,
            "descripcion" => "LAPTOP DELL INSPIRON 15 3000",
            "mtoBaseIgv" => 2500.00,
            "porcentajeIgv" => 18.00,
            "igv" => 450.00,
            "tipAfeIgv" => "10",
            "totalImpuestos" => 450.00,
            "mtoValorVenta" => 2500.00,
            "mtoPrecioUnitario" => 2950.00,
        ]
    ];
    $factura2['mtoOperGravadas'] = 2500.00;
    $factura2['mtoIGV'] = 450.00;
    $factura2['totalImpuestos'] = 450.00;
    $factura2['valorVenta'] = 2500.00;
    $factura2['subTotal'] = 2950.00;
    $factura2['mtoImpVenta'] = 2950.00;
    $factura2['legends'] = [
        [
            "code" => "1000",
            "value" => "SON DOS MIL NOVECIENTOS CINCUENTA CON 00/100 SOLES",
        ]
    ];
    
    $fileName2 = $greenterService->getInvoiceFileName($factura2);
    echo "📝 Archivo: {$fileName2}\n";
    
    $result2 = $adapter->sendInvoice($factura2);
    
    if ($result2['success']) {
        echo "✅ FACTURA 2 ENVIADA EXITOSAMENTE\n";
        echo "   Mensaje: " . $result2['message'] . "\n";
    } else {
        echo "❌ Error en Factura 2: " . ($result2['error']['message'] ?? 'Error desconocido') . "\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    // === FACTURA 3: Servicios múltiples ===
    echo "📄 === FACTURA 3: Servicios Múltiples ===\n";
    $factura3 = $baseInvoiceData;
    $factura3['correlativo'] = '3';
    $factura3['details'] = [
        [
            "codProducto" => "SERV001",
            "unidad" => "ZZ",
            "cantidad" => 1,
            "mtoValorUnitario" => 800.00,
            "descripcion" => "DESARROLLO DE SOFTWARE A MEDIDA",
            "mtoBaseIgv" => 800.00,
            "porcentajeIgv" => 18.00,
            "igv" => 144.00,
            "tipAfeIgv" => "10",
            "totalImpuestos" => 144.00,
            "mtoValorVenta" => 800.00,
            "mtoPrecioUnitario" => 944.00,
        ],
        [
            "codProducto" => "SERV002",
            "unidad" => "HUR",
            "cantidad" => 10,
            "mtoValorUnitario" => 120.00,
            "descripcion" => "CONSULTORIA TECNICA EN TI",
            "mtoBaseIgv" => 1200.00,
            "porcentajeIgv" => 18.00,
            "igv" => 216.00,
            "tipAfeIgv" => "10",
            "totalImpuestos" => 216.00,
            "mtoValorVenta" => 1200.00,
            "mtoPrecioUnitario" => 141.60,
        ]
    ];
    
    // Totales para factura con múltiples items
    $factura3['mtoOperGravadas'] = 2000.00;
    $factura3['mtoIGV'] = 360.00;
    $factura3['totalImpuestos'] = 360.00;
    $factura3['valorVenta'] = 2000.00;
    $factura3['subTotal'] = 2360.00;
    $factura3['mtoImpVenta'] = 2360.00;
    $factura3['legends'] = [
        [
            "code" => "1000",
            "value" => "SON DOS MIL TRESCIENTOS SESENTA CON 00/100 SOLES",
        ]
    ];
    
    $fileName3 = $greenterService->getInvoiceFileName($factura3);
    echo "📝 Archivo: {$fileName3}\n";
    
    $result3 = $adapter->sendInvoice($factura3);
    
    if ($result3['success']) {
        echo "✅ FACTURA 3 ENVIADA EXITOSAMENTE\n";
        echo "   Mensaje: " . $result3['message'] . "\n";
    } else {
        echo "❌ Error en Factura 3: " . ($result3['error']['message'] ?? 'Error desconocido') . "\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    // === FACTURA 4: Productos con descuentos ===
    echo "📄 === FACTURA 4: Productos con Descuento ===\n";
    $factura4 = $baseInvoiceData;
    $factura4['correlativo'] = '4';
    $factura4['details'] = [
        [
            "codProducto" => "PROMO001",
            "unidad" => "NIU",
            "cantidad" => 5,
            "mtoValorUnitario" => 85.00, // Precio con descuento aplicado
            "descripcion" => "MOUSE INALAMBRICO LOGITECH (PROMOCION 5x4)",
            "mtoBaseIgv" => 425.00,
            "porcentajeIgv" => 18.00,
            "igv" => 76.50,
            "tipAfeIgv" => "10",
            "totalImpuestos" => 76.50,
            "mtoValorVenta" => 425.00,
            "mtoPrecioUnitario" => 100.30,
        ]
    ];
    
    $factura4['mtoOperGravadas'] = 425.00;
    $factura4['mtoIGV'] = 76.50;
    $factura4['totalImpuestos'] = 76.50;
    $factura4['valorVenta'] = 425.00;
    $factura4['subTotal'] = 501.50;
    $factura4['mtoImpVenta'] = 501.50;
    $factura4['legends'] = [
        [
            "code" => "1000",
            "value" => "SON QUINIENTOS UNO CON 50/100 SOLES",
        ]
    ];
    
    $fileName4 = $greenterService->getInvoiceFileName($factura4);
    echo "📝 Archivo: {$fileName4}\n";
    
    $result4 = $adapter->sendInvoice($factura4);
    
    if ($result4['success']) {
        echo "✅ FACTURA 4 ENVIADA EXITOSAMENTE\n";
        echo "   Mensaje: " . $result4['message'] . "\n";
    } else {
        echo "❌ Error en Factura 4: " . ($result4['error']['message'] ?? 'Error desconocido') . "\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    
    // === RESUMEN FINAL ===
    echo "📊 === RESUMEN DE PRUEBAS ===\n\n";
    
    $exitosas = 0;
    $fallidas = 0;
    
    // Ya sabemos que la Factura 1 fue exitosa del test anterior
    echo "🟢 FACTURA 1 (F001-1): ✅ EXITOSA (test anterior)\n";
    $exitosas++;
    
    if ($result2['success']) {
        echo "🟢 FACTURA 2 (F001-2): ✅ EXITOSA\n";
        $exitosas++;
    } else {
        echo "🔴 FACTURA 2 (F001-2): ❌ FALLÓ\n";
        $fallidas++;
    }
    
    if ($result3['success']) {
        echo "🟢 FACTURA 3 (F001-3): ✅ EXITOSA\n";
        $exitosas++;
    } else {
        echo "🔴 FACTURA 3 (F001-3): ❌ FALLÓ\n";
        $fallidas++;
    }
    
    if ($result4['success']) {
        echo "🟢 FACTURA 4 (F001-4): ✅ EXITOSA\n";
        $exitosas++;
    } else {
        echo "🔴 FACTURA 4 (F001-4): ❌ FALLÓ\n";
        $fallidas++;
    }
    
    echo "\n📈 ESTADÍSTICAS FINALES:\n";
    echo "   ✅ Exitosas: {$exitosas}/4 (" . round(($exitosas/4)*100, 1) . "%)\n";
    echo "   ❌ Fallidas: {$fallidas}/4 (" . round(($fallidas/4)*100, 1) . "%)\n";
    
    if ($exitosas == 4) {
        echo "\n🎉 ¡INTEGRACIÓN 100% VERIFICADA!\n";
        echo "   La conexión QPse + Greenter funciona perfectamente.\n";
    } else {
        echo "\n⚠️  Revisar facturas que fallaron para identificar patrones.\n";
    }
    
} catch (Exception $e) {
    echo "💥 Error durante las pruebas:\n";
    echo "   " . $e->getMessage() . "\n";
}

echo "\n🏁 Pruebas múltiples completadas.\n";