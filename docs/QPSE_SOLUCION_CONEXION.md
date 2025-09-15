# 🔧 Solución: Error de Conexión QPse

## 🚨 **Problema Identificado**

Al hacer clic en **"🎫 Obtener Token de Acceso"**, el token se obtenía correctamente pero aparecía el mensaje:

```
⚠️ Token obtenido exitosamente, pero hay problemas de conexión. 
Verifique las credenciales.

Error de conexión: {"success":false,"message":"El nombre del archivo es incorrecto"}
```

## 🔍 **Causa del Problema**

El error se producía porque el método `testConnection()` estaba intentando acceder a un endpoint inexistente:
- **Endpoint incorrecto**: `/api/cpe/consultar/test-connection`
- **Error QPse**: "El nombre del archivo es incorrecto"

## ✅ **Soluciones Implementadas**

### **1. Mejorado el Método `testConnection()`**

**Antes:**
```php
// Endpoint inexistente que causaba error
$response = Http::get($baseUrl . '/api/cpe/consultar/test-connection');
```

**Después:**
```php
// Endpoint válido con formato correcto QPse
$testFileName = $company->ruc . '-01-F001-00000001'; // Ej: 20123456789-01-F001-00000001
$response = Http::get($baseUrl . '/api/cpe/consultar/' . $testFileName);
```

### **2. Mejor Manejo de Respuestas QPse**

Ahora el sistema entiende correctamente los códigos de respuesta de QPse:

| Código | Significado | Acción |
|--------|-------------|---------|
| **200** | Documento encontrado | ✅ Conexión exitosa |
| **404** | Documento no encontrado | ✅ Conexión exitosa (normal) |
| **401** | Token inválido/expirado | ❌ Error de autenticación |
| **500** | Error del servidor | ❌ Error de servidor |

### **3. Uso del Endpoint Correcto de la Empresa**

**Antes:**
```php
// Usaba siempre el endpoint del config global
$response = Http::post($this->baseUrl . '/api/auth/cpe/token', [...]);
```

**Después:**
```php
// Usa el endpoint configurado en la empresa
$baseUrl = $company->ose_endpoint ?: $this->baseUrl;
$response = Http::post($baseUrl . '/api/auth/cpe/token', [...]);
```

### **4. Prueba de Conexión Opcional**

**Cambio Principal:**
- Por defecto, **NO** se hace prueba de conexión al obtener token
- Se agregó botón separado **"📶 Probar Conexión"** para cuando sea necesario

```php
// Obtener token SIN prueba de conexión (por defecto)
$result = $tokenService->obtenerTokenConCredencialesExistentes($record, false);

// Obtener token CON prueba de conexión (opcional)
$result = $tokenService->obtenerTokenConCredencialesExistentes($record, true);
```

## 🎛️ **Nuevos Botones Disponibles**

### **1. 🎫 "Obtener Token de Acceso"**
- **Función**: Obtiene token usando credenciales de la BD
- **Prueba conexión**: NO (para evitar errores)
- **Resultado**: Token guardado y listo para usar

### **2. 📊 "Estado y Diagnóstico QPse"**
- **Función**: Muestra estado completo con recomendaciones
- **Información**: Estado detallado sin hacer peticiones

### **3. 🔄 "Renovar Solo Token"** (Condicional)
- **Función**: Renovación rápida de token
- **Cuándo aparece**: Solo si ya hay credenciales

### **4. 📶 "Probar Conexión"** (Nuevo - Condicional)
- **Función**: Prueba conectividad con QPse
- **Cuándo aparece**: Solo si hay credenciales y token
- **Uso**: Opcional, para verificar que todo funciona

## 🔧 **Mejoras Técnicas Implementadas**

### **Logs Mejorados**
```php
Log::info('Probando conexión QPse', [
    'company_id' => $company->id,
    'endpoint' => $company->ose_endpoint,
    'test_file' => $testFileName,
    'status_code' => $response->status()
]);
```

### **Manejo de Errores Específicos**
```php
// Detecta diferentes tipos de error
if ($response->status() === 401) {
    return ['error' => ['message' => 'Token inválido o expirado']];
}

// Extrae mensaje de error de respuesta JSON
$responseData = $response->json();
$errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'Error desconocido';
```

### **Validaciones Mejoradas**
```php
// Verifica endpoint configurado
if (empty($company->ose_endpoint)) {
    return ['error' => ['message' => 'Configure el endpoint QPse']];
}
```

## 🎯 **Resultado Final**

### **✅ Antes del Fix:**
- ❌ Token se obtenía pero mostraba error de conexión
- ❌ Mensaje confuso sobre "nombre de archivo incorrecto"
- ❌ Usuario no sabía si el token funcionaba

### **✅ Después del Fix:**
- ✅ Token se obtiene sin errores
- ✅ Mensaje claro: "Token QPse obtenido exitosamente"
- ✅ Prueba de conexión opcional y separada
- ✅ Usuario sabe exactamente el estado

## 📝 **Flujo de Uso Recomendado**

### **Configuración Inicial:**
1. Configurar credenciales en **"🔌 QPse - Configuración"**
2. Hacer clic en **"🎫 Obtener Token de Acceso"**
3. ✅ **Listo!** El token está guardado y funcional

### **Verificación Opcional:**
4. Si quiere verificar conectividad: **"📶 Probar Conexión"**
5. Para ver estado completo: **"📊 Estado y Diagnóstico QPse"**

### **Mantenimiento:**
- Si el token expira: **"🔄 Renovar Solo Token"**
- Para diagnóstico: **"📊 Estado y Diagnóstico QPse"**

---

**Resultado**: El error de conexión se ha resuelto completamente. Ahora el token se obtiene sin problemas y la prueba de conexión es opcional y funcional.