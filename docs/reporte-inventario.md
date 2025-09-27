# Documentación del Módulo Reporte de Inventario

## Resumen General

El módulo **Reporte de Inventario** es un sistema integral desarrollado con Filament 3 que proporciona análisis detallado del inventario empresarial. Incluye reportes de stock actual, productos con stock mínimo y kardex de movimientos por producto.

**Ubicación**: `app/Filament/Resources/ReporteInventarioResource.php`

**Características principales**:
- ✅ 3 tipos de reportes especializados
- ✅ Interfaz de usuario moderna con sidebar de navegación
- ✅ Exportación a CSV
- ✅ Filtros avanzados por almacén, categoría y estado
- ✅ Estadísticas en tiempo real
- ✅ Modales de detalles de movimientos

---

## Estructura del Módulo

### Recurso Principal
```php
// app/Filament/Resources/ReporteInventarioResource.php
```

**Configuración**:
- **Modelo Base**: `Product::class`
- **Icono**: `heroicon-o-chart-bar-square`
- **Grupo de navegación**: "Inventario"
- **Slug**: `reporte-inventario`
- **Sin CRUD**: Solo lectura (canCreate: false, canEdit: false, canDelete: false)

### Páginas del Módulo

#### 1. Página Índice
```php
// app/Filament/Resources/ReporteInventarioResource/Pages/ReporteInventarioIndex.php
```
- **Vista**: `filament.resources.reporte-inventario.pages.index`
- **Función**: Página principal con navegación a los reportes

#### 2. Stock Actual
```php
// app/Filament/Resources/ReporteInventarioResource/Pages/StockActualPage.php
```
- **Vista**: `filament.resources.reporte-inventario.pages.stock-actual`
- **Ruta**: `/stock-actual`
- **Función**: Reporte completo del inventario actual

#### 3. Stock Mínimo
```php
// app/Filament/Resources/ReporteInventarioResource/Pages/StockMinimoPage.php
```
- **Vista**: `filament.resources.reporte-inventario.pages.stock-minimo`
- **Ruta**: `/stock-minimo`
- **Función**: Productos con stock bajo o crítico

#### 4. Kardex Sencillo
```php
// app/Filament/Resources/ReporteInventarioResource/Pages/KardexSencilloPage.php
```
- **Vista**: `filament.resources.reporte-inventario.pages.kardex-sencillo`
- **Ruta**: `/kardex-sencillo`
- **Función**: Historial de movimientos por producto

---

## Tipos de Reportes Disponibles

### 1. 📊 Reporte de Stock Actual

**Propósito**: Visualizar el inventario actual de todos los productos activos que tienen seguimiento de inventario.

**Características**:
- Lista todos los productos con `track_inventory = true` y `status = 'active'`
- Muestra stock actual, stock mínimo y estado del producto
- Estadísticas en tiempo real: Disponible, Agotado, Crítico, Mínimo
- Colores dinámicos según el estado del stock

**Columnas principales**:
- **Producto**: Nombre del producto
- **SKU**: Código del producto
- **Categoría**: Categoría del producto
- **Almacén**: Almacén donde se encuentra
- **Stock Actual**: Cantidad disponible (con colores)
- **Stock Mínimo**: Cantidad mínima establecida
- **Estado**: Badge con estado (Normal/Bajo/Agotado)

**Estados del stock**:
- 🟢 **Normal**: `stock_actual > stock_minimo`
- 🟡 **Bajo**: `stock_actual <= stock_minimo` y `stock_actual > 0`
- 🔴 **Agotado**: `stock_actual <= 0`

### 2. ⚠️ Reporte de Stock Mínimo

**Propósito**: Identificar productos que requieren reposición urgente.

**Criterios de inclusión**:
- Productos con `qty <= min_qty`
- Solo productos con `min_qty` definido (NOT NULL)
- Productos activos con seguimiento de inventario

