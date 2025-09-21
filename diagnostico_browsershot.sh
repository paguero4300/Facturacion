#!/bin/bash

echo "==================================="
echo "🔍 DIAGNÓSTICO BROWSERSHOT & CHROMIUM"
echo "==================================="
echo "Fecha: $(date)"
echo "Usuario actual: $(whoami)"
echo "Directorio: $(pwd)"
echo ""

# ==========================================
# 1. INFORMACIÓN DEL SISTEMA
# ==========================================
echo "📋 1. INFORMACIÓN DEL SISTEMA"
echo "------------------------------------"
echo "Sistema operativo: $(uname -a)"
echo "Distribución: $(lsb_release -d 2>/dev/null || echo 'No disponible')"
echo "Arquitectura: $(uname -m)"
echo "Memoria total: $(free -h | grep Mem | awk '{print $2}')"
echo "Memoria disponible: $(free -h | grep Mem | awk '{print $7}')"
echo "Espacio en disco (/tmp): $(df -h /tmp | tail -1 | awk '{print $4}')"
echo ""

# ==========================================
# 2. VERIFICACIÓN DE NODE.JS
# ==========================================
echo "🟢 2. VERIFICACIÓN DE NODE.JS"
echo "------------------------------------"

# Verificar Node.js
if command -v node &> /dev/null; then
    echo "✅ Node.js encontrado: $(which node)"
    echo "   Versión: $(node --version)"
else
    echo "❌ Node.js NO encontrado"
fi

# Verificar NPM
if command -v npm &> /dev/null; then
    echo "✅ NPM encontrado: $(which npm)"
    echo "   Versión: $(npm --version)"
else
    echo "❌ NPM NO encontrado"
fi

# Verificar rutas de Node
echo "📁 Rutas de Node:"
echo "   NODE_PATH: ${NODE_PATH:-'No definido'}"
echo "   /usr/bin/node: $(ls -la /usr/bin/node 2>/dev/null || echo 'No existe')"
echo "   /usr/local/bin/node: $(ls -la /usr/local/bin/node 2>/dev/null || echo 'No existe')"
echo "   node_modules globales: $(npm root -g 2>/dev/null || echo 'No disponible')"
echo ""

# ==========================================
# 3. VERIFICACIÓN DE CHROMIUM/CHROME
# ==========================================
echo "🔵 3. VERIFICACIÓN DE CHROMIUM/CHROME"
echo "------------------------------------"

# Lista de posibles ubicaciones de Chromium
CHROME_PATHS=(
    "/usr/bin/chromium-browser"
    "/usr/bin/chromium"
    "/usr/bin/google-chrome"
    "/usr/bin/google-chrome-stable"
    "/snap/bin/chromium"
    "/opt/google/chrome/chrome"
)

echo "🔍 Buscando ejecutables de Chrome/Chromium:"
for path in "${CHROME_PATHS[@]}"; do
    if [ -f "$path" ]; then
        echo "✅ Encontrado: $path"
        echo "   Versión: $($path --version 2>/dev/null || echo 'Error al obtener versión')"
        echo "   Permisos: $(ls -la $path)"
        echo "   Ejecutable: $(test -x $path && echo 'Sí' || echo 'No')"

        # Test básico de ejecución
        echo "   Test de ejecución:"
        timeout 10s $path --headless --disable-gpu --dump-dom about:blank > /dev/null 2>&1
        if [ $? -eq 0 ]; then
            echo "   ✅ Test básico exitoso"
        else
            echo "   ❌ Test básico falló (código: $?)"
        fi
    else
        echo "❌ No encontrado: $path"
    fi
done
echo ""

# ==========================================
# 4. VERIFICACIÓN DE PERMISOS Y LÍMITES
# ==========================================
echo "🔒 4. VERIFICACIÓN DE PERMISOS Y LÍMITES"
echo "------------------------------------"

# Límites del usuario actual
echo "📊 Límites del usuario actual ($(whoami)):"
echo "   ulimit -a:"
ulimit -a | while read line; do echo "     $line"; done

# Verificar límites específicos
echo ""
echo "🔍 Límites específicos:"
echo "   Memory lock (memlock): $(ulimit -l)"
echo "   Open files: $(ulimit -n)"
echo "   Max processes: $(ulimit -u)"
echo "   Virtual memory: $(ulimit -v)"

# Verificar configuración en /etc/security/limits.conf
echo ""
echo "📄 Configuración en /etc/security/limits.conf:"
if [ -f /etc/security/limits.conf ]; then
    grep -E "(memlock|nofile|nproc)" /etc/security/limits.conf | grep -v "^#" || echo "   Sin configuraciones específicas"
