# Iconoir Icons - Documentación

## 🎨 Descripción

Iconoir Icons es una implementación del conjunto de iconos Iconoir para Filament 3.x/4.x, proporcionando un conjunto completo de iconos Iconoir que se integran perfectamente con la interfaz de Filament.

## ✨ Características Principales

### 🎯 **Conjunto Completo de Iconos**
- Más de 1,400 iconos disponibles
- Diseño consistente y moderno
- Optimizados para interfaces web

### 🎨 **Dos Estilos Disponibles**
- **Regular** (por defecto) - Iconos con líneas
- **Solid** - Iconos rellenos (conjunto limitado)

### ⚡ **Integración Perfecta**
- Compatible con Filament 3.x y 4.x
- Reemplaza automáticamente los iconos Heroicons
- Sin conflictos con otros paquetes de iconos

### 🔧 **Configuración Flexible**
- Cambio de estilo global
- Override de iconos específicos
- Soporte para alias de Filament

## 📋 Requisitos

### Requisitos del Sistema
- **PHP**: 8.1 o superior
- **Filament**: v3.x o v4.x
- **Laravel**: 9 o superior

### Dependencias
- andreiio/blade-iconoir
- filafly/filament-icons

## 🚀 Instalación

### Paso 1: Instalación via Composer

```bash
composer require filafly/filament-iconoir-icons
```

### Paso 2: Registro del Plugin

Registra el plugin en tu proveedor de panel de Filament (`app/Providers/Filament/AdminPanelProvider.php`):

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filafly\Icons\Iconoir\IconoirIcons;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->plugins([
                IconoirIcons::make(), // Usar estilo regular por defecto
                // ... otros plugins
            ])
            // ... resto de configuración
    }
}
```

## 🎨 Estilos de Iconos

### Estilos Disponibles

Iconoir viene con dos estilos que puedes alternar:

#### **Regular (Por Defecto)**
- Iconos con líneas y contornos
- Conjunto completo de iconos disponible
- Estilo minimalista y limpio

#### **Solid**
- Iconos rellenos y sólidos
- Conjunto limitado de iconos
- Mayor peso visual

### Configuración de Estilos

```php
// Estilo regular (por defecto)
IconoirIcons::make()->regular();

// Estilo sólido
IconoirIcons::make()->solid();
```

## 🔧 Configuración Avanzada

### Override de Iconos Específicos

#### Usando Alias de Iconos

Puedes sobrescribir iconos específicos usando alias de Filament:

```php
// Override de un solo icono
IconoirIcons::make()->overrideStyleForAlias('tables::actions.filter', 'solid');

// Override de múltiples iconos
IconoirIcons::make()->overrideStyleForAlias([
    'tables::actions.filter',
    'actions::delete-action',
    'forms::components.select.placeholder',
], 'solid');
```

#### Usando Nombres de Iconos

También puedes usar los nombres reales de los iconos Iconoir:

```php
// Override de un solo icono
IconoirIcons::make()->overrideStyleForIcon('iconoir-user', 'solid');

// Override de múltiples iconos
IconoirIcons::make()->overrideStyleForIcon([
    'iconoir-arrow-right-circle',
    'iconoir-arrow-left-circle',
    'iconoir-home',
], 'solid');
```

### Configuración Combinada

```php
IconoirIcons::make()
    ->regular() // Estilo base regular
    ->overrideStyleForAlias([
        'tables::actions.filter',
        'actions::delete-action',
    ], 'solid') // Algunos iconos en sólido
    ->overrideStyleForIcon([
        'iconoir-user',
        'iconoir-settings',
    ], 'solid'); // Iconos específicos en sólido
