# Integración de Tipo de Cambio en Facturas

## Descripción
Se ha integrado la consulta automática de tipo de cambio de Factiliza en el formulario de creación de facturas cuando se selecciona la moneda Dólares (USD).

## Funcionalidad Implementada

### 🔄 Consulta Automática de Tipo de Cambio
- **Activación**: Al seleccionar moneda "Dólares ($)" en el formulario de factura
- **Campo**: "Tipo de Cambio" aparece con botón "Obtener TC"
- **Fuente**: API de Factiliza (tipo de cambio del día)
- **Valor usado**: Precio de venta (más alto que compra)

### 📋 Cómo usar:

#### 🎆 **Experiencia Mejorada - Carga Automática:**
1. **Ir a**: `/admin/invoices/create`
2. **Seleccionar**: Moneda "Dólares ($)"
3. **Auto-carga**: Tipo de cambio se carga automáticamente si existe en cache
4. **Notificación**: "TC del 2025-09-10: S/ 3.509000 (desde cache)"
5. **Opcional**: Usar botón "Obtener TC" para actualizar

#### 🔄 **Si no hay cache:**
1. **Seleccionar**: Moneda "Dólares ($)"
2. **Valor por defecto**: 1.000000
3. **Notificación**: "Use el botón para consultar el tipo de cambio actual"
4. **Hacer clic**: Botón "Obtener TC"
5. **Resultado**: Tipo de cambio consultado y guardado en cache

## Características Técnicas

### 🎯 Comportamiento del Campo
```php
// Selección de moneda con carga automática
Select::make('currency_code')
    ->live()
    ->afterStateUpdated(function ($state, $set) {
        if ($state === 'USD') {
            // Auto-cargar desde cache si existe
            self::autoLoadExchangeRateFromCache($set);
        }
    })

// Campo de tipo de cambio con botón de actualización
TextInput::make('exchange_rate')
    ->visible(fn (callable $get) => $get('currency_code') === 'USD')
    ->helperText('Se carga automáticamente si está disponible')
    ->suffixAction(
        Action::make('get_exchange_rate')
            ->label('Obtener TC')
            ->tooltip('Actualizar tipo de cambio desde Factiliza')
            ->action(function ($set) {
                self::getExchangeRateFromFactiliza($set);
            })
    )
```

### 🔧 Integración con Factiliza
- **Servicio**: `FactilizaService::consultarTipoCambio()`
- **Endpoint**: `GET /api/factiliza/tipo-cambio`
- **Valor usado**: `data.venta` (precio de venta)
- **Precisión**: 6 decimales

### 📊 Respuesta de la API
```json
{
  "success": true,
  "message": "Consulta exitosa",
  "data": {
    "fecha": "2025-09-10",
    "compra": 3.502,
    "venta": 3.509
  }
}
```

### 💰 Cálculo Automático
- **Tipo de cambio**: Se usa el precio de **venta** (3.509)
- **Ejemplo**: US$ 100.00 = S/ 350.90
- **Carga automática**: Al seleccionar USD (si hay cache)
- **Actualización manual**: Botón "Obtener TC" cuando sea necesario

## Notificaciones

### ✅ Éxito
```
Título: "Tipo de cambio actualizado"
Mensaje: "TC del 2025-09-10: S/ 3.509000"
Duración: 5 segundos
```

### ⚠️ Advertencia
```
Título: "Error al obtener tipo de cambio"
Mensaje: "No se pudo consultar el tipo de cambio"
```

### ❌ Error
```
Título: "Error de conexión"
Mensaje: "No se pudo conectar con el servicio de tipo de cambio"
```

## Validaciones y Seguridad

### 🔒 Validaciones
- **Token requerido**: Verifica que esté configurado en la empresa
- **Conexión**: Timeout de 30 segundos
- **Formato**: Valida respuesta de la API
- **Rango**: Mínimo 0.000001, máximo sin límite

### 📝 Logging
- **Éxito**: Log de consultas exitosas
- **Error**: Log de errores con detalles
- **Contexto**: Incluye fecha y valores consultados

## Comando de Prueba

Para verificar la funcionalidad:

```bash
php artisan invoice:test-exchange-rate
```