**Columnas adicionales específicas**:
- **Diferencia**: Diferencia entre stock actual y mínimo
- **Cantidad Requerida**: Cantidad necesaria para alcanzar el stock mínimo
- **Prioridad**: Nivel de urgencia de reposición

**Niveles de prioridad**:
- 🔴 **Crítica**: `stock_actual <= 0`
- 🟡 **Alta**: `stock_actual <= (stock_minimo * 0.5)`
- 🔵 **Media**: `stock_actual > (stock_minimo * 0.5)` pero `<= stock_minimo`

### 3. 📋 Kardex Sencillo

**Propósito**: Mostrar el historial completo de movimientos de inventario de un producto específico.

**Funcionalidad**:
- Requiere selección de producto
- Filtro por rango de fechas (por defecto: últimos 3 meses)
- Muestra todos los tipos de movimientos de inventario

**Columnas del kardex**:
- **Fecha**: Fecha y hora del movimiento
- **Tipo**: Tipo de movimiento con iconos y colores
- **Cantidad**: Cantidad movida con prefijos (±, →)
- **Movimiento de Almacén**: Descripción del flujo entre almacenes
- **Descripción/Motivo**: Razón del movimiento
- **Referencia**: Documento asociado al movimiento
- **Usuario**: Usuario responsable del movimiento

**Estadísticas del kardex**:
- Total de movimientos en el período
- Total de entradas
- Total de salidas
- Número de transferencias
- Número de ajustes
- Movimiento neto

---

## Modelos y Relaciones de Datos

### Modelo Product
```php
// app/Models/Product.php
```

**Campos relacionados con inventario**:
- `track_inventory`: Boolean - Si el producto tiene seguimiento de inventario
- `current_stock`: Decimal - Stock actual (legacy)
- `minimum_stock`: Decimal - Stock mínimo (legacy)
- `status`: String - Estado del producto ('active', etc.)

**Relaciones**:
- `stocks()`: HasMany - Relación con Stock por almacén
- `category()`: BelongsTo - Categoría del producto
- `brand()`: BelongsTo - Marca del producto

### Modelo Stock
```php
// app/Models/Stock.php
```

**Campos principales**:
- `product_id`: ID del producto
- `warehouse_id`: ID del almacén
- `qty`: Decimal(4) - Cantidad actual en stock
- `min_qty`: Decimal(4) - Cantidad mínima requerida

**Relaciones**:
- `product()`: BelongsTo - Producto asociado
- `warehouse()`: BelongsTo - Almacén donde se encuentra
- `company()`: BelongsTo - Empresa propietaria

**Scopes útiles**:
- `lowStock()`: Productos con stock bajo o crítico
- `byWarehouse($id)`: Filtro por almacén
- `byProduct($id)`: Filtro por producto

### Modelo InventoryMovement
```php
// app/Models/InventoryMovement.php
```

**Tipos de movimientos**:
- `OPENING`: Inventario inicial
- `IN`: Entrada de mercancía
- `OUT`: Salida de mercancía
- `TRANSFER`: Transferencia entre almacenes
- `ADJUST`: Ajuste de inventario

**Campos principales**:
- `product_id`: Producto afectado
- `type`: Tipo de movimiento
- `from_warehouse_id`: Almacén origen (nullable)
- `to_warehouse_id`: Almacén destino (nullable)
- `qty`: Cantidad del movimiento
- `reason`: Motivo del movimiento
- `ref_type`: Tipo de documento de referencia
- `ref_id`: ID del documento de referencia
- `movement_date`: Fecha del movimiento

**Relaciones**:
- `product()`: BelongsTo
- `fromWarehouse()`: BelongsTo
- `toWarehouse()`: BelongsTo
- `user()`: BelongsTo

**Métodos útiles**:
- `getTypeLabel()`: Retorna etiqueta en español del tipo
- `getWarehouseMovementDescription()`: Descripción del flujo entre almacenes

### Modelo Warehouse
```php
// app/Models/Warehouse.php (referenciado)
```

**Relaciones**:
- Almacenes de origen y destino en movimientos
- Ubicación de stocks por producto