```

## 📚 Iconos Implementados en QPOS

### Navegación Principal

| Recurso | Icono Anterior | Icono Iconoir | Descripción |
|---------|----------------|---------------|-------------|
| **Facturas** | `heroicon-o-document-text` | `iconoir-page` | Gestión de comprobantes |
| **Clientes** | `heroicon-o-users` | `iconoir-user` | Gestión de clientes |
| **Productos** | `heroicon-o-cube` | `iconoir-box` | Catálogo de productos |
| **Categorías** | `heroicon-o-tag` | `iconoir-label` | Categorías de productos |
| **Marcas** | `heroicon-o-building-storefront` | `iconoir-shop` | Marcas de productos |
| **Logs** | `heroicon-o-document-text` | `iconoir-page` | Visor de logs |

### Secciones de Formularios

#### InvoiceResource
- **Datos Básicos**: `iconoir-page`
- **Detalle de Productos**: `iconoir-shopping-bag`
- **Resumen**: `iconoir-calculator`

#### ClientResource
- **Información Básica**: `iconoir-user`
- **Información de Contacto**: `iconoir-phone`
- **Configuración Comercial**: `iconoir-coins`

#### ProductResource
- **Información Básica**: `iconoir-box`
- **Clasificación y Unidades**: `iconoir-label`
- **Precios y Costos**: `iconoir-coins`
- **Configuración Tributaria**: `iconoir-percentage`
- **Inventario y Stock**: `iconoir-package`
- **Estado y Configuración**: `iconoir-settings`

## 🎯 Iconos Populares de Iconoir

### Navegación y UI
```php
'iconoir-home'           // Inicio
'iconoir-menu'           // Menú
'iconoir-search'         // Búsqueda
'iconoir-settings'       // Configuración
'iconoir-user'           // Usuario
'iconoir-users'          // Usuarios múltiples
```

### Acciones
```php
'iconoir-plus'           // Agregar
'iconoir-edit'           // Editar
'iconoir-trash'          // Eliminar
'iconoir-eye'            // Ver
'iconoir-download'       // Descargar
'iconoir-upload'         // Subir
```

### Comercio
```php
'iconoir-page'           // Página/Documento
'iconoir-shopping-bag'   // Bolsa de compras
'iconoir-shopping-cart'  // Carrito
'iconoir-coins'          // Monedas
'iconoir-credit-card'    // Tarjeta de crédito
'iconoir-receipt'        // Recibo
```

### Datos y Archivos
```php
'iconoir-page'           // Página
'iconoir-folder'         // Carpeta
'iconoir-file'           // Archivo
'iconoir-database'       // Base de datos
'iconoir-chart'          // Gráfico
'iconoir-stats'          // Estadísticas
```

### Comunicación
```php
'iconoir-phone'          // Teléfono
'iconoir-mail'           // Correo
'iconoir-message'        // Mensaje
'iconoir-chat'           // Chat
'iconoir-notification'   // Notificación
```

## 🔍 Búsqueda de Iconos

### Recursos Oficiales

- **Sitio Web**: [iconoir.com](https://iconoir.com)
- **GitHub**: [iconoir-icons/iconoir](https://github.com/iconoir-icons/iconoir)
- **Figma**: Plugin oficial de Iconoir
- **NPM**: [@iconoir/icons](https://www.npmjs.com/package/@iconoir/icons)

### Herramientas de Búsqueda

1. **Navegador Web**: Visita iconoir.com para explorar todos los iconos
2. **Búsqueda por Categoría**: Los iconos están organizados por categorías
3. **Búsqueda por Palabra Clave**: Usa el buscador del sitio oficial

## 🛠️ Uso en Recursos de Filament

### En Resources

```php
class ProductResource extends Resource
{
    protected static ?string $navigationIcon = 'iconoir-box';
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Información Básica')
                ->icon('iconoir-box')
                ->schema([
                    // campos del formulario
                ]),
        ]);
    }
}
```

### En Actions

```php
Action::make('export')
    ->icon('iconoir-download')
    ->action(fn () => $this->export()),

Action::make('import')
    ->icon('iconoir-upload')
    ->action(fn () => $this->import()),
```

### En Navigation

```php
NavigationItem::make('Dashboard')
    ->icon('iconoir-home')
    ->url('/admin'),

NavigationItem::make('Reports')
    ->icon('iconoir-stats')
    ->url('/admin/reports'),
```

## 🎨 Personalización

### CSS Personalizado

Puedes personalizar la apariencia de los iconos con CSS:

```css
/* Cambiar color de iconos específicos */
.iconoir-user {
    color: #3B82F6;
}

