# Sistema de Cache para Tipos de Cambio

## Descripción
Sistema implementado para cachear los tipos de cambio por día y evitar consultas innecesarias a la API de Factiliza, ahorrando tokens y mejorando el rendimiento.

## Características Principales

### 💾 Cache Inteligente
- **Almacenamiento**: Base de datos (tabla `exchange_rates`)
- **Duración**: Por día (un registro por fecha)
- **Reutilización**: Múltiples consultas del mismo día usan el cache
- **Ahorro**: Evita consumo innecesario de tokens de API

### 🔄 Funcionamiento Automático
- **Primera consulta del día**: Consulta API y guarda en cache
- **Consultas posteriores**: Usa datos del cache
- **Actualización**: Solo cuando se fuerza o no existe cache
- **Limpieza**: Automática de registros antiguos

## Estructura de la Base de Datos

### Tabla `exchange_rates`
```sql
CREATE TABLE exchange_rates (
    id BIGINT PRIMARY KEY,
    date DATE UNIQUE,                    -- Fecha del tipo de cambio
    buy_rate DECIMAL(10,6),             -- Tipo de cambio compra
    sell_rate DECIMAL(10,6),            -- Tipo de cambio venta
    source VARCHAR(50) DEFAULT 'factiliza', -- Fuente del dato
    raw_data JSON,                      -- Datos originales de la API
    fetched_at TIMESTAMP,               -- Cuándo se consultó
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Modelo ExchangeRate

### 🎯 Métodos Principales
```php
// Obtener tipo de cambio para una fecha
ExchangeRate::getForDate('2025-09-10')

// Verificar si existe para hoy
ExchangeRate::existsForToday()

// Crear o actualizar
ExchangeRate::createOrUpdate($data, $date)

// Limpiar registros antiguos
ExchangeRate::cleanOldRates(30)

// Obtener estadísticas
ExchangeRate::getStats(7)
```

## Servicio FactilizaService Actualizado

### 🔧 Método Principal
```php
public function consultarTipoCambio(?string $fecha = null, bool $forceRefresh = false): array
```

### 📋 Parámetros
- **$fecha**: Fecha específica (opcional, por defecto hoy)
- **$forceRefresh**: Forzar consulta a API (opcional, por defecto false)

### 🔄 Flujo de Funcionamiento
1. **Verificar cache**: Si existe y no se fuerza, devolver cache
2. **Consultar API**: Si no hay cache o se fuerza actualización
3. **Guardar cache**: Almacenar resultado para futuras consultas
4. **Devolver resultado**: Con indicador de fuente (cache/API)

### 📊 Respuesta Mejorada
```json
{
  "success": true,
  "message": "Consulta exitosa (desde cache)",
  "data": {
    "fecha": "2025-09-10",
    "compra": 3.502,
    "venta": 3.509,
    "cached": true,
    "fetched_at": "2025-09-10 01:12:00"
  }
}
```

## Comandos de Gestión

### 📋 Comando Principal
```bash
php artisan exchange-rate:manage {action}
```

### 🎯 Acciones Disponibles

#### 1. Obtener Tipo de Cambio
```bash
# Para hoy
php artisan exchange-rate:manage fetch

# Para fecha específica
php artisan exchange-rate:manage fetch --date=2025-09-10

# Forzar actualización
php artisan exchange-rate:manage fetch --force
```

#### 2. Ver Estadísticas
```bash
php artisan exchange-rate:manage stats
```
**Muestra**:
- Número de registros
- Promedios, mínimos y máximos
- Último registro
- Disponibilidad para hoy

#### 3. Limpiar Cache Antiguo
```bash
# Mantener últimos 30 días (por defecto)
php artisan exchange-rate:manage clean

