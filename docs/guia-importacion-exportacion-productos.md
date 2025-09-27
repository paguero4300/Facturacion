# Guía de Importación y Exportación de Productos

## 📋 Descripción General

Este sistema permite importar y exportar productos masivamente usando archivos Excel/CSV, facilitando la gestión de inventarios y la migración de datos.

## 🚀 Funcionalidades Implementadas

### ✅ Exportación de Productos
- **Ubicación**: `http://facturacion.test/admin/products`
- **Botón**: "Exportar Excel" (icono de descarga)
- **Formato**: Excel (.xlsx) con 20 columnas según formato establecido
- **Nombre del archivo**: `productos-YYYY-MM-DD-HH-mm-ss.xlsx`

### ✅ Importación de Productos
- **Ubicación**: `http://facturacion.test/admin/products`
- **Botón**: "Importar Excel" (icono de subida)
- **Formatos soportados**: Excel (.xlsx), CSV (.csv)
- **Validaciones automáticas**: Códigos únicos, precios, categorías, marcas

## 📊 Formato de Archivo (21 Columnas)

| # | Columna | Obligatorio | Descripción | Ejemplo |
|---|---------|-------------|-------------|----------|
| 1 | Empresa | ❌ | RUC o nombre de la empresa | 20613251988, GREEN SAC |
| 2 | Código | ✅ | Código único del producto | PROD001 |
| 3 | Nombre | ✅ | Nombre del producto | Laptop HP |
| 4 | Descripción | ❌ | Descripción detallada | Laptop para oficina |
| 5 | Modelo | ❌ | Modelo del producto | HP-15-DY |
| 6 | Unidad de medida | ✅ | Unidad base | UND, KG, LT |
| 7 | Posee IGV | ✅ | SI/NO para impuestos | SI, NO |
| 8 | Categoría | ❌ | Categoría del producto | Electrónicos |
| 9 | Marca | ❌ | Marca del producto | HP |
| 10 | Precio | ✅ | Precio de venta | 1500.00 |
| 11 | Fecha de vencimiento | ❌ | Fecha límite | 2024-12-31 |
| 12 | Precio Unidad 1 | ❌ | Precio primera unidad | 1500.00 |
| 13 | Descripción Unidad 1 | ❌ | Descripción unidad 1 | Unidad |
| 14 | Factor Unidad 1 | ❌ | Factor conversión | 1 |
| 15 | Precio Costo Unidad 1 | ❌ | Costo primera unidad | 1200.00 |
| 16 | Precio Unidad 2 | ❌ | Precio segunda unidad | 18000.00 |
| 17 | Descripción Unidad 2 | ❌ | Descripción unidad 2 | Caja |
| 18 | Factor Unidad 2 | ❌ | Factor conversión | 12 |
| 19 | Precio Costo Unidad 2 | ❌ | Costo segunda unidad | 14400.00 |
| 20 | Stock actual | ❌ | Cantidad en inventario | 10 |
| 21 | imagenes | ❌ | Ruta o URL de imagen | productos/laptop.jpg |

## 🔧 Instrucciones de Uso

### Para Exportar Productos:
1. Ir a `http://facturacion.test/admin/products`
2. Hacer clic en "Exportar Excel"
3. Confirmar la exportación
4. Descargar el archivo generado

### Para Importar Productos:
1. Ir a `http://facturacion.test/admin/products`
2. Hacer clic en "Importar Excel"
3. Seleccionar archivo Excel/CSV con formato correcto
4. Revisar vista previa de datos
5. Confirmar importación
6. Revisar resultados y errores (si los hay)

**Nota sobre el campo Empresa:**
- Si se especifica el RUC o nombre de empresa, el producto se asignará a esa empresa
- Si se deja vacío, se asignará a la empresa del usuario autenticado
- Ejemplos válidos: `20613251988`, `GREEN SAC`, `DETALLES Y MÁS FLORES`

## 📁 Plantilla de Ejemplo

Se ha creado una plantilla CSV de ejemplo en:
`storage/app/public/plantilla-productos.csv`

Esta plantilla incluye:
- ✅ Cabeceras correctas
- ✅ 3 productos de ejemplo
- ✅ Diferentes casos de uso
- ✅ Formato correcto de datos

## 🛡️ Validaciones Implementadas

### Validaciones Automáticas:
- **Código único**: No se permiten códigos duplicados
- **Campos obligatorios**: Código, Nombre, Unidad de medida, Posee IGV, Precio
- **Formato de precios**: Solo números positivos
- **IGV válido**: Solo acepta SI/NO (case insensitive)
- **Categorías/Marcas**: Se crean automáticamente si no existen

### Manejo de Imágenes:
- **URLs**: Descarga automática de imágenes desde URLs
- **Rutas relativas**: Verifica existencia en storage/app/public
- **Formatos soportados**: JPG, PNG, GIF, WEBP
- **Almacenamiento**: storage/app/public/products/

## ⚠️ Consideraciones Importantes

### Antes de Importar:
1. **Backup**: Realizar respaldo de la base de datos
2. **Formato**: Verificar que el archivo siga exactamente el formato
3. **Códigos**: Asegurar que los códigos sean únicos
4. **Imágenes**: Verificar que las rutas/URLs sean válidas

### Durante la Importación:
- El proceso puede tomar tiempo con archivos grandes
- Se mostrarán errores específicos por fila
- Las categorías y marcas se crean automáticamente
- Los productos se asocian a la empresa del usuario logueado

### Después de la Importación:
- Revisar productos creados
- Verificar imágenes cargadas
- Confirmar precios y stock
- Ajustar configuraciones adicionales si es necesario

## 🔍 Solución de Problemas

### Errores Comunes:

**"El código ya existe"**
- Verificar códigos duplicados en el archivo
- Revisar productos existentes en el sistema

**"Precio inválido"**
- Usar punto (.) como separador decimal
- Solo números positivos

**"IGV inválido"**
- Usar exactamente "SI" o "NO"
- Verificar mayúsculas/minúsculas

**"Error al cargar imagen"**
- Verificar URL accesible
- Confirmar formato de imagen válido
- Revisar permisos de storage

## 📞 Soporte

Para problemas técnicos o dudas sobre el formato:
1. Revisar esta documentación
2. Verificar logs en `storage/logs/laravel.log`
3. Contactar al administrador del sistema

---

**Versión**: 1.0  
**Fecha**: Septiembre 2024  
**Compatibilidad**: Laravel 11 + Filament 3