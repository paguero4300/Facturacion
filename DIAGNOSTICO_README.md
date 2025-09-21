# 🔍 Scripts de Diagnóstico para Browsershot

Este conjunto de scripts te ayudará a diagnosticar y resolver problemas con la generación de PDFs usando Browsershot y Laravel PDF.

## 📁 Archivos Incluidos

- `diagnostico_browsershot.sh` - Script de diagnóstico del sistema completo
- `test_laravel_pdf.php` - Test específico de Laravel PDF
- `DIAGNOSTICO_README.md` - Este archivo

## 🚀 Uso

### 1. Script de Diagnóstico del Sistema

```bash
# Hacer el script ejecutable
chmod +x diagnostico_browsershot.sh

# Ejecutar diagnóstico completo
./diagnostico_browsershot.sh

# Guardar salida en archivo
./diagnostico_browsershot.sh > diagnostico_resultado.txt 2>&1
```

### 2. Test de Laravel PDF

```bash
# Ejecutar desde el directorio del proyecto
php test_laravel_pdf.php

# Guardar salida
php test_laravel_pdf.php > test_laravel_resultado.txt 2>&1
```

## 🔧 Qué Diagnostican los Scripts

### Script Bash (`diagnostico_browsershot.sh`):
- ✅ Información del sistema operativo
- ✅ Verificación de Node.js y NPM
- ✅ Búsqueda de Chrome/Chromium en ubicaciones comunes
- ✅ Verificación de permisos y límites del sistema
- ✅ Test de ejecución de Chromium con diferentes argumentos
- ✅ Verificación de variables de entorno
- ✅ Test directo de Browsershot
- ✅ Recomendaciones específicas

### Script PHP (`test_laravel_pdf.php`):
- ✅ Verificación de paquetes Laravel instalados
- ✅ Configuración de Laravel PDF
- ✅ Test de generación de PDF simple
- ✅ Test con argumentos anti-memlock
- ✅ Información del entorno PHP
- ✅ Recomendaciones de configuración

## 🎯 Problemas Comunes y Soluciones

### Error: "cannot set memlock limit to 524288:524288: Operation not permitted"

**Causa**: Límites de memoria bloqueada demasiado restrictivos.

**Solución**:
```bash
# Aumentar límites temporalmente
ulimit -l unlimited

# Configuración permanente en /etc/security/limits.conf
echo "www-data soft memlock unlimited" | sudo tee -a /etc/security/limits.conf
echo "www-data hard memlock unlimited" | sudo tee -a /etc/security/limits.conf

# Reiniciar servicios web
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

### Error: "Chrome/Chromium not found"

**Solución**:
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y chromium-browser

# CentOS/RHEL
sudo yum install -y chromium

# Verificar instalación
chromium-browser --version
```

### Error: "Node.js not found"

**Solución**:
```bash
# Instalar Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Verificar instalación
node --version
npm --version
```

## 📋 Checklist de Verificación

Antes de ejecutar los scripts, verifica:

- [ ] Estás en el directorio correcto del proyecto Laravel
- [ ] Tienes permisos para ejecutar scripts
- [ ] El servidor web está funcionando
- [ ] Tienes acceso a logs del sistema

## 📊 Interpretando los Resultados

### ✅ Signos Positivos:
- Todos los binarios encontrados y ejecutables
- Tests de PDF exitosos
- Límites de memoria apropiados
- Variables de entorno configuradas

### ❌ Signos de Problema:
- Binarios no encontrados o no ejecutables
- Tests de PDF fallan
- Límites de memoria restrictivos
- Variables de entorno no definidas

### ⚠️ Advertencias:
- Algunos tests pasan pero con limitaciones
- Configuración subóptima
- Dependencias faltantes no críticas

## 🔗 Recursos Adicionales

- [Documentación de Browsershot](https://github.com/spatie/browsershot)
- [Laravel PDF Documentation](https://github.com/spatie/laravel-pdf)
- [Puppeteer Troubleshooting](https://pptr.dev/troubleshooting)
- [Chrome Arguments List](https://peter.sh/experiments/chromium-command-line-switches/)

## 🆘 Si Nada Funciona

1. **Ejecuta ambos scripts** y guarda los resultados
2. **Revisa los logs** de Laravel y del servidor web
3. **Prueba manualmente** Chrome/Chromium desde la línea de comandos
4. **Considera usar Docker** para aislar el ambiente de Chrome
5. **Contacta soporte** con los resultados de los diagnósticos

---

*Estos scripts han sido diseñados para ser comprehensivos pero seguros. No modifican configuraciones del sistema, solo diagnostican.*