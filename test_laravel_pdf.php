<?php

/**
 * Script de prueba para diagnosticar problemas con Laravel PDF y Browsershot
 *
 * Ejecutar desde el directorio raíz del proyecto:
 * php test_laravel_pdf.php
 */

echo "=================================\n";
echo "🧪 TEST LARAVEL PDF & BROWSERSHOT\n";
echo "=================================\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Directorio: " . getcwd() . "\n\n";

// Autoloader de Laravel
require_once __DIR__ . '/vendor/autoload.php';

try {
    // Inicializar aplicación Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    echo "✅ Laravel inicializado correctamente\n\n";
} catch (Exception $e) {
    echo "❌ Error al inicializar Laravel: " . $e->getMessage() . "\n";
    exit(1);
}

// ==========================================
// 1. VERIFICACIÓN DE PAQUETES
// ==========================================
echo "📦 1. VERIFICACIÓN DE PAQUETES INSTALADOS\n";
echo "---------------------------------------\n";

$requiredPackages = [
    'spatie/laravel-pdf' => 'Spatie\\LaravelPdf\\',
    'spatie/browsershot' => 'Spatie\\Browsershot\\',
    'symfony/process' => 'Symfony\\Component\\Process\\'
];

foreach ($requiredPackages as $package => $class) {
    if (class_exists($class . 'Browsershot') || class_exists($class . 'PdfBuilder') || class_exists($class . 'Process')) {
        echo "✅ $package instalado\n";
    } else {
        echo "❌ $package NO instalado o no encontrado\n";
    }
}
echo "\n";

// ==========================================
// 2. VERIFICACIÓN DE CONFIGURACIÓN
// ==========================================
echo "⚙️ 2. VERIFICACIÓN DE CONFIGURACIÓN\n";
echo "---------------------------------------\n";

// Verificar archivo de configuración
$configPath = config_path('laravel-pdf.php');
if (file_exists($configPath)) {
    echo "✅ Archivo de configuración encontrado: $configPath\n";

    $config = config('laravel-pdf.browsershot');
    echo "📋 Configuración de Browsershot:\n";
    echo "   Node binary: " . ($config['node_binary'] ?? 'No definido') . "\n";
    echo "   NPM binary: " . ($config['npm_binary'] ?? 'No definido') . "\n";
    echo "   Chrome path: " . ($config['chrome_path'] ?? 'No definido') . "\n";
    echo "   Include path: " . ($config['include_path'] ?? 'No definido') . "\n";
    echo "   Node modules path: " . ($config['node_modules_path'] ?? 'No definido') . "\n";
} else {
    echo "❌ Archivo de configuración NO encontrado: $configPath\n";
}
echo "\n";

// ==========================================
// 3. VERIFICACIÓN DE RUTAS Y ARCHIVOS
// ==========================================
echo "📁 3. VERIFICACIÓN DE RUTAS Y ARCHIVOS\n";
echo "---------------------------------------\n";

// Verificar binario de Browsershot
$browsershotBin = __DIR__ . '/vendor/spatie/browsershot/src/../bin/browser.cjs';
if (file_exists($browsershotBin)) {
    echo "✅ Browsershot binary encontrado: $browsershotBin\n";
    echo "   Tamaño: " . filesize($browsershotBin) . " bytes\n";
    echo "   Permisos: " . substr(sprintf('%o', fileperms($browsershotBin)), -4) . "\n";
} else {
    echo "❌ Browsershot binary NO encontrado: $browsershotBin\n";
}

// Verificar directorio temporal
$tempDir = sys_get_temp_dir();
echo "📂 Directorio temporal del sistema: $tempDir\n";
echo "   Escribible: " . (is_writable($tempDir) ? 'Sí' : 'No') . "\n";
echo "   Espacio libre: " . formatBytes(disk_free_space($tempDir)) . "\n";
echo "\n";

// ==========================================
// 4. TEST DE BROWSERSHOT BÁSICO
// ==========================================
echo "🧪 4. TEST DE BROWSERSHOT BÁSICO\n";
echo "---------------------------------------\n";

