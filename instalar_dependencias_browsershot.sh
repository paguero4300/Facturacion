#!/bin/bash

echo "==========================================="
echo "🔧 INSTALADOR DE DEPENDENCIAS BROWSERSHOT"
echo "==========================================="
echo "Este script instalará y configurará las dependencias necesarias"
echo "para que Browsershot funcione correctamente."
echo ""

# Verificar si se ejecuta como root
if [ "$EUID" -ne 0 ]; then
    echo "⚠️  Este script necesita permisos de administrador."
    echo "   Por favor ejecuta: sudo $0"
    exit 1
fi

echo "✅ Ejecutándose con permisos de administrador"
echo ""

# ==========================================
# 1. ACTUALIZAR SISTEMA
# ==========================================
echo "📦 1. ACTUALIZANDO SISTEMA"
echo "------------------------------------"
apt-get update
echo "✅ Sistema actualizado"
echo ""

# ==========================================
# 2. INSTALAR CHROMIUM
# ==========================================
echo "🔵 2. INSTALANDO CHROMIUM"
echo "------------------------------------"

# Verificar si ya está instalado
if command -v chromium-browser &> /dev/null; then
    echo "✅ Chromium ya está instalado: $(chromium-browser --version)"
else
    echo "📦 Instalando Chromium..."
    apt-get install -y chromium-browser

    if command -v chromium-browser &> /dev/null; then
        echo "✅ Chromium instalado exitosamente: $(chromium-browser --version)"
    else
        echo "❌ Error al instalar Chromium"
        exit 1
    fi
fi
echo ""

# ==========================================
# 3. INSTALAR NODE.JS
# ==========================================
echo "🟢 3. INSTALANDO NODE.JS"
echo "------------------------------------"

# Verificar si ya está instalado
if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version)
    echo "✅ Node.js ya está instalado: $NODE_VERSION"

    # Verificar si es una versión muy antigua
    NODE_MAJOR=$(echo $NODE_VERSION | cut -d'.' -f1 | sed 's/v//')
    if [ "$NODE_MAJOR" -lt 16 ]; then
        echo "⚠️  Versión de Node.js muy antigua ($NODE_VERSION), actualizando..."
        INSTALL_NODE=true
    else
        INSTALL_NODE=false
    fi
else
    echo "📦 Node.js no encontrado, instalando..."
    INSTALL_NODE=true
fi

if [ "$INSTALL_NODE" = true ]; then
    # Instalar Node.js 18.x
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs

    if command -v node &> /dev/null; then
        echo "✅ Node.js instalado exitosamente: $(node --version)"
    else
        echo "❌ Error al instalar Node.js"
        exit 1
    fi
fi

# Verificar NPM
if command -v npm &> /dev/null; then
    echo "✅ NPM disponible: $(npm --version)"
else
    echo "❌ NPM no disponible"
    exit 1
fi
echo ""

# ==========================================
# 4. INSTALAR DEPENDENCIAS ADICIONALES
# ==========================================
echo "📚 4. INSTALANDO DEPENDENCIAS ADICIONALES"
echo "------------------------------------"

# Dependencias comunes para Chromium en headless
PACKAGES=(
    "fonts-liberation"
    "libappindicator3-1"
    "libasound2"
    "libatk-bridge2.0-0"
    "libdrm2"
    "libgtk-3-0"
    "libnspr4"
    "libnss3"
    "libxcomposite1"
    "libxdamage1"
    "libxrandr2"
    "xdg-utils"
    "libxss1"
    "libgconf-2-4"
)

echo "Instalando dependencias de Chromium..."
for package in "${PACKAGES[@]}"; do
    if dpkg -l | grep -q "^ii  $package "; then
        echo "✅ $package ya está instalado"
    else
        echo "📦 Instalando $package..."
        apt-get install -y "$package" 2>/dev/null || echo "⚠️  No se pudo instalar $package (puede no ser crítico)"
    fi
