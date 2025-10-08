<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QpseService
{
    protected string $baseUrl;
    protected ?string $token = null;
    protected ?string $accessToken = null;
    protected ?string $username = null;
    protected ?string $password = null;

    protected ?\App\Models\Company $company = null;

    public function __construct()
    {
        $this->baseUrl = config('qpse.url');
        $this->token = config('qpse.token');
        $this->username = config('qpse.username');
        $this->password = config('qpse.password');
        
        // Log de inicialización
        Log::channel('envioqpse')->info('🚀 QpseService inicializado', [
            'base_url' => $this->baseUrl,
            'has_token' => !empty($this->token),
            'has_username' => !empty($this->username),
            'has_password' => !empty($this->password),
            'config_mode' => config('qpse.mode'),
        ]);
    }

    /**
     * Configurar empresa para usar sus credenciales específicas
     */
    public function setCompany(\App\Models\Company $company): void
    {
        Log::channel('envioqpse')->info('🏢 Configurando empresa en QpseService', [
            'company_id' => $company->id,
            'company_ruc' => $company->ruc,
            'company_name' => $company->business_name,
            'ose_provider' => $company->ose_provider,
            'has_ose_endpoint' => !empty($company->ose_endpoint),
            'has_ose_username' => !empty($company->ose_username),
            'has_ose_password' => !empty($company->ose_password),
            'has_qpse_access_token' => !empty($company->qpse_access_token),
            'token_expires_at' => $company->qpse_token_expires_at?->toISOString(),
        ]);
        
        $this->company = $company;
        
        if ($company->ose_provider === 'qpse') {
            $this->baseUrl = $company->ose_endpoint ?: config('qpse.url');
            $this->username = $company->ose_username;
            $this->password = $company->ose_password;
            $this->accessToken = $company->qpse_access_token;
            
            Log::channel('envioqpse')->info('⚙️ Credenciales de empresa aplicadas', [
                'base_url' => $this->baseUrl,
                'username' => $this->username ? 'SET' : 'NULL',
                'password' => $this->password ? 'SET' : 'NULL',
                'access_token' => $this->accessToken ? 'SET' : 'NULL',
            ]);
            
            // Si el token está expirado, limpiarlo
            if ($company->qpse_token_expires_at && $company->qpse_token_expires_at->isPast()) {
                Log::channel('envioqpse')->warning('⚠️ Token de acceso expirado, limpiando', [
                    'expired_at' => $company->qpse_token_expires_at->toISOString(),
                    'current_time' => now()->toISOString(),
                ]);
                $this->accessToken = null;
            }
        } else {
            Log::channel('envioqpse')->warning('⚠️ Empresa no configurada para QPSE', [
                'current_provider' => $company->ose_provider,
            ]);
        }
    }

    /**
     * Crear empresa en QPse (solo necesario una vez por empresa)
     */
    public function crearEmpresa(string $ruc, string $tipoPlan = '01'): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ])->post($this->baseUrl . '/api/empresa/crear', [
            'ruc' => $ruc,
            'tipo_de_plan' => $tipoPlan
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Guardar credenciales automáticamente si están disponibles
            if (isset($data['username']) && isset($data['password'])) {
                $this->username = $data['username'];
                $this->password = $data['password'];
            }
            
            return $data;
        }

        throw new \Exception('Error al crear empresa: ' . $response->body());
    }

    /**
     * Obtener token de acceso para operaciones
     */
    public function obtenerToken(): string
    {
        Log::channel('envioqpse')->info('🔐 Iniciando obtención de token de acceso', [
            'has_username' => !empty($this->username),
            'has_password' => !empty($this->password),
            'base_url' => $this->baseUrl,
            'company_id' => $this->company?->id,
        ]);
        
        if (!$this->username || !$this->password) {
            Log::channel('envioqpse')->error('❌ Credenciales QPse no configuradas', [
                'username' => $this->username ? 'SET' : 'NULL',
                'password' => $this->password ? 'SET' : 'NULL',
            ]);
            throw new \Exception('Credenciales QPse no configuradas. Ejecuta crearEmpresa() primero.');
        }
        
        $url = $this->baseUrl . '/api/auth/cpe/token';
        
        Log::channel('envioqpse')->info('🚀 Enviando petición de token', [
            'url' => $url,
            'username' => $this->username,
            'password_length' => strlen($this->password ?? ''),
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->post($url, [
            'usuario' => $this->username,
            'contraseña' => $this->password
        ]);
        
        Log::channel('envioqpse')->info('📨 Respuesta de token recibida', [
            'status_code' => $response->status(),
            'successful' => $response->successful(),
            'headers' => $response->headers(),
            'body_preview' => substr($response->body(), 0, 500),
            'full_body' => $response->body(),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            Log::channel('envioqpse')->info('✅ Token obtenido exitosamente', [
                'token_data' => $data,
                'has_token_acceso' => isset($data['token_acceso']),
                'token_length' => isset($data['token_acceso']) ? strlen($data['token_acceso']) : 0,
                'expires_in' => $data['expira_en'] ?? null,
            ]);
            
            $this->accessToken = $data['token_acceso'];
            
            // Si tenemos una empresa configurada, guardar el token
            if ($this->company) {
                $expiresIn = $data['expira_en'] ?? 86400; // Default 24 horas
                $expiresAt = \Carbon\Carbon::now()->addSeconds($expiresIn);
                
                Log::channel('envioqpse')->info('💾 Guardando token en base de datos', [
                    'company_id' => $this->company->id,
                    'expires_in_seconds' => $expiresIn,
                    'expires_at' => $expiresAt->toISOString(),
                ]);
                
                $this->company->update([
                    'qpse_access_token' => $this->accessToken,
                    'qpse_token_expires_at' => $expiresAt,
                    'qpse_last_response' => $data
                ]);
                
                Log::info('Token QPse guardado en empresa', [
                    'company_id' => $this->company->id,
                    'expires_at' => $expiresAt->toISOString()
                ]);
            }
            
            Log::info('Token QPse obtenido', [
                'expira_en' => $data['expira_en'] ?? null
            ]);
            
            return $this->accessToken;
        }
        
        Log::channel('envioqpse')->error('❌ Error al obtener token', [
            'status_code' => $response->status(),
            'error_body' => $response->body(),
            'headers' => $response->headers(),
        ]);

        throw new \Exception('Error al obtener token: ' . $response->body());
    }

    /**
     * Firmar XML con QPse
     */
    public function firmarXml(string $nombreArchivo, string $xmlContent): array
    {
        Log::channel('envioqpse')->info('✍️ Iniciando firmado de XML', [
            'archivo' => $nombreArchivo,
            'xml_length' => strlen($xmlContent),
            'has_access_token' => !empty($this->accessToken),
            'base_url' => $this->baseUrl,
        ]);
        
        if (!$this->accessToken) {
            Log::channel('envioqpse')->warning('⚠️ No hay token de acceso, obteniendo nuevo token');
            $this->obtenerToken();
        }
        
        $xmlBase64 = base64_encode($xmlContent);
        
        Log::channel('envioqpse')->info('🚀 Enviando XML para firma', [
            'archivo' => $nombreArchivo,
            'xml_base64_length' => strlen($xmlBase64),
            'access_token_length' => strlen($this->accessToken),
            'url' => $this->baseUrl . '/api/cpe/generar',
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ])->post($this->baseUrl . '/api/cpe/generar', [
            'tipo_integracion' => 0,
            'nombre_archivo' => $nombreArchivo,
            'contenido_archivo' => $xmlBase64
        ]);
        
        Log::channel('envioqpse')->info('📨 Respuesta de firmado recibida', [
            'status_code' => $response->status(),
            'successful' => $response->successful(),
            'headers' => $response->headers(),
            'body_preview' => substr($response->body(), 0, 500),
            'full_body' => $response->body(),
        ]);

        if ($response->successful()) {
            $result = $response->json();
            
            Log::channel('envioqpse')->info('✅ XML firmado exitosamente', [
                'response_data' => $result,
                'has_xml' => isset($result['xml']),
                'has_hash' => isset($result['codigo_hash']),
            ]);
            
            return $result;
        }
        
        Log::channel('envioqpse')->error('❌ Error al firmar XML', [
            'status_code' => $response->status(),
            'error_body' => $response->body(),
            'headers' => $response->headers(),
        ]);

        throw new \Exception('Error al firmar XML: ' . $response->body());
    }

    /**
     * Enviar XML firmado y obtener CDR
     */
    public function enviarXmlFirmado(string $nombreXmlFirmado, string $xmlFirmadoContent): array
    {
        if (!$this->accessToken) {
            $this->obtenerToken();
        }

        $xmlFirmadoBase64 = base64_encode($xmlFirmadoContent);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ])->post($this->baseUrl . '/api/cpe/enviar', [
            'nombre_xml_firmado' => $nombreXmlFirmado,
            'contenido_xml_firmado' => $xmlFirmadoBase64
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error al enviar XML: ' . $response->body());
    }

    /**
     * Consultar ticket (para documentos asincrónicos)
     */
    public function consultarTicket(string $nombreArchivo): array
    {
        if (!$this->accessToken) {
            $this->obtenerToken();
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ])->get($this->baseUrl . '/api/cpe/consultar/' . $nombreArchivo);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error al consultar ticket: ' . $response->body());
    }

    /**
     * Proceso completo: Firmar y enviar XML (método de conveniencia)
     */
    public function procesarDocumento(string $nombreArchivo, string $xmlContent): array
    {
        Log::channel('envioqpse')->info('🚀 ==== INICIANDO PROCESAMIENTO COMPLETO DE DOCUMENTO ====', [
            'archivo' => $nombreArchivo,
            'xml_length' => strlen($xmlContent),
            'timestamp' => now()->toISOString(),
        ]);

        try {
            // Paso 1: Firmar XML
            Log::channel('envioqpse')->info('🔄 PASO 1: Firmando XML', [
                'archivo' => $nombreArchivo
            ]);
            
            $resultadoFirma = $this->firmarXml($nombreArchivo, $xmlContent);
            
            if (!isset($resultadoFirma['xml'])) {
                Log::channel('envioqpse')->error('❌ Error: QPse no devolvió XML firmado', [
                    'resultado_firma' => $resultadoFirma,
                ]);
                throw new \Exception('Error: QPse no devolvió XML firmado');
            }

            $xmlFirmado = base64_decode($resultadoFirma['xml']);
            $nombreXmlFirmado = $nombreArchivo; // Usar el mismo nombre

            Log::channel('envioqpse')->info('✅ XML firmado exitosamente', [
                'archivo_firmado' => $nombreXmlFirmado,
                'xml_firmado_length' => strlen($xmlFirmado),
                'codigo_hash' => $resultadoFirma['codigo_hash'] ?? null
            ]);

            // Paso 2: Enviar XML firmado
            Log::channel('envioqpse')->info('🔄 PASO 2: Enviando XML firmado a SUNAT', [
                'archivo' => $nombreXmlFirmado
            ]);
            
            $resultadoEnvio = $this->enviarXmlFirmado($nombreXmlFirmado, $xmlFirmado);

            Log::channel('envioqpse')->info('✅ Documento procesado completamente', [
                'archivo' => $nombreArchivo,
                'estado_envio' => $resultadoEnvio['estado'] ?? null,
                'mensaje_envio' => $resultadoEnvio['mensaje'] ?? null,
                'resultado_completo' => $resultadoEnvio,
            ]);

            $resultado = [
                'firma' => $resultadoFirma,
                'envio' => $resultadoEnvio,
                'xml_firmado' => $xmlFirmado,
                'cdr' => isset($resultadoEnvio['cdr']) ? base64_decode($resultadoEnvio['cdr']) : null
            ];
            
            Log::channel('envioqpse')->info('🎉 ==== PROCESAMIENTO COMPLETADO EXITOSAMENTE ====', [
                'archivo' => $nombreArchivo,
                'tiene_cdr' => isset($resultado['cdr']),
                'estado_final' => $resultadoEnvio['estado'] ?? 'DESCONOCIDO',
            ]);
            
            return $resultado;
            
        } catch (\Exception $e) {
            Log::channel('envioqpse')->error('❌ ==== ERROR EN PROCESAMIENTO DE DOCUMENTO ====', [
                'archivo' => $nombreArchivo,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Configurar credenciales manualmente
     */
    public function setCredenciales(string $username, string $password): void
    {
        $this->username = $username;
        $this->password = $password;
        $this->accessToken = null; // Resetear token para forzar renovación
    }

    /**
     * Verificar si las credenciales están configuradas
     */
    public function tieneCredenciales(): bool
    {
        return !empty($this->username) && !empty($this->password);
    }
}