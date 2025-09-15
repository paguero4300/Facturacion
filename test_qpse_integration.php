<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\QpseGreenterAdapter;
use App\Services\QpseService;
use App\Services\GreenterXmlService;

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "🚀 Iniciando prueba de integración QPse con patrón de nombres corregido...\n\n";
    
    // Crear servicios
    $qpseService = new QpseService();
    $greenterService = new GreenterXmlService();
    $adapter = new QpseGreenterAdapter($qpseService, $greenterService);
    
    // Verificar configuración
    if (!$adapter->isConfigured()) {
        throw new Exception('QPse no está configurado correctamente. Verifica las credenciales en .env');
    }
    
    // Obtener datos de ejemplo
    $invoiceData = $greenterService->getExampleInvoiceData();
    
    // Mostrar nombre de archivo generado
    $fileName = $greenterService->getInvoiceFileName($invoiceData);
    echo "📄 Nombre de archivo generado: {$fileName}\n";
    echo "📋 Patrón esperado por QPse: RUC-01-SERIE-CORRELATIVO (sin padding)\n\n";
    
    // Generar XML usando Greenter
    echo "🔧 Generando XML con Greenter...\n";
    $xml = $greenterService->generateInvoiceXml($invoiceData);
    echo "✅ XML generado exitosamente (" . strlen($xml) . " caracteres)\n\n";
    
    // Mostrar primera parte del XML para verificación
    echo "📄 Vista previa del XML generado:\n";
    echo substr($xml, 0, 500) . "...\n\n";
    
    // Procesar con QPse
    echo "🔄 Enviando a QPse para firmar y transmitir...\n";
    $result = $adapter->sendInvoice($invoiceData);
    
    if ($result['success']) {
        echo "✅ ¡ÉXITO! Factura procesada correctamente por QPse\n";
        echo "📝 Mensaje: " . $result['message'] . "\n";
        
        if (isset($result['cdr'])) {
            echo "📋 CDR recibido (" . strlen($result['cdr']) . " caracteres)\n";
        }
        
        if (isset($result['xml_firmado'])) {
            echo "🔐 XML firmado recibido (" . strlen($result['xml_firmado']) . " caracteres)\n";
        }
        
    } else {
        echo "❌ Error en el procesamiento:\n";
        echo "   Código: " . ($result['error']['code'] ?? 'N/A') . "\n";
        echo "   Mensaje: " . ($result['error']['message'] ?? 'Error desconocido') . "\n";
        
        // Mostrar datos de QPse para debug
        if (isset($result['qpse_raw'])) {
            echo "\n🔍 Respuesta completa de QPse:\n";
            print_r($result['qpse_raw']);
        }
    }
    
} catch (Exception $e) {
    echo "💥 Error durante la prueba:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🏁 Prueba completada.\n";