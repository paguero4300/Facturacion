# Sistema de Filtros Globales Integrado: Categorías + Almacén para Toda la Aplicación

## Visión General

El sistema actual de productos cuenta con filtros de categorías distribuidos en múltiples páginas y componentes. El requerimiento es **crear un sistema de filtros global** que integre categorías y almacén, funcionando de manera consistente en **todas las páginas principales** de la aplicación:

- **`/detalles`** (página principal/home)
- **`/tienda`** (catálogo de productos)
- **`/{categorySlug}`** (páginas individuales de categorías) 
- **Widgets de productos destacados** (componentes)
- **Widgets de categorías** (componentes)

**Problema Identificado**: Se implementó previamente un modal independiente de productos por almacén, pero el requerimiento real es integrar el filtro de almacén al sistema existente de manera que funcione **globalmente en toda la aplicación**, no solo en una página específica.

**IMPORTANTE: NO es un modal que aparece al cargar la página. Es una integración natural al sistema de filtros existente que ya tienes en la sidebar de /tienda, pero extendido a TODAS las páginas.**

## Cómo Funciona el Sistema (SIN MODAL)

### Explicación Visual del Comportamiento

**Estado Actual en /tienda:**
```
[Sidebar Izquierdo]        [Contenido Principal]
┌───────────────┐      ┌────────────────┐
│ Categorías      │      │ Grid Productos  │
│ ☐ Todas       │      │ [P1] [P2] [P3]  │
│ ☐ Electrónicos │      │ [P4] [P5] [P6]  │
│ ☑ Hogar       │      │ [P7] [P8] [P9]  │
└────────────────┘      └────────────────┘
```

**Estado Deseado en /tienda (EXPANDIDO):**
```
[Sidebar Izquierdo EXPANDIDO]  [Contenido Principal]
┌────────────────────┐      ┌────────────────┐
│ Categorías            │      │ Grid Productos  │
│ ☐ Todas             │      │ [P1] [P2] [P3]  │
│ ☐ Electrónicos       │      │ [P4] [P5] [P6]  │
│ ☑ Hogar             │      │ [P7] [P8] [P9]  │
│                      │      │                 │
│ Almacenes            │      │ (Solo productos │
│ ☐ Todos             │      │  de Hogar +      │
│ ☐ Almacén Centro   │      │  Almacén Norte)  │
│ ☑ Almacén Norte    │      │                 │
└────────────────────┘      └────────────────┘
```

**Estado Deseado en /detalles (PÁGINA PRINCIPAL):**
```
[Header con Filtros]
┌───────────────────────────────────────────────────────────┐
│ Filtros: [▼ Categorías] [▼ Almacenes] [Limpiar Filtros]     │
└───────────────────────────────────────────────────────────┘

[Widget Categorías - Solo muestra categorías con productos filtrados]
[Widget Productos Destacados - Solo productos que pasan filtros]
```

**Estado Deseado en /{categorySlug} (PÁGINAS DE CATEGORÍAS):**
```
[Breadcrumb con Filtros]
Inicio > Hogar > Filtros: Almacén Norte [X]

[Filtros Adicionales - NO interfiere con categoría URL]
┌──────────────────────────────────────────┐
│ Filtros Adicionales: [▼ Almacenes]        │
└──────────────────────────────────────────┘

[Grid de Productos - Categoría de URL + Filtros Adicionales]
```

### Implementación Técnica SIN MODAL

**NO se crea ningún modal**. Se aprovecha la estructura existente:

1. **En /tienda**: Se **EXPANDE** el sidebar existente agregando la sección de almacenes
2. **En /detalles**: Se **AGREGA** una barra de filtros horizontal en la parte superior
3. **En /{categorySlug}**: Se **AGREGA** un pequeño selector de filtros adicionales
4. **En Widgets**: Los productos destacados y categorías **RESPONDEN** a parámetros URL

**Flujo de Usuario Real:**

```
Usuario en /detalles
↓
Ve dropdown "Almacenes" en header
↓
Selecciona "Almacén Norte"
↓
URL cambia a: /detalles?warehouse=2
↓
Página recarga mostrando solo productos con stock en Almacén Norte
↓
Usuario navega a "Ver Todos los Productos"
↓
URL: /tienda?warehouse=2 (mantiene el filtro)
↓
Sidebar de /tienda muestra "Almacén Norte" seleccionado
```

**Es exactamente como funciona ahora el filtro de categorías, pero extendido con almacenes y funcionando en todas las páginas.**

El sistema actual funciona de manera distribuida en múltiples controladores:

**Controladores Involucrados**:
- `DetallesController::index()` - Página principal con categorías y productos destacados
- `DetallesController::showCategory()` - Páginas individuales de categorías
- `ShopController::index()` - Catálogo completo de productos
- `ShopController::show()` - Detalle de producto individual

**Vistas con Filtros**:
- `resources/views/index.blade.php` - Home principal con widgets
- `resources/views/shop/index.blade.php` - Tienda con sidebar de filtros
- `resources/views/category.blade.php` - Páginas de categorías individuales
- `resources/views/partials/categories.blade.php` - Widget de categorías
- `resources/views/partials/featured-products.blade.php` - Widget de productos destacados

