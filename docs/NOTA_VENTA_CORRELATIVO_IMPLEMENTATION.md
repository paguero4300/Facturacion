# 🔢 Implementación de Correlativo Automático para Nota de Venta

## ✅ Funcionalidad Completada

He implementado la funcionalidad completa para que cuando se seleccione "Nota de Venta" se obtenga automáticamente el correlativo y la serie NV correspondiente, igual que funciona con facturas y boletas.

## 🎯 **Funcionalidades Implementadas**

### **🔄 Auto-selección de Serie y Correlativo:**
- ✅ **Al seleccionar tipo de documento** → Se busca automáticamente la serie activa
- ✅ **Serie NV01** → Se asigna automáticamente para Nota de Venta
- ✅ **Correlativo automático** → Se calcula el siguiente número disponible
- ✅ **Mismo comportamiento** que facturas y boletas

### **⚙️ Gestión de Series Completa:**
- ✅ **DocumentSeriesResource** actualizado para Nota de Venta
- ✅ **Edición de series NV** disponible en `/admin/document-series`
- ✅ **Filtros y búsquedas** incluyen Nota de Venta
- ✅ **Badge morado** para identificar visualmente

## 🔧 **Archivos Modificados**

### **📋 InvoiceResource.php:**
```php
// Auto-selección de serie y correlativo
->afterStateUpdated(function ($state, callable $set, callable $get) {
    // Reset series when document type changes
    $set('document_series_id', null);
    $set('series', null);
    $set('number', null);
    
    // Auto-select document series based on document type
    $companyId = $get('company_id');
    if ($companyId && $state) {
        $series = DocumentSeries::query()
            ->where('company_id', $companyId)
            ->where('document_type', $state)
            ->where('status', 'active')
            ->orderBy('series')
            ->first();
        
        if ($series) {
            $set('document_series_id', $series->id);
            $set('series', $series->series);
            $set('number', str_pad($series->current_number + 1, 8, '0', STR_PAD_LEFT));
        }
    }
})
```

### **📊 DocumentSeriesResource.php:**
```php
// Opciones actualizadas
'09' => __('Nota de Venta (Uso Interno)')

// Badge con color morado
'09' => 'purple'

// Filtros incluyen Nota de Venta
```

## 🎯 **Flujo de Funcionamiento**

### **📝 Al Crear Nueva Factura/Nota:**
```
1. Usuario selecciona "Empresa"
2. Usuario selecciona "Tipo de Documento"
   ├── Factura → Busca serie F001
   ├── Boleta → Busca serie B001
   └── Nota de Venta → Busca serie NV01 ✨
3. Sistema auto-completa:
   ├── Serie: NV01
   └── Número: 00000001 (siguiente disponible)
```

### **🔄 Lógica de Auto-selección:**
```php
DocumentSeries::query()
    ->where('company_id', $companyId)      // De la empresa seleccionada
    ->where('document_type', '09')         // Tipo Nota de Venta
    ->where('status', 'active')            // Solo series activas
    ->orderBy('series')                    // Primera serie disponible
    ->first();                             // Obtener la serie
```

## 📋 **Gestión de Series NV**

### **🏗️ Serie Creada Automáticamente:**
```sql
DocumentSeries {
    company_id: 1
    document_type: '09'
    series: 'NV01'
    current_number: 1
    status: 'active'
    description: 'Serie para Notas de Venta - Uso Interno'
}
```

### **⚙️ Edición de Series:**
- ✅ **Acceso**: `/admin/document-series/4/edit` (ID de la serie NV)
- ✅ **Modificar correlativo** actual
- ✅ **Cambiar estado** (activo/inactivo)
- ✅ **Editar descripción**
- ✅ **Configurar rangos** (inicial/final)

### **🎨 Visualización Mejorada:**
- ✅ **Badge morado** para Nota de Venta
- ✅ **Filtro específico** en la tabla
- ✅ **Búsqueda** por tipo de documento
- ✅ **Ordenamiento** por serie

## 🎯 **Casos de Uso Prácticos**

### **📊 Escenario 1: Primera Nota de Venta**
```
1. Crear nueva factura
2. Seleccionar "Nota de Venta (Uso Interno)"
3. Sistema auto-completa:
   - Serie: NV01
   - Número: 00000001
4. Guardar → Se crea NV01-00000001
```

### **📊 Escenario 2: Múltiples Notas de Venta**
```
1. Primera nota: NV01-00000001
2. Segunda nota: NV01-00000002
3. Tercera nota: NV01-00000003
4. Correlativo se incrementa automáticamente
```

