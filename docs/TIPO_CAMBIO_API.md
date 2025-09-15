# API de Tipo de Cambio - Factiliza

## Descripción
Endpoint implementado para consultar el tipo de cambio del día usando el servicio de Factiliza.

## Configuración
El token de Factiliza debe estar configurado en la empresa desde el panel de administración (`/admin/companies`).

## Endpoint Disponible

### Consulta de Tipo de Cambio
**GET** `/api/factiliza/tipo-cambio`

Obtiene el tipo de cambio del día actual.

**Ejemplo:**
```bash
curl -X GET "http://localhost:8000/api/factiliza/tipo-cambio"
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Consulta exitosa",
  "data": {
    "fecha": "2024-04-11",
    "compra": 3.773,
    "venta": 3.781
  }
}
```

## Códigos de Respuesta

- **200**: Consulta exitosa
- **400**: Error en la consulta (servicio no disponible, etc.)
- **503**: Servicio no configurado (token no encontrado)

## Respuestas de Error

### Token no configurado:
```json
{
  "success": false,
  "message": "Servicio no configurado. Token de Factiliza no encontrado.",
  "data": null
}
```

### Error de consulta:
```json
{
  "success": false,
  "message": "Error en la consulta: 400",
  "data": null
}
```

## Comando de Prueba

Para probar la API desde la línea de comandos:

```bash
php artisan factiliza:test-tipo-cambio
```

## Logs

Todas las consultas se registran en los logs de Laravel para auditoría y debugging.

## Integración

### Servicio
El endpoint utiliza el método `consultarTipoCambio()` del `FactilizaService`.

### URL de API Externa
```
https://api.factiliza.com/v1/tipocambio/info/dia
```

### Headers Requeridos
```
Authorization: Bearer <token>
Accept: application/json
```

## Uso en Aplicación

### JavaScript/Frontend
```javascript
fetch('/api/factiliza/tipo-cambio')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Tipo de cambio:', data.data);
      console.log('Compra:', data.data.compra);
      console.log('Venta:', data.data.venta);
    }
  });
```

### PHP/Backend
```php
use App\Services\FactilizaService;

$factilizaService = app(FactilizaService::class);
$tipoCambio = $factilizaService->consultarTipoCambio();

if ($tipoCambio['success']) {
    $fecha = $tipoCambio['data']['fecha'];
    $compra = $tipoCambio['data']['compra'];
    $venta = $tipoCambio['data']['venta'];
}
```

## Características

- ✅ **Validación de token**: Verifica que esté configurado
- ✅ **Manejo de errores**: Respuestas estructuradas
- ✅ **Logging**: Registro de todas las consultas
- ✅ **Timeout**: 30 segundos para evitar bloqueos
- ✅ **Formato consistente**: Misma estructura que otros endpoints