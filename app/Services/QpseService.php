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
    }

    /**
     * Configurar empresa para usar sus credenciales específicas
     */
    public function setCompany(\App\Models\Company $company): void
    {
        $this->company = $company;
        
        if ($company->ose_provider === 'qpse') {
            $this->baseUrl = $company->ose_endpoint ?: config('qpse.url');
            $this->username = $company->ose_username;
            $this->password = $company->ose_password;
            $this->accessToken = $company->qpse_access_token;
            
            // Si el token está expirado, limpiarlo
            if ($company->qpse_token_expires_at && $company->qpse_token_expires_at->isPast()) {
                $this->accessToken = null;
            }
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
        if (!$this->username || !$this->password) {
            throw new \Exception('Credenciales QPse no configuradas. Ejecuta crearEmpresa() primero.');
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/api/auth/cpe/token', [
            'usuario' => $this->username,
            'contraseña' => $this->password
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $this->accessToken = $data['token_acceso'];
            
            // Si tenemos una empresa configurada, guardar el token
            if ($this->company) {
                $expiresIn = $data['expira_en'] ?? 86400; // Default 24 horas
                $expiresAt = \Carbon\Carbon::now()->addSeconds($expiresIn);
                
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

        throw new \Exception('Error al obtener token: ' . $response->body());
    }

    /**
     * Firmar XML con QPse
     */
    public function firmarXml(string $nombreArchivo, string $xmlContent): array
    {
        if (!$this->accessToken) {
            $this->obtenerToken();
        }

        $xmlBase64 = base64_encode($xmlContent);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ])->post($this->baseUrl . '/api/cpe/generar', [
            'tipo_integracion' => 0,
            'nombre_archivo' => $nombreArchivo,
            'contenido_archivo' => $xmlBase64
        ]);

        if ($response->successful()) {
            return $response->json();
        }

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
        Log::info('Iniciando procesamiento de documento QPse', [
            'archivo' => $nombreArchivo
        ]);

        // Paso 1: Firmar XML
        $resultadoFirma = $this->firmarXml($nombreArchivo, $xmlContent);
        
        if (!isset($resultadoFirma['xml'])) {
            throw new \Exception('Error: QPse no devolvió XML firmado');
        }

        $xmlFirmado = base64_decode($resultadoFirma['xml']);
        $nombreXmlFirmado = $nombreArchivo; // Usar el mismo nombre

        Log::info('XML firmado exitosamente por QPse', [
            'archivo_firmado' => $nombreXmlFirmado,
            'codigo_hash' => $resultadoFirma['codigo_hash'] ?? null
        ]);

        // Paso 2: Enviar XML firmado
        $resultadoEnvio = $this->enviarXmlFirmado($nombreXmlFirmado, $xmlFirmado);

        Log::info('Documento procesado por QPse', [
            'estado' => $resultadoEnvio['estado'] ?? null,
            'mensaje' => $resultadoEnvio['mensaje'] ?? null
        ]);

        return [
            'firma' => $resultadoFirma,
            'envio' => $resultadoEnvio,
            'xml_firmado' => $xmlFirmado,
            'cdr' => isset($resultadoEnvio['cdr']) ? base64_decode($resultadoEnvio['cdr']) : null
        ];
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