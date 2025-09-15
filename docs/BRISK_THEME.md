# Brisk Theme - Documentación

## 🎨 Descripción

Brisk es un tema gratuito, moderno y amigable para paneles de administración de Filament PHP. Combina simplicidad con una estética acogedora, diseñado cuidadosamente para embellecer tu aplicación mientras preserva todas las características y capacidades robustas de Filament.

## ✨ Características Principales

### 🆓 **Gratuito y Código Abierto**
- Sin tarifas de licencia ni restricciones
- Código completamente abierto y modificable
- Comunidad activa de desarrollo

### 🎯 **Diseño Moderno**
- Interfaz limpia y minimalista
- Elementos visuales contemporáneos
- Experiencia de usuario mejorada

### 🌓 **Soporte Completo Light & Dark Mode**
- Modo claro para trabajo diurno
- Modo oscuro para trabajo nocturno
- Transición suave entre modos
- Preferencias del sistema respetadas

### 📱 **Layout Responsivo**
- Funciona perfectamente en todos los tamaños de dispositivo
- Optimizado para móviles, tablets y escritorio
- Navegación adaptativa

### 🔤 **Tipografía Kumbh Sans**
- Fuente limpia y legible
- Optimizada para interfaces de administración
- Excelente legibilidad en todas las resoluciones

### ⚡ **Integración Fácil**
- Configuración simple y rápida
- Compatible con todas las características de Filament
- Sin conflictos con plugins existentes

## 📋 Requisitos

### Requisitos del Sistema
- **PHP**: 8.2 o superior
- **Filament**: v4
- **Tailwind CSS**: v4
- **Laravel**: 10 o superior
- **Node.js**: 18 o superior (para compilación)

### Dependencias
- Laravel Vite Plugin
- Tailwind CSS Vite Plugin

## 🚀 Instalación

### Paso 1: Instalación via Composer

```bash
composer require filafly/brisk
```

### Paso 2: Crear Tema Personalizado

Para asegurar que Filament reconozca los cambios de estilo, debemos configurar un nuevo tema personalizado:

```bash
php artisan make:filament-theme
```

### Paso 3: Configurar Importación del Tema

Una vez creado el tema, reemplaza la importación existente en `resources/css/filament/admin/theme.css`:

```css
@import '../../../../vendor/filafly/brisk/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';
```

### Paso 4: Configurar Vite

Agrega el archivo de tema al array `input` en `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css'  // ← Agregar esta línea
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

### Paso 5: Configurar Panel Provider

En tu proveedor de panel (ej: `app/Providers/Filament/AdminPanelProvider.php`):

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filafly\Themes\Brisk\BriskTheme;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber, // O tu color preferido
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->plugins([
                BriskTheme::make(),
                // ... otros plugins
            ])
            // ... resto de configuración
    }
}
```

### Paso 6: Compilar Assets

```bash
npm run build
```

### Paso 7: Limpiar Caché

```bash
php artisan config:clear
php artisan view:clear
```

## 🎨 Personalización

### Colores Primarios

Puedes personalizar los colores del tema en el panel provider:

```php
use Filament\Support\Colors\Color;

return $panel
    ->colors([
        'primary' => Color::Amber,     // Amarillo (por defecto)
        'primary' => Color::Blue,      // Azul
        'primary' => Color::Green,     // Verde
        'primary' => Color::Purple,    // Morado
        'primary' => Color::Red,       // Rojo
        'primary' => Color::Orange,    // Naranja
        // O colores personalizados
        'primary' => [
            50 => '255, 251, 235',
            100 => '254, 243, 199',
            // ... resto de tonos
        ],
    ]);
```

### Personalización Avanzada

Para personalizar aún más el tema, puedes agregar CSS personalizado en `resources/css/filament/admin/theme.css`:

```css
@import '../../../../vendor/filafly/brisk/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';

/* Personalizaciones adicionales */
.fi-sidebar {
    /* Personalizar sidebar */
}

.fi-topbar {
    /* Personalizar topbar */
}

/* Modo oscuro personalizado */
.dark .fi-sidebar {
    /* Personalizar sidebar en modo oscuro */
}
```

## 🌓 Modo Oscuro

### Activación Automática

