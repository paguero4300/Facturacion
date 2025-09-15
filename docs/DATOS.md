# Definiciones Básicas para Entender la Facturación Electrónica en Perú

## 📋 Conceptos Fundamentales

### 1. Facturación Electrónica
Sistema donde los comprobantes de pago (facturas, boletas, notas) se emiten, transmiten y almacenan en formato digital (XML) en lugar de papel. Es obligatorio en Perú para la mayoría de empresas.

### 2. SUNAT (Superintendencia Nacional de Aduanas y Administración Tributaria)
**Qué es:** Entidad gubernamental peruana que administra los impuestos.

**Su rol:** Recibe, valida y autoriza los comprobantes electrónicos.

💡 **Importante:** SUNAT NO firma los documentos, solo los verifica.

### 3. XML (eXtensible Markup Language)
**Qué es:** Formato estándar para los comprobantes electrónicos.

**Característica:** Es legible tanto por humanos como por máquinas.

**Contiene:** Toda la información de la factura (emisor, cliente, productos, impuestos).

### 4. Firma Digital
**Qué es:** Sello electrónico que garantiza:

- **Autenticidad:** Quien firma es quien dice ser.
- **Integridad:** El documento no ha sido alterado.
- **No repudio:** El emisor no puede negar haberlo emitido.

**Se realiza:** Con un Certificado Digital.

### 5. Certificado Digital Tributario
**Qué es:** Archivo digital (como una "identificación electrónica") que permite firmar documentos.

**Emisor:** SUNAT (gratuito) o Entidades de Certificación autorizadas (pagadas).

**Vigencia:** 2 años (SUNAT) o 1-3 años (comerciales).

### 6. PSE (Proveedor de Servicios Electrónicos)
**Qué es:** Empresa autorizada por SUNAT para intermediar en el proceso de facturación.

**Ejemplos:** QPOS, otros proveedores.

**Ventaja:** Ellos se encargan de la firma digital y comunicación técnica con SUNAT.

💡 **Clave:** Al usar un PSE, NO necesitas gestionar tu propio Certificado Digital.

### 7. CDR (Constancia de Recepción)
**Qué es:** Respuesta de SUNAT que confirma si un comprobante fue:

- ✅ **Aceptado** (Código 0)
- ❌ **Rechazado** (Código 2000-3999)
- ⚠️ **Observado** (Aceptado con observaciones)

**Formato:** Archivo ZIP que contiene un XML con la respuesta.

### 8. Greenter
**Qué es:** Librería de código abierto (PHP) que ayuda a generar comprobantes electrónicos en el formato XML que exige SUNAT.

**Su función:** Crear la estructura correcta del comprobante, NO firmarlo.

💡 **Importante:** Greenter puede trabajar independientemente o con un PSE.

## 🔄 Flujo Simplificado con PSE (QPOS)

```
1. Tu Sistema → Genera XML (con Greenter)
2. Tu Sistema → Envía XML a PSE (QPOS) vía API
3. PSE → Firma digitalmente el XML
4. PSE → Envía XML firmado a SUNAT
5. SUNAT → Valida y responde con CDR
6. PSE → Recibe CDR de SUNAT
7. PSE → Te devuelve la respuesta (éxito/error)
```

## 🆚 Comparación: Emisión Directa vs. Con PSE

| Aspecto | Emisión Directa | Con PSE (QPOS) |
|---------|----------------|----------------|
| **Certificado** | Lo gestionas tú | Lo gestiona el PSE |
| **Complejidad Técnica** | Alta | Media/Baja |
| **Conexión con SUNAT** | Tu sistema se conecta directamente | El PSE se conecta por ti |
| **Mantenimiento** | Tú te encargas | Mayormente el PSE |
| **Costo** | Certificado gratuito (SUNAT) | Pago por servicio al PSE |

## 📌 Términos Técnicos Clave

**API (Application Programming Interface):** Conjunto de reglas que permite que diferentes software se comuniquen entre sí. QPOS te ofrece una API para enviarles XML y recibir respuestas.