### Modelo Category
```php
// app/Models/Category.php (referenciado)
```

**Uso en reportes**:
- Filtro de productos por categoría
- Agrupación y análisis por categoria

---

## Interfaz de Usuario

### Diseño General

**Layout principal**:
- **Sidebar izquierdo**: Navegación entre reportes con diseño gradient
- **Área principal**: Contenido del reporte seleccionado
- **Diseño responsivo**: Se adapta a dispositivos móviles

### Componentes de la UI

#### Sidebar de Navegación
```html
<!-- Ubicación: resources/views/filament/resources/reporte-inventario/pages/ -->
```

**Características**:
- Diseño con gradiente naranja/dorado
- Efectos de hover animados
- Navegación sticky en desktop
- Indicador visual de página activa
- Responsive (se convierte en stack en móvil)

#### Tarjetas de Estadísticas

**Métricas mostradas**:
- 🟢 **Stock Disponible**: Productos en estado normal
- 🔴 **Stock Agotado**: Productos sin existencias
- 🟡 **Stock Crítico**: Productos en estado bajo
- 📊 **Stock Mínimo**: Productos con mínimo definido

**Características visuales**:
- Gradientes de colores según tipo
- Efectos de hover con elevación
- Números grandes y llamativos
- Animaciones CSS suaves

#### Tablas de Datos

**Funcionalidades**:
- Filtros por almacén, categoría y estado
- Búsqueda en tiempo real
- Ordenamiento por columnas
- Paginación configurable (10, 25, 50, 100)
- Acciones por fila y bulk actions
- Exportación a CSV

### Modal de Detalles de Movimiento

**Ubicación**: `resources/views/filament/resources/reporte-inventario/modals/movement-details.blade.php`

**Contenido**:
- Header con tipo de movimiento y color
- Cantidad destacada con formato
- Flujo de almacenes (para transferencias)
- Grid de detalles completos
- Información adicional (lotes, vencimientos, notas)
- Resumen del movimiento

---

## Funcionalidades Técnicas

### Filtros Avanzados

#### Stock Actual y Stock Mínimo
- **Por Almacén**: SelectFilter con opciones de Warehouse
- **Por Categoría**: SelectFilter con opciones de Category
- **Por Estado del Stock**: Filter personalizado (Normal/Bajo/Agotado)

#### Kardex Sencillo
- **Por Tipo de Movimiento**: SelectFilter con tipos de InventoryMovement
- **Por Almacén**: Filtro que considera origen Y destino
- **Por Rango de Cantidad**: Filter personalizado (Pequeñas/Medianas/Grandes)

### Exportación a CSV

**Características**:
- Export de registros seleccionados o todos
- Headers personalizados en español
- Formato de fecha localizado
- Nombres de archivo con timestamp
- Streaming response para grandes volúmenes

**Archivos generados**:
- `stock-actual-YYYY-MM-DD-HH-MM-SS.csv`
- `stock-minimo-YYYY-MM-DD-HH-MM-SS.csv`
- `kardex-{producto-name}-YYYY-MM-DD-HH-MM-SS.csv`

### Cálculos en Tiempo Real

#### Estadísticas de Stock
```php
// Cálculo realizado en mount() de cada página
foreach ($products as $product) {
    $stock = $product->stocks->first();
    $actual = $stock->qty ?? 0;
    $minimo = $stock->min_qty ?? 0;

    if ($actual <= 0) {
        $this->stockAgotado++;
    } elseif ($actual <= $minimo) {
        $this->stockCritico++;
    } else {
        $this->stockDisponible++;
    }
}
```

#### Datos del Kardex
```php
// Resumen automático por producto seleccionado
$totalIn = $movements->whereIn('type', ['IN', 'OPENING'])->sum('qty');
$totalOut = $movements->where('type', 'OUT')->sum('qty');
$netMovement = $totalIn - $totalOut;
```

---

## Rutas de Acceso