done
echo ""

# ==========================================
# 5. CONFIGURAR LÍMITES DE MEMORIA
# ==========================================
echo "🔒 5. CONFIGURANDO LÍMITES DE MEMORIA"
echo "------------------------------------"

# Detectar usuario del servidor web
WEB_USER=""
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
fi

if [ -n "$WEB_USER" ]; then
    echo "👤 Usuario del servidor web detectado: $WEB_USER"

    # Verificar si ya está configurado
    if grep -q "$WEB_USER.*memlock" /etc/security/limits.conf; then
        echo "✅ Límites de memlock ya configurados para $WEB_USER"
    else
        echo "🔧 Configurando límites de memlock para $WEB_USER..."

        # Hacer backup del archivo
        cp /etc/security/limits.conf /etc/security/limits.conf.backup.$(date +%Y%m%d_%H%M%S)

        # Agregar configuraciones
        cat >> /etc/security/limits.conf << EOF

# Configuración para Browsershot/Chromium
$WEB_USER soft memlock unlimited
$WEB_USER hard memlock unlimited
$WEB_USER soft nofile 65536
$WEB_USER hard nofile 65536
EOF

        echo "✅ Límites de memlock configurados"
    fi
else
    echo "⚠️  No se pudo detectar el usuario del servidor web"
    echo "   Configura manualmente en /etc/security/limits.conf:"
    echo "   tu_usuario_web soft memlock unlimited"
    echo "   tu_usuario_web hard memlock unlimited"
fi
echo ""

# ==========================================
# 6. OPTIMIZAR CONFIGURACIÓN DEL SISTEMA
# ==========================================
echo "⚙️ 6. OPTIMIZANDO CONFIGURACIÓN DEL SISTEMA"
echo "------------------------------------"

# Configurar swappiness para mejor rendimiento
CURRENT_SWAPPINESS=$(cat /proc/sys/vm/swappiness)
if [ "$CURRENT_SWAPPINESS" -gt 10 ]; then
    echo "🔧 Optimizando swappiness del sistema..."
    echo "vm.swappiness=10" >> /etc/sysctl.conf
    sysctl -w vm.swappiness=10
    echo "✅ Swappiness configurado a 10 (era $CURRENT_SWAPPINESS)"
else
    echo "✅ Swappiness ya está optimizado ($CURRENT_SWAPPINESS)"
fi

# Aumentar límites de archivos abiertos
echo "🔧 Configurando límites de archivos abiertos..."
if ! grep -q "fs.file-max" /etc/sysctl.conf; then
    echo "fs.file-max=100000" >> /etc/sysctl.conf
    sysctl -w fs.file-max=100000
    echo "✅ Límite de archivos configurado"
else
    echo "✅ Límite de archivos ya configurado"
fi
echo ""

# ==========================================
# 7. CREAR DIRECTORIO TEMPORAL OPTIMIZADO
# ==========================================
echo "📁 7. CONFIGURANDO DIRECTORIO TEMPORAL"
echo "------------------------------------"

# Crear directorio temporal específico para Browsershot
BROWSERSHOT_TMP="/tmp/browsershot"
if [ ! -d "$BROWSERSHOT_TMP" ]; then
    mkdir -p "$BROWSERSHOT_TMP"
    chmod 777 "$BROWSERSHOT_TMP"
    echo "✅ Directorio temporal creado: $BROWSERSHOT_TMP"
else
    echo "✅ Directorio temporal ya existe: $BROWSERSHOT_TMP"
fi

# Configurar permisos
if [ -n "$WEB_USER" ]; then
    chown -R "$WEB_USER:$WEB_USER" "$BROWSERSHOT_TMP" 2>/dev/null || echo "⚠️  No se pudieron cambiar los permisos del directorio temporal"
fi
echo ""

# ==========================================
# 8. TEST DE INSTALACIÓN
# ==========================================
echo "🧪 8. TEST DE INSTALACIÓN"
echo "------------------------------------"