else
    echo "   Archivo no encontrado"
fi

# Verificar permisos en /tmp
echo ""
echo "📁 Permisos en directorios temporales:"
echo "   /tmp: $(ls -ld /tmp)"
echo "   Espacio disponible en /tmp: $(df -h /tmp | tail -1 | awk '{print $4}')"

# Verificar /dev/shm
if [ -d /dev/shm ]; then
    echo "   /dev/shm: $(ls -ld /dev/shm)"
    echo "   Espacio en /dev/shm: $(df -h /dev/shm | tail -1 | awk '{print $4}')"
else
    echo "   /dev/shm: No existe"
fi
echo ""

# ==========================================
# 5. VERIFICACIÓN DE BROWSERSHOT
# ==========================================
echo "📦 5. VERIFICACIÓN DE BROWSERSHOT"
echo "------------------------------------"

# Verificar si existe el binario de Browsershot
BROWSERSHOT_BIN="/var/www/facturacion/vendor/spatie/browsershot/src/../bin/browser.cjs"
if [ -f "$BROWSERSHOT_BIN" ]; then
    echo "✅ Browsershot binary encontrado: $BROWSERSHOT_BIN"
    echo "   Permisos: $(ls -la $BROWSERSHOT_BIN)"
    echo "   Ejecutable: $(test -x $BROWSERSHOT_BIN && echo 'Sí' || echo 'No')"
else
    echo "❌ Browsershot binary NO encontrado en: $BROWSERSHOT_BIN"
fi

# Verificar directorio vendor
VENDOR_DIR="/var/www/facturacion/vendor"
if [ -d "$VENDOR_DIR" ]; then
    echo "✅ Directorio vendor existe: $VENDOR_DIR"
    echo "   Permisos: $(ls -ld $VENDOR_DIR)"
else
    echo "❌ Directorio vendor NO encontrado: $VENDOR_DIR"
fi

# Verificar node_modules de Puppeteer
PUPPETEER_DIR="/var/www/facturacion/node_modules/puppeteer"
if [ -d "$PUPPETEER_DIR" ]; then
    echo "✅ Puppeteer encontrado: $PUPPETEER_DIR"
else
    echo "❌ Puppeteer NO encontrado en: $PUPPETEER_DIR"
fi
echo ""

# ==========================================
# 6. TEST DE CHROMIUM CON ARGUMENTOS
# ==========================================
echo "🧪 6. TEST DE CHROMIUM CON ARGUMENTOS"
echo "------------------------------------"

# Encontrar la primera versión disponible de Chrome/Chromium
CHROME_EXEC=""
for path in "${CHROME_PATHS[@]}"; do
    if [ -f "$path" ] && [ -x "$path" ]; then
        CHROME_EXEC="$path"
        break
    fi
done

if [ -n "$CHROME_EXEC" ]; then
    echo "🔧 Usando Chrome/Chromium: $CHROME_EXEC"

    # Test 1: Sin argumentos especiales
    echo ""
    echo "Test 1: Ejecución básica"
    timeout 15s $CHROME_EXEC --headless --disable-gpu --dump-dom about:blank > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "✅ Test básico exitoso"
    else
        echo "❌ Test básico falló (código: $?)"
    fi

    # Test 2: Con argumentos de sandbox
    echo ""
    echo "Test 2: Con argumentos de no-sandbox"
    timeout 15s $CHROME_EXEC --headless --disable-gpu --no-sandbox --disable-setuid-sandbox --dump-dom about:blank > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "✅ Test no-sandbox exitoso"
    else
        echo "❌ Test no-sandbox falló (código: $?)"
    fi

    # Test 3: Con argumentos completos de memlock
    echo ""
    echo "Test 3: Con argumentos anti-memlock"
    timeout 15s $CHROME_EXEC --headless --disable-gpu --no-sandbox --disable-setuid-sandbox --disable-dev-shm-usage --single-process --no-zygote --dump-dom about:blank > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "✅ Test anti-memlock exitoso"
    else
        echo "❌ Test anti-memlock falló (código: $?)"
    fi

    # Test 4: Captura de errores detallados
    echo ""
    echo "Test 4: Captura de errores detallados"
    echo "Ejecutando con salida de errores..."
    timeout 15s $CHROME_EXEC --headless --disable-gpu --no-sandbox --disable-setuid-sandbox --disable-dev-shm-usage --single-process --no-zygote --dump-dom about:blank 2>&1 | head -20

else
    echo "❌ No se encontró ningún ejecutable de Chrome/Chromium válido"
fi
echo ""