### URLs del Módulo
- **Índice**: `/admin/reporte-inventario`
- **Stock Actual**: `/admin/reporte-inventario/stock-actual`
- **Stock Mínimo**: `/admin/reporte-inventario/stock-minimo`
- **Kardex Sencillo**: `/admin/reporte-inventario/kardex-sencillo`

### Navegación en Filament
```
Panel Admin > Inventario > Reporte Inventario
```

---

## Configuración y Dependencias

### Requisitos del Sistema
- **Laravel**: 10+
- **Filament**: 3.x
- **PHP**: 8.1+
- **Base de datos**: MySQL/PostgreSQL

### Modelos Requeridos
- `Product` (con relación a stocks)
- `Stock` (tabla pivot producto-almacén)
- `InventoryMovement` (log de movimientos)
- `Warehouse` (almacenes)
- `Category` (categorías de productos)
- `Company` (empresas/tenants)
- `User` (usuarios del sistema)

### Migraciones Relacionadas
- `products` table
- `stocks` table
- `inventory_movements` table
- `warehouses` table
- `categories` table

---

## Casos de Uso Principales

### 1. Control de Inventario Diario
**Usuario**: Administrador de inventario
**Flujo**:
1. Accede a "Stock Actual"
2. Revisa estadísticas generales
3. Filtra por almacén específico
4. Identifica productos con stock bajo
5. Exporta reporte para seguimiento

### 2. Gestión de Reposiciones
**Usuario**: Comprador/Gerente
**Flujo**:
1. Accede a "Stock Mínimo"
2. Revisa productos críticos (prioridad alta)
3. Filtra por categoría específica
4. Calcula cantidades requeridas
5. Genera orden de compra

### 3. Auditoría de Movimientos
**Usuario**: Contador/Auditor
**Flujo**:
1. Accede a "Kardex Sencillo"
2. Selecciona producto específico
3. Define rango de fechas
4. Revisa historial completo
5. Analiza movimientos anómalos
6. Exporta para documentación

### 4. Análisis de Rotación
**Usuario**: Analista de inventario
**Flujo**:
1. Usa kardex para productos específicos
2. Analiza frecuencia de movimientos
3. Identifica patrones de entrada/salida
4. Calcula velocidad de rotación
5. Optimiza niveles de stock mínimo

---

## Consideraciones de Rendimiento

### Optimizaciones Implementadas
- **Eager Loading**: Carga relaciones (stocks.warehouse, category) en una consulta
- **Select específico**: Solo campos necesarios en queries principales
- **Distinct**: Evita duplicados en joins complejos
- **Índices**: En campos de filtro frecuente (product_id, warehouse_id, type)

### Limitaciones Conocidas
- Kardex requiere selección de producto (no carga todos por defecto)
- Estadísticas se calculan en mount() (no cached)
- Export de grandes volúmenes puede ser lento sin queue

---

## Extensibilidad

### Puntos de Extensión
1. **Nuevos tipos de reporte**: Agregar páginas adicionales
2. **Filtros personalizados**: Implementar Filter classes
3. **Métricas adicionales**: Expandir cálculos en mount()
4. **Formatos de export**: CSV, Excel, PDF
5. **Gráficos**: Integrar Charts.js o similar

### Patrones Seguidos
- **Page classes** para cada tipo de reporte
- **Blade components** reutilizables
- **Scopes** en modelos para filtros comunes
- **Traits** para funcionalidades compartidas

---

## Mantenimiento y Soporte

### Logs y Debugging
- Usa logs estándar de Laravel
- Filament panel debug en desarrollo
- SQL query logging para optimización

### Testing Recomendado
- Tests de integración para cada reporte
- Verificación de filtros y exports
- Performance testing con grandes datasets
- UI testing con Dusk para flujos completos

### Documentación de Cambios
- Mantener changelog en este documento
- Documentar nuevas columnas o filtros
- Versionar cambios en structure de datos

---

**Última actualización**: Septiembre 2024
**Versión**: 1.0
**Mantenido por**: Equipo de Desarrollo