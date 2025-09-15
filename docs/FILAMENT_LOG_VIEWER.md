# Filament Log Viewer - Documentación

## 📋 Descripción

Filament Log Viewer es un plugin para Filament que permite leer y mostrar los archivos de log de Laravel en una tabla limpia y buscable con stack traces y filtrado avanzado.

## 🚀 Instalación

### Requisitos
- Laravel 10+
- Filament v4
- PHP 8.1+

### Instalación via Composer

```bash
composer require achyutn/filament-log-viewer
```

### Registro del Plugin

El plugin ya está registrado en el panel de administración en `app/Providers/Filament/AdminPanelProvider.php`:

```php
use AchyutN\FilamentLogViewer\FilamentLogViewer;

return $panel
    ->plugins([
        FilamentLogViewer::make()
            ->authorize(fn () => auth()->check())
            ->navigationGroup(__('Sistema'))
            ->navigationIcon('heroicon-o-document-text')
            ->navigationLabel(__('Visor de Logs'))
            ->navigationSort(100)
            ->pollingTime(null), // Desactivar auto-refresh
    ]);
```

## 📖 Uso

### Acceso al Visor de Logs

Después de la instalación, visita `/admin/logs` en tu panel de Filament. Verás una tabla con las entradas de log.

### Navegación

El plugin aparece en el menú de navegación bajo el grupo **"Sistema"** con el nombre **"Visor de Logs"**.

## 🎯 Características

### Columnas de la Tabla

| Columna | Descripción | Toggleable |
|---------|-------------|------------|
| **Log Level** | Badge con color mapeado desde el nivel de log | No |
| **Environment** | Entorno de la aplicación (local, production, etc.) | Sí |
| **File** | Nombre del archivo de log (ej: laravel.log) | Sí |
| **Message** | Resumen corto del log | No |
| **Occurred** | Fecha/hora legible | No |

### Niveles de Log Soportados

- 🔴 **Emergency** - Emergencias del sistema
- 🟠 **Alert** - Alertas que requieren acción inmediata
- 🔴 **Critical** - Condiciones críticas
- 🟠 **Error** - Errores de tiempo de ejecución
- 🟡 **Warning** - Advertencias excepcionales
- 🔵 **Notice** - Eventos normales pero significativos
- 🟢 **Info** - Mensajes informativos
- ⚪ **Debug** - Información detallada de debug

### Stack Traces

Haz clic en la acción **"Ver"** para inspeccionar stack traces detallados de los errores.

### Vista Previa de Correos

Si tus logs contienen mensajes de correo, puedes previsualizarlos directamente desde la tabla. La pestaña **"Mail"** es visible solo si hay correos presentes.

## 🔍 Filtros

### Filtros por Nivel de Log

Los filtros están disponibles como pestañas encima de la tabla, permitiendo filtrar por:

- Todos los niveles
- Emergency
- Alert
- Critical
- Error
- Warning
- Notice
- Info
- Debug

### Filtro por Fecha

Puedes filtrar logs por fecha usando el selector de fecha en la esquina superior derecha de la tabla. Esto permite seleccionar un rango de fechas específico.

### Columnas Toggleables

Puedes alternar la visibilidad de las columnas **Environment** y **File** haciendo clic en el ícono del ojo en la esquina superior derecha de la tabla.

## ⚙️ Configuración Avanzada

### Personalización Completa

```php
use AchyutN\FilamentLogViewer\FilamentLogViewer;

FilamentLogViewer::make()
    ->authorize(fn () => auth()->check()) // Autorización personalizada
    ->navigationGroup('Sistema') // Grupo de navegación
    ->navigationIcon('heroicon-o-document-text') // Icono personalizado
    ->navigationLabel('Visor de Logs') // Etiqueta personalizada
    ->navigationSort(100) // Orden en el menú
    ->navigationUrl('/logs') // URL personalizada
    ->pollingTime(null); // Tiempo de polling (null = desactivado)
```