# Mantener últimos 15 días
php artisan exchange-rate:manage clean --days=15
```

#### 4. Actualizar Hoy
```bash
php artisan exchange-rate:manage refresh
```

## Integración en Facturas

### 🔄 Comportamiento Actualizado
- **Primera consulta del día**: Consulta API y guarda cache
- **Consultas posteriores**: Usa cache (sin consumir tokens)
- **Notificación**: Indica si viene de cache o API
- **Rendimiento**: Respuesta instantánea desde cache

### 📱 Experiencia de Usuario
```
Primera vez del día:
"TC del 2025-09-10: S/ 3.509000 (desde API)"

Consultas posteriores:
"TC del 2025-09-10: S/ 3.509000 (desde cache)"
```

## Beneficios del Sistema

### 💰 Ahorro de Tokens
- **Problema anterior**: Cada factura = 1 consulta API
- **Solución actual**: 1 consulta API por día
- **Ahorro**: 95%+ en consultas repetidas

### ⚡ Rendimiento
- **Cache**: Respuesta instantánea
- **API**: Solo cuando es necesario
- **Disponibilidad**: Funciona aunque API esté lenta

### 📊 Trazabilidad
- **Historial**: Tipos de cambio por día
- **Auditoría**: Cuándo se consultó cada dato
- **Estadísticas**: Tendencias y análisis

## Comandos de Prueba

### 🧪 Verificar Funcionamiento
```bash
# Probar API y cache
php artisan factiliza:test-tipo-cambio

# Ver estadísticas
php artisan exchange-rate:manage stats

# Probar integración en facturas
php artisan invoice:test-exchange-rate
```

### 📋 Salida Esperada
```
=== PRUEBA DE API TIPO DE CAMBIO FACTILIZA ===

1. Verificando configuración...
✅ Token configurado correctamente

2. Consultando tipo de cambio del día...
✅ Consulta exitosa:
+------------+-------------+
| Campo      | Valor       |
+------------+-------------+
| Fecha      | 2025-09-10  |
| Compra     | S/ 3.502000 |
| Venta      | S/ 3.509000 |
| Diferencia | S/ 0.007000 |
| Fuente     | API         |
| Consultado | Ahora       |
+------------+-------------+

3. Probando cache (segunda consulta)...
✅ Cache funcionando correctamente
```

## Mantenimiento Automático

### 🗑️ Limpieza Automática
- **Frecuencia**: Recomendado diario
- **Retención**: 30 días por defecto
- **Comando**: `exchange-rate:manage clean`

### 📅 Programación Sugerida
```bash
# En crontab para limpieza automática
0 2 * * * php /path/to/artisan exchange-rate:manage clean --days=30
```

### 🔄 Actualización Proactiva
```bash
# Obtener tipo de cambio al inicio del día
0 8 * * * php /path/to/artisan exchange-rate:manage fetch
```

## Monitoreo y Alertas

### 📊 Métricas Importantes
- **Cache hit rate**: % de consultas desde cache
- **Registros almacenados**: Número de días en cache
- **Última actualización**: Cuándo se consultó por última vez

### 🚨 Alertas Recomendadas
- **Sin cache para hoy**: Verificar conectividad API
- **Errores frecuentes**: Revisar token y configuración
- **Cache muy antiguo**: Forzar actualización

## Archivos Modificados

### 📁 Nuevos Archivos
- `database/migrations/2025_09_10_010942_create_exchange_rates_table.php`
- `app/Models/ExchangeRate.php`
- `app/Console/Commands/ManageExchangeRateCache.php`
- `CACHE_TIPO_CAMBIO.md`

### 📝 Archivos Actualizados
- `app/Services/FactilizaService.php`: Lógica de cache
- `app/Console/Commands/TestTipoCambioApi.php`: Pruebas de cache
- `app/Filament/Resources/InvoiceResource.php`: Integración existente

## Próximos Pasos

### 🚀 Mejoras Futuras
- [ ] Dashboard de estadísticas en admin
- [ ] Alertas automáticas por email
- [ ] API para consultar historial
- [ ] Exportación de datos históricos
- [ ] Integración con otros proveedores de TC

### 🔧 Optimizaciones
- [ ] Cache en Redis para mayor velocidad
- [ ] Compresión de datos históricos
- [ ] Backup automático de cache
- [ ] Sincronización entre servidores