echo "Verificando instalación de Chromium..."
if chromium-browser --headless --disable-gpu --no-sandbox --dump-dom about:blank > /dev/null 2>&1; then
    echo "✅ Test de Chromium exitoso"
else
    echo "❌ Test de Chromium falló"
fi

echo "Verificando Node.js..."
if node -e "console.log('Node.js funciona')" > /dev/null 2>&1; then
    echo "✅ Test de Node.js exitoso"
else
    echo "❌ Test de Node.js falló"
fi
echo ""

# ==========================================
# 9. CONFIGURACIÓN PARA LARAVEL
# ==========================================
echo "🎯 9. CONFIGURACIÓN PARA LARAVEL"
echo "------------------------------------"

# Crear archivo de configuración de ejemplo
ENV_CONFIG_FILE="/tmp/browsershot_env_config.txt"
cat > "$ENV_CONFIG_FILE" << EOF
# Agregar estas líneas a tu archivo .env de Laravel:
BROWSERSHOT_CHROME_PATH=/usr/bin/chromium-browser
BROWSERSHOT_NODE_BINARY=/usr/bin/node
BROWSERSHOT_NPM_BINARY=/usr/bin/npm
BROWSERSHOT_INCLUDE_PATH=/usr/bin:/usr/local/bin
BROWSERSHOT_TEMP_PATH=$BROWSERSHOT_TMP
EOF

echo "📄 Configuración para Laravel guardada en: $ENV_CONFIG_FILE"
echo ""
echo "🔧 Agrega estas líneas a tu archivo .env:"
cat "$ENV_CONFIG_FILE"
echo ""

# ==========================================
# 10. REINICIAR SERVICIOS
# ==========================================
echo "🔄 10. REINICIANDO SERVICIOS"
echo "------------------------------------"

# Detectar y reiniciar servicios web
if systemctl is-active --quiet nginx; then
    echo "🔄 Reiniciando Nginx..."
    systemctl restart nginx
    echo "✅ Nginx reiniciado"
fi

if systemctl is-active --quiet apache2; then
    echo "🔄 Reiniciando Apache2..."
    systemctl restart apache2
    echo "✅ Apache2 reiniciado"
fi

# Reiniciar PHP-FPM si está disponible
for php_version in 8.2 8.1 8.0 7.4; do
    if systemctl is-active --quiet "php${php_version}-fpm"; then
        echo "🔄 Reiniciando PHP${php_version}-FPM..."
        systemctl restart "php${php_version}-fpm"
        echo "✅ PHP${php_version}-FPM reiniciado"
        break
    fi
done
echo ""

# ==========================================
# RESUMEN FINAL
# ==========================================
echo "✅ INSTALACIÓN COMPLETADA"
echo "==========================================="
echo ""
echo "📋 RESUMEN DE LO INSTALADO:"
echo "  • Chromium: $(chromium-browser --version 2>/dev/null || echo 'Error al verificar')"
echo "  • Node.js: $(node --version 2>/dev/null || echo 'Error al verificar')"
echo "  • NPM: $(npm --version 2>/dev/null || echo 'Error al verificar')"
echo ""
echo "🔧 CONFIGURACIONES APLICADAS:"
echo "  • Límites de memlock configurados"
echo "  • Directorio temporal optimizado: $BROWSERSHOT_TMP"
echo "  • Sistema optimizado para Chromium headless"
echo "  • Servicios web reiniciados"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "  1. Agrega la configuración del archivo $ENV_CONFIG_FILE a tu .env"
echo "  2. Ejecuta los scripts de diagnóstico para verificar:"
echo "     ./diagnostico_browsershot.sh"
echo "     php test_laravel_pdf.php"
echo "  3. Prueba la vista previa de PDF en tu aplicación"
echo ""
echo "🎉 ¡Browsershot debería funcionar correctamente ahora!"
echo "==========================================="