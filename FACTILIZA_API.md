# API de Factiliza - Documentación

## Descripción
APIs implementadas para consultar información de DNI y RUC usando el servicio de Factiliza.

## Configuración
El token de Factiliza debe estar configurado en la empresa desde el panel de administración (`/admin/companies`).

## Endpoints Disponibles

### 1. Estado del Servicio
**GET** `/api/factiliza/estado`

Verifica si el token está configurado correctamente.

**Respuesta:**
```json
{
  "success": true,
  "message": "Estado del servicio Factiliza",
  "data": {
    "configurado": true,
    "mensaje": "Token configurado correctamente",
    "longitud": 201,
    "inicio": "eyJhbGciOiJIUzI1NiIs..."
  }
}
```

### 2. Consulta de DNI
**GET** `/api/factiliza/dni/{dni}`

Consulta información de una persona por su DNI.

**Parámetros:**
- `dni`: Número de DNI (8 dígitos)

**Ejemplo:**
```bash
curl -X GET "http://localhost:8000/api/factiliza/dni/27427864"
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Consulta exitosa",
  "data": {
    "numero": "27427864",
    "nombres": "JOSE PEDRO",
    "apellido_paterno": "CASTILLO",
    "apellido_materno": "TERRONES",
    "nombre_completo": "CASTILLO TERRONES, JOSE PEDRO",
    "departamento": "",
    "provincia": "",
    "distrito": "",
    "direccion": "",
    "direccion_completa": "",
    "ubigeo_reniec": "",
    "ubigeo_sunat": "",
    "ubigeo": [],
    "fecha_nacimiento": "",
    "sexo": ""
  }
}
```

### 3. Consulta de RUC
**GET** `/api/factiliza/ruc/{ruc}`

Consulta información de una empresa por su RUC.

**Parámetros:**
- `ruc`: Número de RUC (11 dígitos)

**Ejemplo:**
```bash
curl -X GET "http://localhost:8000/api/factiliza/ruc/20131312955"
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Consulta exitosa",
  "data": {
    "numero": "20131312955",
    "nombre_o_razon_social": "SUPERINTENDENCIA NACIONAL DE ADUANAS Y DE ADMINISTRACION TRIBUTARIA - SUNAT",
    "tipo_contribuyente": "INSTITUCIONES PUBLICAS",
    "estado": "ACTIVO",
    "condicion": "HABIDO",
    "departamento": "LIMA",
    "provincia": "LIMA",
    "distrito": "LIMA",
    "direccion": "AV. GARCILASO DE LA VEGA NRO. 1472",
    "direccion_completa": "AV. GARCILASO DE LA VEGA NRO. 1472, LIMA - LIMA - LIMA",
    "ubigeo_sunat": "150101",
    "ubigeo": ["15", "1501", "150101"]
  }
}
```

### 4. Consulta Genérica
**POST** `/api/factiliza/consultar`

Permite consultar DNI o RUC mediante un endpoint unificado.

**Parámetros del body:**
```json
{
  "tipo": "dni|ruc",
  "numero": "27427864"
}
```

**Ejemplo:**
```bash
curl -X POST "http://localhost:8000/api/factiliza/consultar" \
  -H "Content-Type: application/json" \
  -d '{"tipo": "dni", "numero": "27427864"}'
```

## Códigos de Respuesta

- **200**: Consulta exitosa
- **400**: Error en la consulta (datos no encontrados, etc.)
- **422**: Datos de entrada inválidos
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

### Datos inválidos:
```json
{
  "success": false,
  "message": "Datos inválidos",
  "errors": {
    "dni": ["El DNI debe tener exactamente 8 dígitos"]
  },
  "data": null
}
```

### Error de consulta:
```json
{
  "success": false,
  "message": "Error en la consulta: 404",
  "data": null
}
```

## Comando de Prueba

Para probar las APIs desde la línea de comandos:

```bash
# Probar DNI
php artisan factiliza:test dni 27427864

# Probar RUC
php artisan factiliza:test ruc 20131312955
```

## Logs

Todas las consultas se registran en los logs de Laravel para auditoría y debugging.

## Seguridad

- El token se almacena de forma segura en la base de datos
- Las consultas se registran para auditoría
- Validación estricta de formatos de DNI y RUC
- Timeout de 30 segundos para evitar bloqueos