try {
    use Spatie\Browsershot\Browsershot;

    echo "Creando instancia de Browsershot...\n";
    $browsershot = Browsershot::html('<html><body><h1>Test</h1></body></html>');

    // Verificar configuración de Node
    $nodeCommand = $browsershot->getNodeCommand();
    echo "Comando Node: $nodeCommand\n";

    // Test de detección de binarios
    echo "\n🔍 Detección automática de binarios:\n";
    try {
        $reflection = new ReflectionClass($browsershot);
        $method = $reflection->getMethod('getChromiumCommand');
        $method->setAccessible(true);
        $chromeCommand = $method->invoke($browsershot);
        echo "Chrome command: $chromeCommand\n";
    } catch (Exception $e) {
        echo "No se pudo detectar comando de Chrome: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error al crear Browsershot: " . $e->getMessage() . "\n";
}
echo "\n";

// ==========================================
// 5. TEST DE PDF SIMPLE
// ==========================================
echo "📄 5. TEST DE PDF SIMPLE\n";
echo "---------------------------------------\n";

try {
    use function Spatie\LaravelPdf\Support\pdf;

    echo "Creando PDF de prueba...\n";

    $pdf = pdf()
        ->view('welcome') // Vista básica de Laravel
        ->format('A4')
        ->margins(10, 10, 10, 10);

    // Test 1: PDF sin argumentos especiales
    echo "\nTest 1: PDF básico\n";
    try {
        $pdfContent = $pdf->getBrowsershot()->pdf();
        echo "✅ PDF básico generado exitosamente (" . strlen($pdfContent) . " bytes)\n";
    } catch (Exception $e) {
        echo "❌ Error en PDF básico: " . $e->getMessage() . "\n";
    }

    // Test 2: PDF con argumentos de no-sandbox
    echo "\nTest 2: PDF con no-sandbox\n";
    try {
        $pdfWithArgs = pdf()
            ->view('welcome')
            ->format('A4')
            ->withBrowsershot(function ($browsershot) {
                $browsershot->noSandbox();
            });

        $pdfContent = $pdfWithArgs->getBrowsershot()->pdf();
        echo "✅ PDF con no-sandbox generado exitosamente (" . strlen($pdfContent) . " bytes)\n";
    } catch (Exception $e) {
        echo "❌ Error en PDF con no-sandbox: " . $e->getMessage() . "\n";
    }

    // Test 3: PDF con argumentos completos anti-memlock
    echo "\nTest 3: PDF con argumentos anti-memlock\n";
    try {
        $pdfAntiMemlock = pdf()
            ->view('welcome')
            ->format('A4')
            ->withBrowsershot(function ($browsershot) {
                $browsershot
                    ->noSandbox()
                    ->setOption('args', [
                        '--disable-setuid-sandbox',
                        '--disable-dev-shm-usage',
                        '--disable-gpu',
                        '--single-process',
                        '--no-zygote',
                        '--memory-pressure-off'
                    ]);
            });

        $pdfContent = $pdfAntiMemlock->getBrowsershot()->pdf();
        echo "✅ PDF anti-memlock generado exitosamente (" . strlen($pdfContent) . " bytes)\n";
    } catch (Exception $e) {
        echo "❌ Error en PDF anti-memlock: " . $e->getMessage() . "\n";
        echo "   Detalles: " . $e->getFile() . ":" . $e->getLine() . "\n";

        // Intentar capturar la salida del proceso si es posible
        if (method_exists($e, 'getProcessOutput')) {
            echo "   Salida del proceso: " . $e->getProcessOutput() . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error general en tests de PDF: " . $e->getMessage() . "\n";
}
echo "\n";

// ==========================================
// 6. VERIFICACIÓN DE VARIABLES DE ENTORNO
// ==========================================
echo "🌍 6. VARIABLES DE ENTORNO PHP\n";
echo "---------------------------------------\n";

$envVars = [
    'PATH',
    'NODE_PATH',
    'HOME',
    'USER',
    'TMPDIR',
    'BROWSERSHOT_NODE_BINARY',
    'BROWSERSHOT_CHROME_PATH'
];

foreach ($envVars as $var) {
    $value = getenv($var) ?: $_ENV[$var] ?? $_SERVER[$var] ?? 'No definido';
    echo "$var: $value\n";
}
echo "\n";

// ==========================================
// 7. INFORMACIÓN DEL SISTEMA PHP
// ==========================================
echo "🔧 7. INFORMACIÓN DEL SISTEMA PHP\n";
echo "---------------------------------------\n";

echo "Sistema operativo: " . php_uname() . "\n";
echo "Versión PHP: " . phpversion() . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "Memory limit: " . ini_get('memory_limit') . "\n";
echo "Max execution time: " . ini_get('max_execution_time') . "\n";
echo "Upload max filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post max size: " . ini_get('post_max_size') . "\n";

// Verificar extensiones necesarias
$requiredExtensions = ['curl', 'json', 'mbstring', 'zip'];
echo "\nExtensiones PHP:\n";
foreach ($requiredExtensions as $ext) {
    echo "  $ext: " . (extension_loaded($ext) ? '✅ Cargada' : '❌ No cargada') . "\n";
}
echo "\n";

// ==========================================
// 8. RECOMENDACIONES FINALES
// ==========================================
echo "💡 8. RECOMENDACIONES FINALES\n";
echo "---------------------------------------\n";

echo "Para resolver problemas de Browsershot:\n\n";

echo "1. 🔧 Si hay errores de memlock:\n";
echo "   sudo bash -c 'echo \"www-data soft memlock unlimited\" >> /etc/security/limits.conf'\n";
echo "   sudo bash -c 'echo \"www-data hard memlock unlimited\" >> /etc/security/limits.conf'\n";
echo "   sudo systemctl restart nginx\n\n";

echo "2. 📦 Si faltan dependencias:\n";
echo "   sudo apt-get update\n";
echo "   sudo apt-get install -y chromium-browser nodejs npm\n\n";

echo "3. 🛠️ Configuración Laravel PDF en .env:\n";
echo "   BROWSERSHOT_CHROME_PATH=/usr/bin/chromium-browser\n";
echo "   BROWSERSHOT_NODE_BINARY=/usr/bin/node\n\n";

echo "4. 🧪 Para más debugging, ejecuta también:\n";
echo "   bash diagnostico_browsershot.sh\n\n";

echo "✅ Diagnóstico PHP completado.\n";
echo "=================================\n";

// Función helper
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }

    return round($size, $precision) . ' ' . $units[$i];
}