# Sistema de Categorías Dinámicas

## Descripción General

El sistema de categorías ahora soporta una estructura jerárquica completamente dinámica que permite:
- Crear **categorías principales** (sin padre)
- Crear **subcategorías** (con categoría padre)
- Generar el menú de navegación **automáticamente** desde la base de datos
- Ordenar las categorías según preferencias
- Activar/desactivar categorías sin eliminarlas

## Estructura de la Base de Datos

### Campos Nuevos en `categories`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `parent_id` | foreignId | ID de la categoría padre (NULL para categorías principales) |
| `slug` | string(150) | URL amigable (ej: "ocasiones", "peluches") |
| `order` | integer | Orden de aparición en el menú (menor = primero) |

## Cómo Usar en Filament

### 1. Crear Categoría Principal

1. Ir a **Gestión Comercial > Categorías**
2. Clic en **Nuevo**
3. Llenar los campos:
   - **Categoría Principal**: Dejar vacío
   - **Nombre**: Ej: "Ocasiones"
   - **Slug**: Se genera automáticamente (ej: "ocasiones")
   - **Orden**: 10 (menor número = aparece primero)
   - **Color, Icono, Descripción**: Opcionales
   - **Estado**: Activo
4. Guardar

### 2. Crear Subcategoría

1. Ir a **Gestión Comercial > Categorías**
2. Clic en **Nuevo**
3. Llenar los campos:
   - **Categoría Principal**: Seleccionar "Ocasiones"
   - **Nombre**: Ej: "Amor"
   - **Slug**: Se genera automáticamente (ej: "amor")
   - **Orden**: 10
4. Guardar

### 3. Ordenar Categorías

- El campo **Orden** determina la posición en el menú
- Números menores aparecen primero
- Ejemplo:
  - Orden 10: Ocasiones
  - Orden 20: Arreglos
  - Orden 30: Regalos
  - Orden 40: Festivos

### 4. Desactivar Categoría

- Cambiar el toggle **Categoría Activa** a OFF
- La categoría desaparecerá automáticamente del menú web
- Los productos asociados NO se eliminan

## Cómo Funciona el Menú Web

### Generación Automática

El menú en `header.blade.php` se genera automáticamente:

```blade
@foreach($menuCategories as $category)
    <li class="relative group">
        <a href="{{ url('/' . $category->slug) }}">
            {{ strtoupper($category->name) }}
        </a>
        
        @if($category->activeChildren->count() > 0)
            <div class="dropdown">
                @foreach($category->activeChildren as $subcategory)
                    <a href="{{ url('/' . $subcategory->slug) }}">
                        {{ $subcategory->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </li>
@endforeach
```

### URLs Generadas

Las categorías generan URLs automáticamente:
- Categoría principal: `http://facturacion.test/ocasiones`
- Subcategoría: `http://facturacion.test/amor`

## Ejemplo de Estructura

```
INICIO
NOSOTROS
OCASIONES (Categoría Principal - Order: 10)
  ├── Amor (Subcategoría - Order: 10)
  ├── Aniversario (Subcategoría - Order: 20)
  ├── Cumpleaños (Subcategoría - Order: 30)
  └── Graduación (Subcategoría - Order: 40)
ARREGLOS (Categoría Principal - Order: 20)
  ├── Rosas (Subcategoría - Order: 10)
  ├── Girasoles (Subcategoría - Order: 20)
  └── Flores Mixtas (Subcategoría - Order: 30)
REGALOS (Categoría Principal - Order: 30)
  └── Peluches (Subcategoría - Order: 10)
```

## Archivos Modificados

### Migración
- `database/migrations/2025_09_30_084600_add_hierarchy_fields_to_categories_table.php`

### Modelo
- `app/Models/Category.php`
  - Relaciones: `parent()`, `children()`, `activeChildren()`
  - Scopes: `parents()`, `children()`
  - Métodos: `isParent()`, `hasChildren()`

### Filament Resource
- `app/Filament/Resources/CategoryResource.php`
  - Campo `parent_id` para seleccionar categoría padre
  - Campo `slug` generado automáticamente
  - Campo `order` para ordenamiento

### Vistas
- `resources/views/partials/header.blade.php` - Menú dinámico

### Controlador
- `app/Http/Controllers/DetallesController.php`
  - Método `showCategory()` para mostrar categoría con productos

### Rutas
- `routes/web.php`
  - Ruta dinámica: `/{categorySlug}`

### Providers
- `app/Providers/AppServiceProvider.php`
  - View Composer para compartir categorías en vistas

## Ventajas del Sistema

✅ **Completamente Dinámico**: Agrega/elimina categorías sin tocar código
✅ **Sin Límite**: Crea todas las categorías principales que necesites
✅ **Ordenable**: Control total sobre el orden de aparición
✅ **SEO Friendly**: URLs amigables con slugs
✅ **Filtrable**: Activa/desactiva categorías según necesidad
✅ **Jerárquico**: Estructura clara padre-hijo

## Próximos Pasos Sugeridos

1. Crear las categorías principales que necesites en Filament
2. Asignar las categorías actuales como subcategorías
3. Ajustar el orden según preferencias
4. Probar la navegación en `http://facturacion.test/detalles`

## Notas Importantes

⚠️ **Las categorías existentes NO fueron eliminadas**
⚠️ El campo `slug` debe ser único
⚠️ Las categorías desactivadas no aparecen en el menú pero siguen en la BD
⚠️ Al eliminar una categoría padre, se eliminan las subcategorías (cascade)
