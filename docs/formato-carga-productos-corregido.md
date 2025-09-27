# Formato Corregido para Carga Masiva de Productos

## Cabeceras Recomendadas (Separadas por tabulaciones)

```
codigo	nombre	descripcion	tipo_producto	codigo_unidad	descripcion_unidad	precio_costo	precio_base	precio_venta	tipo_igv	tasa_igv	categoria	marca	stock_actual	stock_minimo	controlar_inventario	codigo_barras	estado	para_venta	ruta_imagen
```

## Descripción de Campos

| Campo | Tipo | Obligatorio | Descripción | Ejemplo |
|-------|------|-------------|-------------|---------|
| `codigo` | String(50) | ✅ | Código interno único del producto | PROD001 |
| `nombre` | String(500) | ✅ | Nombre/descripción del producto | Laptop HP Pavilion 15 |
| `descripcion` | Text | ❌ | Descripción detallada | Laptop para oficina con procesador Intel i5 |
| `tipo_producto` | Enum | ✅ | Tipo: product o service | product |
| `codigo_unidad` | String(5) | ✅ | Código SUNAT unidad de medida | NIU |
| `descripcion_unidad` | String(100) | ✅ | Descripción de la unidad | UNIDAD (BIENES) |
| `precio_costo` | Decimal(12,4) | ❌ | Precio de costo sin IGV | 2500.00 |
| `precio_base` | Decimal(12,4) | ✅ | Precio unitario sin IGV | 2966.10 |
| `precio_venta` | Decimal(12,4) | ✅ | Precio de venta con IGV | 3500.00 |
| `tipo_igv` | String(2) | ✅ | Tipo afectación IGV SUNAT | 10 |
| `tasa_igv` | Decimal(4,4) | ❌ | Tasa de IGV (por defecto 0.18) | 0.18 |
| `categoria` | String(100) | ❌ | Nombre de la categoría | Computadoras |
| `marca` | String(100) | ❌ | Nombre de la marca | HP |
| `stock_actual` | Decimal(12,4) | ❌ | Stock inicial | 50 |
| `stock_minimo` | Decimal(12,4) | ❌ | Stock mínimo para alertas | 10 |
| `controlar_inventario` | Boolean | ❌ | 1=Sí, 0=No (por defecto 1) | 1 |
| `codigo_barras` | String(50) | ❌ | Código de barras del producto | 7501234567890 |
| `estado` | String | ❌ | active o inactive (por defecto active) | active |
| `para_venta` | Boolean | ❌ | 1=Sí, 0=No (por defecto 1) | 1 |
| `ruta_imagen` | String(500) | ❌ | Ruta o URL de la imagen | productos/laptop-hp.jpg |

## Valores Permitidos

### Tipo de Producto
- `product` - Producto físico
- `service` - Servicio

### Códigos de Unidad SUNAT (Catálogo 03)
- `NIU` - UNIDAD (BIENES)
- `ZZ` - SERVICIO
- `KGM` - KILOGRAMO
- `MTR` - METRO
- `LTR` - LITRO
- `M2` - METRO CUADRADO
- `M3` - METRO CÚBICO
- `CEN` - CIENTO
- `MIL` - MILLAR
- `DOZ` - DOCENA

### Tipo de Afectación IGV SUNAT
- `10` - Gravado - Operación Onerosa
- `20` - Exonerado - Operación Onerosa
- `30` - Inafecto - Operación Onerosa

## Ejemplo de Datos

```
codigo	nombre	descripcion	tipo_producto	codigo_unidad	descripcion_unidad	precio_costo	precio_base	precio_venta	tipo_igv	tasa_igv	categoria	marca	stock_actual	stock_minimo	controlar_inventario	codigo_barras	estado	para_venta	ruta_imagen
PROD001	Laptop HP Pavilion 15	Laptop para oficina con procesador Intel i5	product	NIU	UNIDAD (BIENES)	2500.00	2966.10	3500.00	10	0.18	Computadoras	HP	50	10	1	7501234567890	active	1	productos/laptop-hp.jpg
SERV001	Consultoría IT	Servicio de consultoría en tecnología	service	ZZ	SERVICIO		84.75	100.00	10	0.18	Servicios				0		active	1	
PROD002	Mouse Inalámbrico	Mouse óptico inalámbrico	product	NIU	UNIDAD (BIENES)	25.00	42.37	50.00	10	0.18	Accesorios	Logitech	100	20	1	7501234567891	active	1	productos/mouse-logitech.jpg
```

## Manejo de Imágenes

### Opciones para el campo `ruta_imagen`:

1. **Ruta relativa** (Recomendado):
   - `productos/imagen.jpg`
   - Se almacenará en `storage/app/public/productos/`

2. **URL completa**:
   - `https://ejemplo.com/imagenes/producto.jpg`
   - Se descargará y almacenará localmente

3. **Múltiples imágenes** (Futuro):
   - `imagen1.jpg|imagen2.jpg|imagen3.jpg`
   - Separadas por pipe (|)

4. **Campo vacío**:
   - Se usará imagen por defecto del sistema

### Estructura de Carpetas Recomendada:
```
storage/app/public/
└── productos/
    ├── categoria1/
    │   ├── producto1.jpg
    │   └── producto2.jpg
    └── categoria2/
        ├── producto3.jpg
        └── producto4.jpg
```

## Validaciones Importantes

1. **Código único**: No puede repetirse dentro de la misma empresa
2. **Precios coherentes**: precio_venta >= precio_base >= precio_costo
3. **IGV calculado**: precio_venta = precio_base * (1 + tasa_igv) cuando tipo_igv = '10'
4. **Categorías y marcas**: Se crearán automáticamente si no existen
5. **Imágenes**: Se validará que el archivo existe o la URL es accesible

## Notas de Implementación

- Las categorías y marcas se crearán automáticamente si no existen
- Las imágenes se procesarán y optimizarán automáticamente
- Los campos booleanos aceptan: 1/0, true/false, sí/no
- Los decimales pueden usar punto (.) o coma (,) como separador
- Las fechas deben estar en formato YYYY-MM-DD
- El archivo debe estar en formato UTF-8 para caracteres especiales

## Diferencias con el Formato Original

### ❌ Campos Removidos:
- `0` (columna sin propósito)
- `Modelo` (reemplazado por `codigo`)
- `Fecha de vencimiento` (no aplicable a productos)
- `Factor` (duplicado, sin contexto)
- `Precio 1, 2, 3` (reemplazados por estructura clara)
- Campos duplicados de `Unidad` y `Descripcion`

### ✅ Campos Agregados:
- `codigo` - Identificador único obligatorio
- `nombre` - Nombre del producto obligatorio
- `tipo_producto` - Clasificación producto/servicio
- `tipo_igv` - Configuración tributaria SUNAT
- `estado` - Control de estado activo/inactivo
- `controlar_inventario` - Flag para control de stock
- `codigo_barras` - Identificación por código de barras

### 🔄 Campos Mejorados:
- `Posee IGV` → `tipo_igv` + `tasa_igv` (más específico)
- `Precio` → `precio_costo` + `precio_base` + `precio_venta` (estructura clara)
- `Unidad de medida` → `codigo_unidad` + `descripcion_unidad` (cumple SUNAT)
- `imagenes` → `ruta_imagen` (singular, más claro)

Este formato garantiza compatibilidad total con la base de datos existente y cumple con los requerimientos de SUNAT para facturación electrónica.