**Flujo de Filtrado Actual**:
```
URL: /tienda?category=categoria-slug (ShopController)
URL: /categoria-slug (DetallesController::showCategory)
URL: /detalles (DetallesController::index - sin filtros)
↓
Cada controlador maneja filtros independientemente
↓
No hay consistencia global de filtros
```

## Diseño de la Solución

### Arquitectura de Filtros Combinados

La solución integra el filtro de almacén al sistema existente manteniendo la simplicidad y consistencia:

**Parámetros de URL Unificados Globalmente**:
- `?category=categoria-slug` (mejorado - funciona en todas las páginas)
- `?warehouse=almacen-id` (nuevo - funciona en todas las páginas)
- `?category=categoria-slug&warehouse=almacen-id` (combinado - global)

**Lógica de Filtrado Global Unificada**:
```
Consulta Base: Product::where('status', 'active')->where('for_sale', true)
↓
Si hay parámetro 'category' → Aplicar filtro de categoría (todas las páginas)
↓
Si hay parámetro 'warehouse' → Aplicar filtro de almacén via stocks (todas las páginas)
↓
Ordenamiento consistente y paginación (donde aplique)
↓
Compartir lógica entre todos los controladores
```

### Modificaciones a los Controladores

**Creación de Trait GlobalFilters**

Para evitar duplicación de código, se crea un trait compartido:

**Funcionalidades del Trait**:
- Método `applyGlobalFilters()` para aplicar filtros de categoría y almacén
- Método `getFilterData()` para obtener categorías y almacenes para selectores
- Método `buildFilteredQuery()` para construir consultas consistentes

**Controladores Modificados**:

1. **DetallesController**:
   - `index()` - Aplicar filtros a productos destacados y mantener widgets
   - `showCategory()` - Combinar filtro de categoría URL con filtros adicionales

2. **ShopController**:
   - `index()` - Extender filtros existentes con almacén
   - `show()` - Aplicar filtros a productos relacionados

3. **Nuevos Métodos Globales**:
   - Filtro de almacén mediante relación `stocks`
   - Mantener productos con stock disponible (qty > 0)
   - Preservar toda la lógica existente

### Modificaciones a las Vistas

**Sistema de Filtros Consistente en Todas las Páginas**

Se implementa un componente de filtros reutilizable que funciona en todas las páginas:

**Componente Global de Filtros**:
```
components/global-filters.blade.php
├── Sección Categorías
│   ├── "Todas las categorías"
│   └── Lista de categorías activas
├── Separador visual
└── Sección Almacenes
    ├── "Todos los almacenes"
    └── Lista de almacenes activos
```

**Integración por Página**:

1. **Página Principal (/detalles)**:
   - Filtros aplicados a widgets de productos destacados
   - Filtros aplicados a widgets de categorías
   - Navegación consistente a páginas filtradas

2. **Tienda (/tienda)**:
   - Sidebar expandido con ambos filtros
   - Grid principal respondiendo a filtros combinados

3. **Páginas de Categorías (/{slug})**:
   - Filtros adicionales preservando categoría de la URL
   - Combinar filtro de categoría implícito con filtros explícitos

4. **Widgets Globales**:
   - Productos destacados filtrados según parámetros globales
   - Enlaces de categorías que preservan filtros activos

### Experiencia de Usuario

**Flujo de Interacción**:

1. **Estado Inicial**: Todos los productos visibles sin filtros
2. **Filtro por Categoría**: Click en categoría → filtra productos de esa categoría
3. **Filtro por Almacén**: Click en almacén → filtra productos con stock en ese almacén
4. **Filtros Combinados**: Ambos filtros activos → productos de categoría X con stock en almacén Y
5. **Limpieza de Filtros**: Enlaces para remover filtros individuales o todos

**Indicadores Visuales**:
- Filtros activos resaltados con color distintivo
- Breadcrumb que muestra: "Categoría: X | Almacén: Y"
- Contador de productos encontrados
- Badges de stock disponible en cards de productos

## Especificaciones Técnicas

### Modelo de Datos

**Relaciones Utilizadas**:
- `Product → Category` (existente)
- `Product → Stocks → Warehouse` (existente)
- `Warehouse` independiente para selector

**Consulta de Filtrado Combinado**:
La consulta se construye dinámicamente basada en parámetros presentes:

```
Base Query → Filter Category → Filter Warehouse → Order & Paginate
```

### Componentes de la Vista

**Sidebar de Filtros Expandido**:
- Sección de categorías (sin cambios)
- Nueva sección de almacenes con diseño consistente
- Enlaces que preservan estado de filtros cruzados

**Grid de Productos Mejorado**:
- Indicadores de stock por producto
- Badge de almacén en caso de filtro activo
- Información de disponibilidad mejorada

**Header de Resultados**":
- Breadcrumb de filtros aplicados
- Contador total de productos encontrados
- Enlaces de limpieza de filtros