**Token de Acceso:** Credencial temporal que te permite usar los servicios de una API (como la de QPOS) por un tiempo limitado. Es como una "llave de sesión".

**Base64:** Método para codificar datos binarios (como un archivo XML) en texto plano. Se usa para enviar archivos a través de APIs.

**Clave SOL:** Credenciales (usuario y contraseña) que SUNAT entrega a cada contribuyente para acceder a sus servicios electrónicos. No las confundas con las credenciales de la API de tu PSE.

## ✅ Conclusión Práctica

Si usas un PSE como QPOS:

- **NO necesitas** preocuparte por obtener o renovar un Certificado Digital.
- **SÍ necesitas** las credenciales (usuario/contraseña) que QPOS te provee.
- **Tu rol** es generar el XML válido (con Greenter) y enviarlo a la API de QPOS.
- **QPOS se encarga** de lo complejo: firmar, enviar a SUNAT, recibir el CDR y devolverte la respuesta.

Estas definiciones te ayudarán a entender la documentación técnica y comunicarte mejor con los desarrolladores o con el soporte de tu PSE.

---

## 📘 Documentación API QPse

### Introducción

La documentación oficial de la plataforma QPse se encuentra publicada mediante GitBook. La página de inicio explica que la plataforma suministra una colección de endpoints REST y recomienda utilizar clientes como Postman o Insomnia para probar las solicitudes sin necesidad de escribir código.

La colección de Postman incluye variables como `{{token_acceso}}` y `{{url}}`, que deben configurarse dependiendo de si se utiliza el entorno de desarrollo (`https://demo-cpe.qpse.pe`) o de producción (`https://cpe.qpse.pe`).

### 🏢 Crear Empresa

**Descripción:** La operación Crear Empresa permite que un usuario cree nuevas empresas en la plataforma. El token que se usa en la cabecera se obtiene desde el panel de configuración; el username y el password devueltos en la respuesta se utilizarán luego para obtener un token_acceso mediante el endpoint Obtener Token.

**Endpoint:** `POST {{url}}/api/empresa/crear`

**Cabeceras:** La solicitud debe enviar las cabeceras Accept y Content-Type con valor `application/json`, y una cabecera de autorización con el token proporcionado.

**Cuerpo de la solicitud:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `ruc` | string | Número de RUC de la empresa |
| `tipo_de_plan` | string | Define el tipo de plan; 01 indica plan por comprobantes y 02 plan por empresa |

**Respuesta (200 OK):** La respuesta exitosa incluye los campos `success`, `message`, `username` y `password`, que contienen las credenciales generadas.

### 🔐 Obtener Token

**Descripción:** Este endpoint permite obtener el `token_acceso` que se utiliza para firmar y enviar documentos; la documentación indica que el tiempo de expiración del token está en segundos.

**Endpoint:** `POST {{url}}/api/auth/cpe/token`

**Cabeceras:** Se deben incluir las cabeceras Accept y Content-Type con el valor `application/json`. No se requiere cabecera de autorización porque el token se obtiene con las credenciales.

**Cuerpo de la solicitud:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `usuario` | string | Nombre de usuario obtenido al crear la empresa |
| `contraseña` | string | Contraseña obtenida al crear la empresa |

**Respuesta (200 OK):** El servicio devuelve un objeto JSON con las claves `token_acceso` y `expira_en`. El campo `expira_en` indica la validez del token (en segundos), por lo que después de ese tiempo el token deberá renovarse.

### ✍️ Firmar XML

**Descripción:** Este endpoint recibe un XML sin firmar (en formato base64) y devuelve el XML firmado con el certificado PSE. Según la documentación, este proceso es aplicable a todos los tipos de documentos electrónicos.

**Endpoint:** `POST {{url}}/api/cpe/generar`

**Cabeceras:** Deben enviarse Accept y Content-Type con valor `application/json`, y la cabecera Authorization con el `token_acceso` previamente generado.