# ==========================================
# 7. VERIFICACIÓN DE VARIABLES DE ENTORNO
# ==========================================
echo "🌍 7. VARIABLES DE ENTORNO"
echo "------------------------------------"
echo "PATH: $PATH"
echo "NODE_PATH: ${NODE_PATH:-'No definido'}"
echo "HOME: ${HOME:-'No definido'}"
echo "USER: ${USER:-'No definido'}"
echo "TMPDIR: ${TMPDIR:-'No definido'}"
echo "BROWSERSHOT_NODE_BINARY: ${BROWSERSHOT_NODE_BINARY:-'No definido'}"
echo "BROWSERSHOT_CHROME_PATH: ${BROWSERSHOT_CHROME_PATH:-'No definido'}"
echo ""

# ==========================================
# 8. INFORMACIÓN DE PROCESOS
# ==========================================
echo "⚙️ 8. INFORMACIÓN DE PROCESOS"
echo "------------------------------------"
echo "Procesos de Chrome/Chromium activos:"
ps aux | grep -E "(chrome|chromium)" | grep -v grep || echo "Ninguno encontrado"
echo ""
echo "Procesos de Node activos:"
ps aux | grep -E "node" | grep -v grep || echo "Ninguno encontrado"
echo ""

# ==========================================
# 9. TEST DE BROWSERSHOT DIRECTO
# ==========================================
echo "🎯 9. TEST DE BROWSERSHOT DIRECTO"
echo "------------------------------------"

# Cambiar al directorio del proyecto
cd /var/www/facturacion

if [ -f "$BROWSERSHOT_BIN" ] && command -v node &> /dev/null; then
    echo "🔧 Intentando ejecutar Browsershot directamente..."

    # Crear un JSON simple para test
    TEST_JSON='{"url":"data:text/html,<html><body><h1>Test</h1></body></html>","action":"pdf","options":{"args":["--no-sandbox","--disable-setuid-sandbox","--disable-dev-shm-usage","--disable-gpu"],"format":"A4"}}'

    echo "JSON de test: $TEST_JSON"
    echo ""
    echo "Ejecutando comando Browsershot:"
    echo "PATH=/usr/bin:/usr/local/bin NODE_PATH='/usr/lib/node_modules' /usr/bin/node '$BROWSERSHOT_BIN' '$TEST_JSON'"
    echo ""

    # Ejecutar el comando con timeout
    timeout 30s bash -c "PATH=/usr/bin:/usr/local/bin NODE_PATH='/usr/lib/node_modules' /usr/bin/node '$BROWSERSHOT_BIN' '$TEST_JSON'" 2>&1 | head -50

    EXIT_CODE=${PIPESTATUS[0]}
    echo ""
    echo "Código de salida: $EXIT_CODE"

else
    echo "❌ No se puede ejecutar test directo (falta Node.js o Browsershot binary)"
fi
echo ""

# ==========================================
# 10. RECOMENDACIONES
# ==========================================
echo "💡 10. RECOMENDACIONES"
echo "------------------------------------"

echo "Basado en el diagnóstico, aquí tienes algunas recomendaciones:"
echo ""

# Verificar si memlock es el problema
MEMLOCK_LIMIT=$(ulimit -l)
if [ "$MEMLOCK_LIMIT" != "unlimited" ] && [ "$MEMLOCK_LIMIT" -lt 524288 ]; then
    echo "⚠️  PROBLEMA DETECTADO: Límite de memlock muy bajo ($MEMLOCK_LIMIT)"
    echo "   Solución 1: Aumentar límite con 'ulimit -l unlimited'"
    echo "   Solución 2: Agregar en /etc/security/limits.conf:"
    echo "              www-data soft memlock unlimited"
    echo "              www-data hard memlock unlimited"
    echo ""
fi

# Verificar Chrome
CHROME_FOUND=false
for path in "${CHROME_PATHS[@]}"; do
    if [ -f "$path" ] && [ -x "$path" ]; then
        CHROME_FOUND=true
        break
    fi
done

if [ "$CHROME_FOUND" = false ]; then
    echo "⚠️  PROBLEMA DETECTADO: No se encontró Chrome/Chromium"
    echo "   Solución: Instalar Chromium con:"
    echo "            sudo apt-get update && sudo apt-get install -y chromium-browser"
    echo ""
fi

# Verificar Node.js
if ! command -v node &> /dev/null; then
    echo "⚠️  PROBLEMA DETECTADO: Node.js no encontrado"
    echo "   Solución: Instalar Node.js con:"
    echo "            curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -"
    echo "            sudo apt-get install -y nodejs"
    echo ""
fi

echo "✅ Diagnóstico completado. Revisa los resultados arriba para identificar problemas."
echo "==================================="