/* Cambiar tamaño */
.iconoir-settings {
    width: 20px;
    height: 20px;
}

/* Efectos hover */
.iconoir-trash:hover {
    color: #EF4444;
}
```

### Clases CSS Disponibles

Los iconos Iconoir usan clases CSS con el prefijo `iconoir-`:

```html
<svg class="iconoir-user">...</svg>
<svg class="iconoir-settings">...</svg>
<svg class="iconoir-home">...</svg>
```

## 🔧 Troubleshooting

### Problemas Comunes

#### Los iconos no aparecen
1. Verifica que el plugin esté registrado correctamente
2. Limpia la caché: `php artisan config:clear`
3. Asegúrate de usar nombres de iconos válidos

#### Iconos no encontrados
1. Verifica el nombre del icono en [iconoir.com](https://iconoir.com)
2. Asegúrate de usar el prefijo `iconoir-`
3. Algunos iconos solo están disponibles en estilo regular

#### Conflictos con otros paquetes de iconos
1. Asegúrate de que IconoirIcons esté registrado antes que otros paquetes de iconos
2. Usa overrides específicos si es necesario

### Comandos de Diagnóstico

```bash
# Verificar instalación
composer show filafly/filament-iconoir-icons

# Limpiar caché
php artisan config:clear
php artisan view:clear

# Verificar assets
php artisan vendor:publish --tag=laravel-assets
```

## 📈 Rendimiento

### Optimización

1. **Lazy Loading**: Los iconos se cargan solo cuando se necesitan
2. **SVG Inline**: Los iconos se insertan como SVG inline para mejor rendimiento
3. **Cache**: Los iconos se cachean automáticamente

### Mejores Prácticas

1. **Usa nombres consistentes** para iconos similares
2. **Evita overrides innecesarios** para mantener consistencia
3. **Prefiere el estilo regular** para mejor disponibilidad de iconos

## 🔄 Migración desde Heroicons

### Mapeo de Iconos Comunes

| Heroicon | Iconoir | Descripción |
|----------|---------|-------------|
| `heroicon-o-home` | `iconoir-home` | Inicio |
| `heroicon-o-user` | `iconoir-user` | Usuario |
| `heroicon-o-users` | `iconoir-users` | Usuarios |
| `heroicon-o-cog-6-tooth` | `iconoir-settings` | Configuración |
| `heroicon-o-document-text` | `iconoir-page` | Documento |
| `heroicon-o-archive-box` | `iconoir-package` | Paquete/Caja |
| `heroicon-o-shopping-cart` | `iconoir-cart` | Carrito |
| `heroicon-o-shopping-bag` | `iconoir-shopping-bag` | Compras |
| `heroicon-o-cube` | `iconoir-box` | Caja/Producto |
| `heroicon-o-tag` | `iconoir-label` | Etiqueta |
| `heroicon-o-phone` | `iconoir-phone` | Teléfono |
| `heroicon-o-banknotes` | `iconoir-coins` | Dinero |

### Script de Migración

```bash
# Buscar y reemplazar en archivos PHP
find app/ -name "*.php" -exec sed -i 's/heroicon-o-home/iconoir-home/g' {} \;
find app/ -name "*.php" -exec sed -i 's/heroicon-o-user/iconoir-user/g' {} \;
find app/ -name "*.php" -exec sed -i 's/heroicon-o-settings/iconoir-settings/g' {} \;
```

## 📄 Licencia

Iconoir Icons es software de código abierto licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).

## 🏆 Créditos

### Desarrollado por Filafly

**Filafly** es un equipo dedicado a crear herramientas y temas de alta calidad para el ecosistema de Filament PHP.

### Iconos por Iconoir

**Iconoir** es un conjunto de iconos de código abierto creado por la comunidad, diseñado para ser simple, consistente y hermoso.

---

**Integrado en**: Sistema QPOS  
**Versión**: v2.0.0  
**Fecha de Instalación**: Enero 2025  
**Estado**: ✅ Activo como Conjunto de Iconos por Defecto