Brisk respeta automáticamente las preferencias del sistema del usuario. Los usuarios pueden alternar entre modo claro y oscuro usando el switcher en la interfaz.

### Configuración Manual

Para forzar un modo específico:

```php
// En tu panel provider
return $panel
    ->darkMode(true)  // Forzar modo oscuro
    ->darkMode(false) // Forzar modo claro
    // Sin especificar = automático (recomendado)
```

## 📱 Responsividad

### Breakpoints Soportados

- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px
- **Large Desktop**: > 1280px

### Características Responsivas

- **Navegación colapsible** en dispositivos móviles
- **Sidebar adaptativo** que se oculta/muestra según el tamaño
- **Tablas responsivas** con scroll horizontal
- **Formularios optimizados** para touch
- **Modales adaptados** a pantallas pequeñas

## 🔧 Troubleshooting

### Problemas Comunes

#### El tema no se aplica
1. Verifica que el archivo CSS esté compilado: `npm run build`
2. Limpia la caché: `php artisan config:clear && php artisan view:clear`
3. Verifica que la ruta del tema sea correcta en el panel provider

#### Errores de compilación
1. Asegúrate de que Node.js esté actualizado (v18+)
2. Reinstala dependencias: `npm install`
3. Verifica que vite.config.js tenga la configuración correcta

#### Conflictos con otros temas
1. Asegúrate de importar solo el tema Brisk
2. Elimina importaciones de otros temas
3. Verifica el orden de los plugins en el panel provider

### Comandos de Diagnóstico

```bash
# Verificar instalación de Brisk
composer show filafly/brisk

# Verificar compilación de assets
npm run build

# Limpiar todas las cachés
php artisan optimize:clear

# Verificar configuración de Vite
npm run dev
```

## 🎯 Mejores Prácticas

### Desarrollo

1. **Usa modo desarrollo** durante el desarrollo:
   ```bash
   npm run dev
   ```

2. **Compila para producción** antes de desplegar:
   ```bash
   npm run build
   ```

3. **Mantén separadas las personalizaciones** en archivos CSS adicionales

### Rendimiento

1. **Compila assets** en producción para mejor rendimiento
2. **Usa CDN** para assets estáticos si es posible
3. **Optimiza imágenes** y recursos personalizados

### Mantenimiento

1. **Actualiza regularmente** el tema Brisk
2. **Revisa changelog** antes de actualizar
3. **Prueba en staging** antes de producción

## 🔄 Actualizaciones

### Verificar Versión Actual

```bash
composer show filafly/brisk
```

### Actualizar a la Última Versión

```bash
composer update filafly/brisk
npm run build
php artisan config:clear
```

### Changelog

Consulta el [changelog oficial](https://github.com/filafly/brisk/releases) para ver las últimas mejoras y correcciones.

## 🤝 Soporte y Comunidad

### Recursos Oficiales

- **Repositorio**: [filafly/brisk](https://github.com/filafly/brisk)
- **Documentación**: [Brisk Theme Docs](https://github.com/filafly/brisk#readme)
- **Issues**: [GitHub Issues](https://github.com/filafly/brisk/issues)

### Comunidad

- **Discord**: Únete al servidor de Filament
- **Twitter**: Sigue [@filafly](https://twitter.com/filafly)
- **Discussions**: Participa en GitHub Discussions

### Contribuciones

Las contribuciones son bienvenidas:

1. Fork el repositorio
2. Crea una rama para tu feature
3. Envía un pull request con descripción detallada
4. Sigue las guías de contribución del proyecto

## 📄 Licencia

Brisk Theme es software de código abierto licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).

## 🏆 Créditos

### Desarrollado por Filafly

**Filafly** es un equipo dedicado a crear herramientas y temas de alta calidad para el ecosistema de Filament PHP.

### Tecnologías Utilizadas

- **Filament PHP** - Framework de administración
- **Tailwind CSS** - Framework de CSS
- **Kumbh Sans** - Tipografía
- **Laravel Vite** - Build tool

---

**Integrado en**: Sistema QPOS  
**Versión**: v1.0.1  
**Fecha de Instalación**: Enero 2025  
**Estado**: ✅ Activo y Funcionando