### Autorización

Por defecto, el plugin requiere que el usuario esté autenticado. Puedes personalizar la autorización:

```php
FilamentLogViewer::make()
    ->authorize(fn () => auth()->user()?->hasRole('admin'))
```

### Polling (Auto-refresh)

El auto-refresh está desactivado por defecto. Para habilitarlo:

```php
FilamentLogViewer::make()
    ->pollingTime('5s') // Actualizar cada 5 segundos
```

## 🛠️ Configuración del Sistema

### Configuración de Logs de Laravel

Asegúrate de que tu aplicación esté configurada para generar logs. En `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'ignore_exceptions' => false,
    ],
    
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
],
```

### Permisos de Archivos

Asegúrate de que el directorio `storage/logs` tenga los permisos correctos:

```bash
chmod -R 755 storage/logs
```

## 📊 Ejemplos de Uso

### Generar Logs de Prueba

```php
// En tinker o en tu código
Log::info('Sistema iniciado correctamente');
Log::warning('Advertencia de prueba');
Log::error('Error de prueba para demostración');
Log::debug('Información de debug');
```

### Logs con Contexto

```php
Log::info('Usuario logueado', [
    'user_id' => auth()->id(),
    'ip' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);
```

### Logs de Errores con Stack Trace

```php
try {
    // Código que puede fallar
    throw new Exception('Error de prueba');
} catch (Exception $e) {
    Log::error('Error capturado', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);
}
```

## 🔧 Troubleshooting

### Problemas Comunes

#### No se muestran logs
1. Verifica que existan archivos de log en `storage/logs/`
2. Comprueba los permisos del directorio
3. Asegúrate de que la configuración de logging esté correcta

#### Error de permisos
```bash
sudo chown -R www-data:www-data storage/logs
chmod -R 755 storage/logs
```

#### Plugin no aparece en el menú
1. Limpia la caché: `php artisan config:clear`
2. Verifica que el plugin esté registrado correctamente
3. Comprueba la autorización del usuario

### Logs de Debug

Para habilitar logs de debug más detallados:

```env
LOG_LEVEL=debug
APP_DEBUG=true
```

## 📈 Rendimiento

### Optimización para Archivos Grandes

Para aplicaciones con muchos logs:

1. **Rotación de logs**: Configura la rotación automática
2. **Límites de memoria**: Ajusta `memory_limit` si es necesario
3. **Paginación**: El plugin maneja automáticamente la paginación

### Configuración de Rotación

En `config/logging.php`:

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14, // Mantener logs por 14 días
],
```

## 🔒 Seguridad

### Consideraciones de Seguridad

1. **Autorización**: Siempre implementa autorización adecuada
2. **Información sensible**: Evita loggear datos sensibles
3. **Acceso restringido**: Limita el acceso solo a administradores

### Ejemplo de Autorización Avanzada

```php
FilamentLogViewer::make()
    ->authorize(function () {
        return auth()->user()?->can('view-logs') && 
               auth()->user()?->hasRole(['admin', 'developer']);
    })
```

## 📝 Compatibilidad

| Versión Plugin | Versión Filament |
|----------------|------------------|
| ^1.x           | Filament v4      |
| ^0.x           | Filament v3      |

## 🤝 Soporte

### Recursos Adicionales

- **Repositorio**: [achyutn/filament-log-viewer](https://github.com/achyutn/filament-log-viewer)
- **Documentación Oficial**: [Filament Log Viewer Docs](https://github.com/achyutn/filament-log-viewer#readme)
- **Issues**: [GitHub Issues](https://github.com/achyutn/filament-log-viewer/issues)

### Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Crea un fork del repositorio
2. Crea una rama para tu feature
3. Envía un pull request con descripción detallada

## 📄 Licencia

Este paquete es software de código abierto licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).

---

**Desarrollado por**: [Achyut Neupane](https://github.com/achyutn)  
**Integrado en**: Sistema QPOS  
**Fecha**: Enero 2025