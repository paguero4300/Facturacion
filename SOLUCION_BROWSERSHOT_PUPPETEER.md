# 🎉 Solución del Problema de Browsershot con Puppeteer

**Fecha:** 21 de Septiembre, 2025
**Estado:** ✅ RESUELTO
**Duración del problema:** Múltiples intentos hasta encontrar la causa raíz

## 🔍 Análisis del Problema

### Error Original
```
Error: Failed to launch the browser process: Code: 1
stderr: cannot set memlock limit to 524288:524288: Operation not permitted
```

### Diagnóstico Inicial (Incorrecto)
Inicialmente pensamos que el problema era:
- ❌ Límites de memlock del sistema
- ❌ Argumentos de Chromium incorrectos
- ❌ Problemas de FrankenPHP específicos
- ❌ Configuración de `/etc/security/limits.conf`

## 🎯 La Causa Raíz Real

### Estructura del Proyecto
```json
// package.json
{
  "dependencies": {
    "puppeteer": "^24.19.0"
  }
}

// composer.json
{
  "require": {
    "spatie/laravel-pdf": "^1.7"
  }
}
```

### Flujo de Ejecución Real
```
Usuario → MediaAction (Filament) → InvoiceResource.php:1012
→ route('invoices.pdf.view')
→ InvoicePdfController::view()
→ pdf()->view()->withBrowsershot()
→ spatie/laravel-pdf → spatie/browsershot
→ Puppeteer 24.19.0 → Chromium (descargado por Puppeteer)
```

### El Problema Real Era Triple:

#### 1. **Puppeteer Moderno Descarga Su Propio Chromium**
- **Puppeteer 24.19.0** NO usa `/usr/bin/chromium-browser`
- Descarga automáticamente su propia versión en `/root/.cache/puppeteer/`
- Versiones encontradas: `linux-140.0.7339.80` y `linux-140.0.7339.82`

#### 2. **Problema de Permisos de Acceso**
```bash
# El cache estaba aquí (inaccesible para www-data):
/root/.cache/puppeteer/chrome/
├── linux-140.0.7339.80/
└── linux-140.0.7339.82/

# www-data tiene home en:
/var/www/ (no /home/www-data/)

# www-data NO podía acceder a /root/
```

#### 3. **Configuración Forzaba Chromium del Sistema**
```php
// config/laravel-pdf.php:59 (PROBLEMÁTICO)
'chrome_path' => env('BROWSERSHOT_CHROME_PATH', '/usr/bin/chromium-browser'),
```

## 🔧 La Solución Final

### Paso 1: Verificar la Estructura Real
```bash
# Descubrir dónde está Puppeteer
find /var/www/facturacion -name "*chrome*" -type d 2>/dev/null

# Encontrar el cache real
ls -la /root/.cache/puppeteer/chrome/
# Resultado: linux-140.0.7339.80, linux-140.0.7339.82

# Verificar home de www-data
getent passwd www-data
# Resultado: www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin
```

### Paso 2: Copiar Cache a Ubicación Accesible
```bash
# Crear directorio cache para www-data
mkdir -p /var/www/.cache

# Copiar cache de Puppeteer
cp -r /root/.cache/puppeteer /var/www/.cache/

# Cambiar propietario
chown -R www-data:www-data /var/www/.cache

# Verificar resultado
ls -la /var/www/.cache/puppeteer/chrome/
```

### Paso 3: Remover Configuración Forzada
```php
// ANTES (config/laravel-pdf.php)
'chrome_path' => env('BROWSERSHOT_CHROME_PATH', '/usr/bin/chromium-browser'),

// DESPUÉS
'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),
```

### Paso 4: Limpiar Cachés
```bash
php artisan config:clear
php artisan config:cache
systemctl restart frankenphp
```

## ✅ Resultado

### Antes (FALLA)
```json
{
  "executablePath": "/usr/bin/chromium-browser",
  "error": "cannot set memlock limit to 524288:524288: Operation not permitted"
}
```

### Después (ÉXITO)
```json
{
  "executablePath": "/var/www/.cache/puppeteer/chrome/linux-140.0.7339.82/chrome",
  "status": "success"
}
```

## 🧠 Lecciones Aprendidas

### 1. **Puppeteer Moderno Es Diferente**
- **NO usar** configuraciones de Chrome del sistema
- Puppeteer descarga y maneja su propia versión de Chromium
- La versión descargada está optimizada para las necesidades de Puppeteer

### 2. **Permisos Son Críticos**
- El usuario web debe tener acceso al cache de Puppeteer
- El directorio home real puede ser diferente al esperado
- Verificar siempre con `getent passwd usuario`

### 3. **Configuración en Cascada**
- Laravel PDF → Browsershot → Puppeteer
- Cada capa puede tener configuraciones que anulen las anteriores
- Verificar TODAS las configuraciones en la cadena

### 4. **Debugging Sistemático**
- Usar scripts de diagnóstico completo
- Verificar cada paso del flujo
- No asumir, siempre verificar con comandos

## 📋 Checklist para Futuros Problemas Similares

### Verificaciones Iniciales
- [ ] ¿Qué versión de Puppeteer se está usando?
- [ ] ¿Dónde está el cache de Puppeteer?
- [ ] ¿Cuál es el usuario web real y su directorio home?
- [ ] ¿Hay configuraciones forzadas de `executablePath`?

### Comandos de Diagnóstico
```bash
# Ver versión de Puppeteer
cat package.json | grep puppeteer

# Encontrar cache de Puppeteer
find / -name ".cache" -type d 2>/dev/null | grep puppeteer

# Ver usuario web
ps aux | grep -E "(nginx|apache|frankenphp)" | head -1

# Ver configuraciones forzadas
grep -r "chromium-browser\|chrome_path" config/ app/
```

### Solución Estándar
1. **Copiar cache de Puppeteer** al directorio accesible por el usuario web
2. **Remover configuraciones forzadas** de `executablePath`
3. **Limpiar cachés** de Laravel
4. **Reiniciar servidor web**

## 🔗 Archivos Modificados

### `/var/www/facturacion/config/laravel-pdf.php`
```php
// Línea 59 - REMOVIDO el default
'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),
```

### `/var/www/facturacion/app/Http/Controllers/InvoicePdfController.php`
```php
// Removido setChromePath() de todas las funciones withBrowsershot()
// Dejando que Puppeteer use automáticamente su propio Chromium
```

### Estructura de Cache Creada
```
/var/www/.cache/puppeteer/chrome/
├── linux-140.0.7339.80/
│   └── chrome
└── linux-140.0.7339.82/
    └── chrome
```

## 🚀 Verificación de Funcionamiento

### Test Manual
1. Ir a admin panel → Invoices
2. Seleccionar cualquier factura
3. Hacer clic en "Vista Previa A4"
4. ✅ El PDF debe abrir sin errores

### Test Programático
```php
// En una ruta de test
use function Spatie\LaravelPdf\Support\pdf;

$pdf = pdf()->html('<h1>Test</h1>')->format('A4');
return $pdf->download(); // Debe funcionar sin errores
```

---

**Documentado por:** Claude Code
**Proyecto:** Sistema de Facturación Laravel
**Tecnologías:** Laravel 12, Filament 4, Spatie Laravel PDF, Browsershot, Puppeteer 24.19.0, FrankenPHP
**Estado:** ✅ RESUELTO DEFINITIVAMENTE