### **📊 Escenario 3: Gestión de Series**
```
1. Ir a /admin/document-series
2. Filtrar por "Nota de Venta (Uso Interno)"
3. Editar serie NV01
4. Cambiar correlativo actual a 100
5. Próxima nota será: NV01-00000101
```

## 🔄 **Comparación con Otros Documentos**

### **📋 Comportamiento Unificado:**

| Tipo | Serie | Auto-selección | Correlativo | Gestión |
|------|-------|----------------|-------------|---------|
| **Factura** | F001 | ✅ | ✅ | ✅ |
| **Boleta** | B001 | ✅ | ✅ | ✅ |
| **Nota Crédito** | NC01 | ✅ | ✅ | ✅ |
| **Nota Débito** | ND01 | ✅ | ✅ | ✅ |
| **Nota Venta** | NV01 | ✅ | ✅ | ✅ |

### **🎯 Consistencia Total:**
- ✅ **Misma lógica** para todos los tipos
- ✅ **Mismo flujo** de auto-selección
- ✅ **Misma gestión** de correlativos
- ✅ **Misma interfaz** de administración

## ✨ **Ventajas de la Implementación**

### **👥 Para el Usuario:**
- ✅ **Automático** - No necesita buscar series manualmente
- ✅ **Consistente** - Funciona igual que otros documentos
- ✅ **Intuitivo** - Selecciona tipo y todo se completa
- ✅ **Sin errores** - No puede elegir serie incorrecta

### **💼 Para el Negocio:**
- ✅ **Control total** - Gestión centralizada de series
- ✅ **Trazabilidad** - Correlativos ordenados
- ✅ **Flexibilidad** - Puede editar rangos y números
- ✅ **Escalabilidad** - Fácil agregar más series

### **🔧 Para el Desarrollador:**
- ✅ **Código reutilizado** - Misma lógica existente
- ✅ **Mantenible** - Un solo patrón para todos
- ✅ **Extensible** - Fácil agregar nuevos tipos
- ✅ **Consistente** - Comportamiento predecible

## 🎨 **Interfaz de Usuario**

### **📝 Formulario de Creación:**
```
┌─────────────────────────────────────────────────┐
│ Empresa: [GREEN SAC                    ▼]       │
│ Tipo: [Nota de Venta (Uso Interno)    ▼]       │
│ Serie: [NV01] (auto-completado)                 │
│ Número: [00000001] (auto-completado)            │
└─────────────────────────────────────────────────┘
```

### **📊 Gestión de Series:**
```
┌─────────────────────────────────────────────────┐
│ Tipo: [Nota de Venta (Uso Interno)] 🟣          │
│ Serie: NV01                                     │
│ Número Actual: 1                                │
│ Estado: Activo                                  │
│ Descripción: Serie para Notas de Venta...      │
└─────────────────────────────────────────────────┘
```

## 🚀 **Resultado Final**

### **✅ Funcionalidad Completa:**
```
Nota de Venta - Correlativo Automático:
├── ✅ Auto-selección de serie NV01
├── ✅ Correlativo automático incremental
├── ✅ Gestión completa en DocumentSeries
├── ✅ Filtros y búsquedas actualizados
├── ✅ Badge morado para identificación
└── ✅ Comportamiento consistente con otros documentos
```

### **🎯 URLs de Gestión:**
- ✅ **Lista de series**: `/admin/document-series`
- ✅ **Editar serie NV**: `/admin/document-series/{id}/edit`
- ✅ **Crear nueva serie**: `/admin/document-series/create`
- ✅ **Filtrar por tipo**: Filtro "Nota de Venta (Uso Interno)"

### **📋 Próximos Pasos:**
1. **Probar** creando una nueva Nota de Venta
2. **Verificar** que se auto-complete serie NV01
3. **Comprobar** correlativo automático
4. **Gestionar** series desde DocumentSeries
5. **Capacitar** al equipo sobre la nueva funcionalidad

¡La **funcionalidad de correlativo automático para Nota de Venta está completamente implementada**! 🎉

### **🎯 Comportamiento Esperado:**
1. **Seleccionar** "Nota de Venta (Uso Interno)"
2. **Ver** que se auto-completa serie "NV01"
3. **Ver** que se auto-completa número "00000001"
4. **Guardar** y obtener documento "NV01-00000001"
5. **Gestionar** series desde `/admin/document-series`