### Casos de Uso Globales

#### Caso 1: Filtros en Página Principal (/detalles)
- URL: `/detalles?category=electrodomesticos&warehouse=1`
- Comportamiento: Productos destacados filtrados por categoría y almacén
- Vista: Widgets muestran solo productos filtrados, enlaces preservan filtros

#### Caso 2: Filtros en Tienda (/tienda)
- URL: `/tienda?category=electrodomesticos&warehouse=1`
- Comportamiento: Catálogo completo filtrado (comportamiento actual mejorado)
- Vista: Sidebar con ambos filtros resaltados

#### Caso 3: Filtros en Páginas de Categorías
- URL: `/electrodomesticos?warehouse=1`
- Comportamiento: Productos de la categoría URL + filtro de almacén adicional
- Vista: Categoría implícita + selector de almacén

#### Caso 4: Navegación Consistente
- Desde cualquier página con filtros activos
- Enlaces preservan filtros al navegar entre secciones
- Breadcrumbs globales muestran filtros aplicados

#### Caso 5: Widgets Inteligentes
- Productos destacados responden a filtros globales
- Categorías muestran solo las que tienen productos con stock
- Enlaces de "Ver más" preservan contexto de filtros

### Consideraciones de Rendimiento Global

**Optimizaciones de Consulta**:
- Índices de base de datos en campos de filtrado frecuente (category_id, warehouse_id)
- Eager loading de relaciones necesarias por página (stocks, categories)
- Consultas optimizadas que eviten N+1 en widgets y listados
- Paginación inteligente que preserve filtros en todas las páginas

**Caché Estratégico Global**:
- Lista de categorías activas (cacheada globalmente, usado en todas las páginas)
- Lista de almacenes con stock (cacheada por períodos cortos)
- Contadores de productos por filtro (caché invalidable por cambios de stock)
- Widgets estáticos con caché condicional basado en filtros

## Flujo de Implementación

### Fase 1: Infraestructura Global
1. Crear trait `GlobalFilters` para lógica compartida de filtros
2. Crear componente `global-filters.blade.php` reutilizable
3. Definir estructura de parámetros URL consistente
4. Implementar helpers para construcción de enlaces con filtros

### Fase 2: Integración en Controladores
1. Modificar `DetallesController::index()` para filtros en home
2. Modificar `DetallesController::showCategory()` para páginas de categorías
3. Extender `ShopController::index()` con filtros de almacén
4. Actualizar `ShopController::show()` para productos relacionados filtrados

### Fase 3: Actualización de Vistas
1. Integrar componente de filtros en todas las páginas principales
2. Actualizar widgets de productos destacados para responder a filtros
3. Modificar widgets de categorías para preservar filtros
4. Implementar breadcrumbs globales de filtros activos

### Fase 4: Experiencia de Usuario
1. Implementar indicadores visuales de filtros activos en todas las páginas
2. Agregar contadores de productos por filtro aplicado
3. Crear enlaces de limpieza de filtros accesibles desde cualquier página
4. Optimizar navegación fluida entre páginas preservando contexto

### Fase 5: Validación Global
1. Probar flujos de filtrado en todas las páginas principales
2. Validar consistencia de filtros entre widgets y páginas completas
3. Asegurar rendimiento con consultas optimizadas globalmente
4. Documentar sistema de filtros global para usuarios y desarrolladores

## Beneficios de la Solución Global

**Para el Usuario**:
- Experiencia de filtrado consistente en todas las páginas de la aplicación
- Capacidad de combinar filtros para búsquedas específicas desde cualquier sección
- Información clara de disponibilidad por almacén en tiempo real
- Navegación fluida entre páginas preservando contexto de búsqueda
- Sin interrupciones por modals - todo integrado naturalmente

**Para el Negocio**:
- Mejor organización del inventario visible en toda la aplicación
- Facilita la gestión de stock por ubicación desde cualquier página
- Mejora la experiencia de compra con filtros consistentes
- Datos más precisos sobre preferencias de filtrado global
- Incremento potencial en conversiones por mejor visibilidad de productos

**Para el Desarrollo**:
- Extensión natural del sistema existente sin cambios drásticos
- Mantenimiento de patrones de código establecidos en todos los controladores
- Reutilización de componentes y estilos existentes
- Escalabilidad para futuros filtros adicionales (marca, precio, etc.)
- Código DRY mediante trait compartido entre controladores

## Conclusión

Esta solución crea un **sistema de filtros global integrado** que funciona consistentemente en todas las páginas principales de la aplicación (/detalles, /tienda, /{categorySlug}), incluyendo widgets y componentes. 

La implementación extiende naturalmente el sistema de categorías existente para incluir filtros de almacén, manteniendo la simplicidad y eficiencia actual mientras proporciona la funcionalidad global requerida. 

Todos los patrones y convenciones establecidos se preservan, asegurando consistencia y mantenibilidad del código, mientras se elimina la necesidad del modal independiente implementado previamente.