# Guía de Solución de Problemas - Error "Ticket no es válido" en QPse

## Descripción del Problema

Cuando haces clic en "Probar Conexión" en la configuración de QPse, aparece el error:

```
Probando conexión...
Verificando conectividad con QPse

❌ Error de Conexión
Ticket no es válido
```

## Causas Comunes y Soluciones

### 1. **Credenciales Incorrectas** (Más Común)

**Síntomas:**
- Error "Ticket no es válido"
- Error "Credenciales inválidas"
- Código de estado HTTP 401

**Solución:**
1. Verifica que el **usuario** y **contraseña** sean correctos
2. Asegúrate de que no haya espacios extra al inicio o final
3. Verifica que las credenciales sean para el entorno correcto (demo vs producción)

**Pasos para verificar:**
```bash
# En el terminal, verifica las credenciales en la base de datos
php artisan tinker
>>> $company = App\Models\Company::first();
>>> echo "Usuario: " . $company->ose_username;
>>> echo "Endpoint: " . $company->ose_endpoint;
```

### 2. **Endpoint Incorrecto**

**Síntomas:**
- Error "Endpoint no encontrado"
- Error de conexión
- Código de estado HTTP 404

**Solución:**
Verifica que el endpoint sea correcto:

- **Demo:** `https://demo-cpe.qpse.pe`
- **Producción:** `https://cpe.qpse.pe`

### 3. **Cuenta QPse No Configurada**

**Síntomas:**
- Error "Acceso denegado"
- Error "Cuenta desactivada"
- Código de estado HTTP 403

**Solución:**
1. Contacta a QPse para verificar el estado de tu cuenta
2. Asegúrate de que tu cuenta esté activa y configurada para facturación electrónica
3. Verifica que tengas los permisos necesarios

### 4. **Problemas de Red/Conectividad**

**Síntomas:**
- Error "Tiempo de espera agotado"
- Error "Conexión rechazada"
- Error "No se pudo resolver el host"

**Solución:**
1. Verifica tu conexión a internet
2. Comprueba que no haya firewall bloqueando la conexión
3. Prueba acceder al endpoint desde el navegador

## Pasos de Diagnóstico

### Paso 1: Verificar Logs
```bash
# Ver logs de Laravel para más detalles
tail -f storage/logs/laravel.log | grep -i qpse
```

### Paso 2: Probar Conexión Manual
```bash
# Probar endpoint con curl
curl -X POST https://demo-cpe.qpse.pe/api/auth/cpe/token \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "usuario": "TU_USUARIO",
    "contraseña": "TU_CONTRASEÑA"
  }'
```

### Paso 3: Verificar Configuración en Base de Datos
```sql
-- Verificar configuración de la empresa
SELECT 
    ruc,
    business_name,
    ose_provider,
    ose_endpoint,
    ose_username,
    qpse_access_token IS NOT NULL as has_token,
    qpse_token_expires_at
FROM companies 
WHERE ose_provider = 'qpse';
```

### Paso 4: Limpiar y Renovar Token
```bash
# Limpiar token existente y obtener uno nuevo
php artisan tinker
>>> $company = App\Models\Company::first();
>>> $company->update(['qpse_access_token' => null, 'qpse_token_expires_at' => null]);
>>> $tokenService = app(\App\Services\QpseTokenService::class);
>>> $result = $tokenService->refreshAccessToken($company);
>>> print_r($result);
```

## Soluciones Avanzadas

### Actualizar Método de Conexión

Si el problema persiste, puedes probar con un método alternativo de autenticación:

```php
// En app/Services/CompanyApiService.php
// Agregar headers adicionales
$response = Http::withHeaders([
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
    'User-Agent' => 'QPOS-Laravel-App/1.0',
    'X-Requested-With' => 'XMLHttpRequest'
])
->timeout(60) // Aumentar timeout
->post($endpoint . '/api/auth/cpe/token', [
    'usuario' => $username,
    'contraseña' => $password,
    'grant_type' => 'password' // Agregar si es necesario
]);
```

### Verificar Formato de Credenciales

Algunos proveedores QPse requieren formatos específicos:

```php
// Probar con diferentes formatos
$credentials = [
    'username' => $username,  // En lugar de 'usuario'
    'password' => $password   // En lugar de 'contraseña'
];

// O con RUC incluido
$credentials = [
    'usuario' => $company->ruc . '|' . $username,
    'contraseña' => $password
];
```

## Contacto con Soporte

Si ninguna de las soluciones anteriores funciona:

1. **Recopila información:**
   - RUC de la empresa
   - Usuario QPse
   - Endpoint utilizado
   - Logs de error completos
   - Capturas de pantalla del error

2. **Contacta a QPse:**
   - Soporte técnico de QPse
   - Proporciona toda la información recopilada
   - Solicita verificación del estado de la cuenta

3. **Verifica con el proveedor:**
   - Confirma que las credenciales sean correctas
   - Verifica que el servicio esté funcionando
   - Solicita documentación actualizada de la API

## Prevención

Para evitar futuros problemas:

1. **Documenta las credenciales** correctas en un lugar seguro
2. **Configura monitoreo** de tokens para renovación automática
3. **Mantén contacto** regular con el soporte de QPse
4. **Prueba la conexión** regularmente, especialmente después de cambios

## Comandos Útiles

```bash
# Verificar estado de QPse para todas las empresas
php artisan qpse:status

# Renovar token para una empresa específica
php artisan qpse:refresh-token --ruc=20123456789

# Probar conexión desde línea de comandos
php artisan qpse:test-connection --ruc=20123456789
```

---

**Nota:** Este error es común durante la configuración inicial. La mayoría de los casos se resuelven verificando las credenciales y el endpoint correcto.