# Integración Factiliza en Formulario de Clientes

## Descripción
Se ha integrado la API de Factiliza en el formulario de creación y edición de clientes para búsqueda automática de datos por DNI y RUC.

## Funcionalidades Implementadas

### 1. Búsqueda Manual con Botón
- **Botón Consultar**: Icono de lupa con texto "Consultar" para búsqueda manual
- **DNI**: Valida 8 dígitos antes de consultar
- **RUC**: Valida 11 dígitos antes de consultar
- **Validación**: Verifica que el campo no esté vacío antes de consultar

### 2. Campos Auto-completados

#### Para DNI:
- ✅ **Razón Social**: `nombre_completo`
- ✅ **Dirección**: `direccion`
- ✅ **Distrito**: `distrito`
- ✅ **Provincia**: `provincia`
- ✅ **Departamento**: `departamento`
- ✅ **Ubigeo**: `ubigeo_sunat`

#### Para RUC:
- ✅ **Razón Social**: `nombre_o_razon_social`
- ✅ **Dirección**: `direccion`
- ✅ **Distrito**: `distrito`
- ✅ **Provincia**: `provincia`
- ✅ **Departamento**: `departamento`
- ✅ **Ubigeo**: `ubigeo_sunat`

### 3. Notificaciones
- ✅ **Éxito**: Datos encontrados y completados
- ⚠️ **Advertencia**: Documento no encontrado
- ❌ **Error**: Formato inválido o servicio no disponible

## Cómo Usar

### 1. Acceder al Formulario
```
http://qpos.test/admin/clients/create
```

### 2. Proceso de Búsqueda

#### Búsqueda Manual con Botón
1. Seleccionar **Tipo de Documento** (DNI o RUC)
2. Escribir el **Número de Documento**
3. Hacer clic en el botón **🔍 Consultar**
4. Los campos se completan automáticamente si se encuentran datos
5. Se muestra notificación de éxito o error

### 3. Validaciones
- **DNI**: Debe tener exactamente 8 dígitos numéricos
- **RUC**: Debe tener exactamente 11 dígitos numéricos
- **Token**: Debe estar configurado en la empresa

## Ejemplos de Uso

### DNI de Prueba
```
Tipo: DNI
Número: 27427864
Resultado: CASTILLO TERRONES, JOSE PEDRO
```

### RUC de Prueba
```
Tipo: RUC
Número: 20131312955
Resultado: SUPERINTENDENCIA NACIONAL DE ADUANAS Y DE ADMINISTRACION TRIBUTARIA - SUNAT
```

## Configuración Requerida

### 1. Token de Factiliza
El token debe estar configurado en:
```
/admin/companies → Editar empresa → Token API Factiliza
```

### 2. Verificar Configuración
```bash
php artisan client:test-factiliza
```

## Mensajes de Notificación

### Éxito
```
✅ Datos encontrados
Información obtenida de Factiliza para DNI/RUC
```

### Advertencia
```
⚠️ DNI/RUC no encontrado
No se encontraron datos para este documento
```

### Error
```
❌ Formato inválido
DNI debe tener 8 dígitos, RUC debe tener 11 dígitos
```

```
❌ Servicio no configurado
Token de Factiliza no encontrado
```

## Características Técnicas

### 1. Implementación
- **Componente**: `TextInput` con `suffixAction`
- **Eventos**: `live(onBlur: true)` y `afterStateUpdated`
- **Servicio**: `FactilizaService`
- **Notificaciones**: `Filament\Notifications\Notification`

### 2. Rendimiento
- **Timeout**: 30 segundos por consulta
- **Cache**: No implementado (consulta en tiempo real)
- **Validación**: Cliente y servidor

### 3. Logs
Todas las consultas se registran en:
```
storage/logs/laravel.log
```

## Solución de Problemas

### Token no configurado
```bash
# Verificar estado
php artisan factiliza:test

# Configurar en admin
/admin/companies → Token API Factiliza
```

### Servicio no responde
```bash
# Verificar conectividad
curl -H "Authorization: Bearer TOKEN" https://api.factiliza.com/pe/v1/dni/info/27427864
```

### Campos no se completan
1. Verificar que el tipo de documento coincida
2. Verificar que el número tenga la longitud correcta
3. Revisar logs para errores de API

## Próximas Mejoras

### Funcionalidades Pendientes
- [ ] Cache de consultas frecuentes
- [ ] Validación de estado del RUC (activo/inactivo)
- [ ] Integración con otros proveedores de datos
- [ ] Historial de consultas por cliente

### Optimizaciones
- [ ] Debounce para evitar consultas excesivas
- [ ] Preloader durante la búsqueda
- [ ] Validación de formato en tiempo real