**Cuerpo de la solicitud:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `tipo_integracion` | Number | Debe enviarse el valor 0 |
| `nombre_archivo` | String | Nombre del archivo XML |
| `contenido_archivo` | String | Contenido del XML en formato base64 |

**Respuesta (200 OK):** El ejemplo de la guía muestra que la respuesta incluye un código de estado (`estado`), el XML firmado (`xml`), un `codigo_hash` de la firma, un mensaje y un `external_id` para identificar el documento.

### 📤 Enviar XML firmado (obtener CDR)

**Descripción:** Este endpoint permite enviar el XML firmado en base64 y obtener el Comprobante de Recepción (CDR) en base64.

**Endpoint:** `POST {{url}}/api/cpe/enviar`

**Cabeceras:** Se deben incluir Accept y Content-Type con valor `application/json` y la cabecera Authorization con el `token_acceso`.

**Cuerpo de la solicitud:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `nombre_xml_firmado` | String | Nombre del archivo XML firmado |
| `contenido_xml_firmado` | String | Contenido del XML firmado en formato base64 |

**Respuesta (200 OK):** La respuesta devuelve un estado, un mensaje indicando si la factura o documento fue aceptado y un `cdr` en base64.

### 🎫 Enviar XML firmado – Obtener ticket

**Descripción:** Esta variante también envía un XML firmado pero devuelve un ticket que servirá para consultar posteriormente el estado; está destinada a resúmenes, anulaciones y guías de remisión.

**Endpoint:** `POST {{url}}/api/cpe/enviar` (es el mismo endpoint que el anterior pero se interpreta de forma asíncrona).

**Cabeceras:** Las cabeceras Accept, Content-Type y Authorization con el `token_acceso` son obligatorias.

**Cuerpo de la solicitud:** Se envían los parámetros `nombre_xml_firmado` y `contenido_xml_firmado` con la misma estructura que el envío de CDR.

**Respuesta (200 OK):** La respuesta contiene un estado, un mensaje de confirmación y el ticket generado. Este ticket se debe utilizar con el endpoint de consulta para obtener el CDR cuando esté disponible.

### 🔍 Consulta de ticket

**Descripción:** Este endpoint permite consultar el estado de un ticket y obtener el CDR correspondiente.

**Consideraciones:** La documentación advierte que el CDR obtenido a partir de resúmenes o anulaciones no está comprimido, mientras que el CDR de guías de remisión se entrega comprimido en un archivo ZIP. Conviene tener esto en cuenta para el manejo posterior del archivo.

**Endpoint:** `GET {{url}}/api/cpe/consultar/{{nombre_archivo}}`

**Cabeceras:** Se deben enviar Accept, Content-Type y Authorization con el `token_acceso`.

**Respuesta (200 OK):** La respuesta contiene el estado, un mensaje que describe la aceptación o el resultado del documento y el CDR en base64.

### 🏗️ Flujo Completo de Integración QPse

```
1. Crear Empresa → Obtener credenciales (usuario/contraseña)
2. Obtener Token → Usar credenciales para obtener token_acceso
3. Generar XML → Con Greenter, crear XML sin firmar
4. Firmar XML → Enviar XML en base64 a QPse para firmar
5. Enviar XML → Enviar XML firmado y obtener CDR o ticket
6. Consultar ticket → (Si aplica) Consultar estado con ticket
```

### ⚠️ Conclusiones y Advertencias

- La documentación proviene directamente de los archivos oficiales de GitBook de QPse
- No se dispone de estudios externos que verifiquen la calidad o seguridad de los endpoints
- La documentación no especifica márgenes de error ni muestra ejemplos de respuestas de error
- Se recomienda probar cada endpoint en un entorno de desarrollo empleando datos de prueba
- Conviene confirmar si existen versiones más recientes de estos endpoints

### 🌍 Entornos Disponibles

**Desarrollo/Demo:**
- URL: `https://demo-cpe.qpse.pe`
- Propósito: Pruebas y desarrollo

**Producción:**
- URL: `https://cpe.qpse.pe`
- Propósito: Operaciones reales con SUNAT