**Salida esperada**:
```
=== PRUEBA DE TIPO DE CAMBIO EN FACTURAS ===

1. Verificando configuración...
✅ Token configurado correctamente

2. Consultando tipo de cambio...
✅ Consulta exitosa:
+------------+-------------+
| Campo      | Valor       |
+------------+-------------+
| Fecha      | 2025-09-10  |
| Compra     | S/ 3.502000 |
| Venta      | S/ 3.509000 |
| Diferencia | S/ 0.007000 |
+------------+-------------+

3. Simulando integración en factura...
✅ Tipo de cambio que se establecería: 3.509
💰 Ejemplo: US$ 100 = S/ 350.90
```

## Flujo de Usuario

### 🎆 **Experiencia Mejorada (Con Cache)**
1. **Usuario crea factura** → Selecciona datos básicos
2. **Cambia moneda a USD** → ✨ **Auto-carga tipo de cambio desde cache**
3. **Ve campo completado** → TC: 3.509000 (desde cache)
4. **Ve notificación** → "TC del 2025-09-10: S/ 3.509000 (desde cache)"
5. **Continúa factura** → Sin necesidad de hacer clic
6. **Opcional** → Botón "Obtener TC" para actualizar si desea

### 🔄 **Primera Vez del Día (Sin Cache)**
1. **Usuario crea factura** → Selecciona datos básicos
2. **Cambia moneda a USD** → Campo aparece con valor por defecto
3. **Ve notificación** → "Use el botón para consultar el tipo de cambio"
4. **Hace clic "Obtener TC"** → Sistema consulta API
5. **Recibe tipo de cambio** → Campo se actualiza + guarda en cache
6. **Ve notificación** → "TC del 2025-09-10: S/ 3.509000 (desde API)"
7. **Continúa factura** → Con tipo de cambio actualizado

### 🎨 Interfaz
- **Campo visible**: Solo cuando moneda = USD
- **Carga automática**: Desde cache al seleccionar USD
- **Botón integrado**: "Obtener TC" para actualizar
- **Helper text**: "Se carga automáticamente si está disponible"
- **Feedback inmediato**: Notificaciones diferenciadas (cache/API)

## Beneficios

### 💼 Para el Negocio
- ✅ **Tipo de cambio actualizado**: Siempre el valor más reciente
- ✅ **Automatización**: Sin consulta manual
- ✅ **Precisión**: 6 decimales de precisión
- ✅ **Trazabilidad**: Fecha y hora de consulta

### 👨‍💻 Para el Usuario
- ✅ **Carga automática**: Sin clics necesarios (con cache)
- ✅ **Simplicidad**: Un clic solo cuando es necesario
- ✅ **Confiabilidad**: Fuente oficial (Factiliza)
- ✅ **Feedback**: Notificaciones claras (cache/API)
- ✅ **Integración**: Parte del flujo normal

## Configuración Requerida

### 🔑 Token de Factiliza
- **Ubicación**: `/admin/companies`
- **Campo**: `factiliza_token`
- **Requerido**: Sí, para todas las consultas

### 🌐 Conectividad
- **URL**: `https://api.factiliza.com/v1/tipocambio/info/dia`
- **Método**: GET
- **Headers**: `Authorization: Bearer <token>`
- **Timeout**: 30 segundos

## Archivos Modificados

### 📁 Código
- `app/Filament/Resources/InvoiceResource.php`: Formulario y lógica
- `app/Services/FactilizaService.php`: Servicio de consulta (ya existía)
- `app/Console/Commands/TestInvoiceExchangeRate.php`: Comando de prueba

### 📄 Documentación
- `TIPO_CAMBIO_FACTURAS.md`: Esta documentación
- `TIPO_CAMBIO_API.md`: Documentación de la API

## Próximos Pasos

### 🚀 Mejoras Futuras
- [ ] Cache de tipo de cambio por día
- [ ] Consulta automática al cambiar moneda
- [ ] Historial de tipos de cambio
- [ ] Configuración de margen personalizado

### 🔧 Mantenimiento
- [ ] Monitoreo de disponibilidad de API
- [ ] Alertas por errores frecuentes
- [ ] Backup de tipos de cambio
- [ ] Actualización automática periódica