<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QpseTokenService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('qpse.url');
    }

    /**
     * Obtener token de acceso usando credenciales existentes en la base de datos
     * Solo obtiene el token, no configura empresa (las credenciales ya existen)
     */
    public function obtenerTokenConCredencialesExistentes(Company $company, bool $testConnection = true): array
    {
        try {
            Log::info('Obteniendo token QPse con credenciales existentes', [
                'company_id' => $company->id,
                'ruc' => $company->ruc,
                'has_credentials' => $company->hasQpseCredentials(),
                'endpoint' => $company->ose_endpoint
            ]);

            // Verificar que la empresa tiene credenciales configuradas
            if (!$company->hasQpseCredentials()) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'NO_CREDENTIALS',
                        'message' => 'La empresa no tiene credenciales QPse configuradas. Configure usuario y contraseña en la sección "QPse - Configuración"'
                    ]
                ];
            }

            // Verificar que tiene endpoint configurado
            if (empty($company->ose_endpoint)) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'NO_ENDPOINT',
                        'message' => 'Configure el endpoint QPse en la sección "QPse - Configuración"'
                    ]
                ];
            }

            // Paso 1: Obtener token de acceso
            $tokenResult = $this->refreshAccessToken($company);
            
            if (!$tokenResult['success']) {
                return $tokenResult;
            }

            // Paso 2: Probar conexión para verificar que todo funciona (opcional)
            if ($testConnection) {
                $connectionResult = $this->testConnection($company);
                
                if (!$connectionResult['success']) {
                    Log::warning('Token obtenido pero conexión falló', [
                        'company_id' => $company->id,
                        'connection_error' => $connectionResult['error'] ?? 'Error desconocido'
                    ]);
                    
                    // Si es error 401, el token es inválido
                    if (isset($connectionResult['error']['code']) && $connectionResult['error']['code'] === 401) {
                        return [
                            'success' => false,
                            'error' => [
                                'code' => 'INVALID_TOKEN',
                                'message' => 'Token obtenido pero es inválido. Verifique las credenciales QPse.'
                            ]
                        ];
                    }
                    
                    return [
                        'success' => true,
                        'warning' => true,
                        'message' => 'Token obtenido exitosamente, pero hay problemas de conexión. Verifique las credenciales.',
                        'connection_error' => $connectionResult['error']['message'] ?? 'Error de conexión',
                        'token_expires_at' => $company->qpse_token_expires_at?->toISOString(),
                        'expires_in_hours' => $company->qpse_token_expires_at?->diffInHours()
                    ];
                }
                
                // Todo exitoso con prueba de conexión
                Log::info('Token QPse obtenido y conexión verificada exitosamente', [
                    'company_id' => $company->id,
                    'token_expires_at' => $company->qpse_token_expires_at?->toISOString(),
                    'expires_in_hours' => $company->qpse_token_expires_at?->diffInHours()
                ]);

                return [
                    'success' => true,
                    'message' => 'Token QPse obtenido exitosamente y conexión verificada',
                    'data' => [
                        'connection_status' => $connectionResult['status_code'] ?? 200,
                        'token_expires_at' => $company->qpse_token_expires_at?->toISOString(),
                        'expires_in_hours' => $company->qpse_token_expires_at?->diffInHours(),
                        'endpoint' => $company->ose_endpoint,
                        'username' => $company->ose_username
                    ]
                ];
            } else {
                // Token obtenido sin prueba de conexión
                Log::info('Token QPse obtenido exitosamente (sin prueba de conexión)', [
                    'company_id' => $company->id,
                    'token_expires_at' => $company->qpse_token_expires_at?->toISOString(),
                    'expires_in_hours' => $company->qpse_token_expires_at?->diffInHours()
                ]);

                return [
                    'success' => true,
                    'message' => 'Token QPse obtenido exitosamente',
                    'data' => [
                        'token_expires_at' => $company->qpse_token_expires_at?->toISOString(),
                        'expires_in_hours' => $company->qpse_token_expires_at?->diffInHours(),
                        'endpoint' => $company->ose_endpoint,
                        'username' => $company->ose_username
                    ]
                ];
            }

        } catch (\Exception $e) {
            Log::error('Excepción obteniendo token QPse', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Error interno obteniendo token: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Obtener estado completo con opciones de acción
     */
    public function getCompleteStatus(Company $company): array
    {
        $status = $this->getQpseStatus($company);
        
        // Agregar recomendaciones de acción
        $recommendations = [];
        $actions_available = [];

        if (!$status['has_credentials']) {
            $recommendations[] = 'Configure las credenciales QPse (usuario y contraseña) en la sección "QPse - Configuración"';
        }

        if (empty($company->ose_endpoint)) {
            $recommendations[] = 'Configure el endpoint QPse en la sección "QPse - Configuración"';
        }

        if (!$status['has_access_token']) {
            $recommendations[] = 'Obtenga token de acceso';
            $actions_available[] = 'refresh_token';
        }

        if ($status['token_status'] === 'expired') {
            $recommendations[] = 'El token de acceso ha expirado, renuévelo';
            $actions_available[] = 'refresh_token';
        }

        if ($status['token_status'] === 'expires_soon') {
            $recommendations[] = 'El token expira pronto, considere renovarlo';
            $actions_available[] = 'refresh_token';
        }

        if ($status['has_credentials'] && !empty($company->ose_endpoint)) {
            if ($status['token_status'] === 'valid') {
                $recommendations[] = 'QPse está configurado correctamente';
                $actions_available[] = 'test_connection';
            } elseif ($status['token_status'] === 'expired' || $status['token_status'] === 'expires_soon') {
                // No duplicar recomendaciones, ya se agregaron arriba
            } else {
                $recommendations[] = 'Las credenciales están configuradas. Obtenga el token de acceso.';
                $actions_available[] = 'get_token';
            }
        }

        $status['recommendations'] = $recommendations;
        $status['actions_available'] = $actions_available;
        $status['overall_status'] = $this->getOverallStatus($status);

        return $status;
    }

    /**
     * Determinar estado general
     */
    private function getOverallStatus(array $status): string
    {
        if (!$status['has_credentials'] || empty($status['endpoint'])) {
            return 'needs_credentials_config';
        }

        if (!$status['has_access_token'] || $status['token_status'] === 'expired') {
            return 'needs_token_refresh';
        }

        if ($status['token_status'] === 'expires_soon') {
            return 'token_expires_soon';
        }

        if ($status['is_configured'] && $status['token_status'] === 'valid') {
            return 'fully_configured';
        }

        return 'unknown';
    }

    /**
     * Configurar empresa en QPse usando token de configuración
     */
    public function setupCompanyWithConfigToken(Company $company, string $configToken, string $planType = '01'): array
    {
        try {
            Log::info('Configurando empresa en QPse', [
                'company_id' => $company->id,
                'ruc' => $company->ruc,
                'plan_type' => $planType
            ]);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $configToken
            ])->post($this->baseUrl . '/api/empresa/crear', [
                'ruc' => $company->ruc,
                'tipo_de_plan' => $planType
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Empresa configurada exitosamente en QPse', [
                    'company_id' => $company->id,
                    'response_keys' => array_keys($data)
                ]);

                // Guardar credenciales y token de configuración
                $company->update([
                    'qpse_config_token' => $configToken,
                    'ose_username' => $data['username'] ?? null,
                    'ose_password' => $data['password'] ?? null,
                    'qpse_last_response' => $data
                ]);

                // Intentar obtener token de acceso inmediatamente
                if ($company->hasQpseCredentials()) {
                    $tokenResult = $this->refreshAccessToken($company);
                    Log::info('Resultado de obtención automática de token', [
                        'company_id' => $company->id,
                        'token_success' => $tokenResult['success'] ?? false
                    ]);
                }

                return [
                    'success' => true,
                    'message' => 'Empresa configurada exitosamente en QPse',
                    'data' => $data
                ];
            }

            $errorBody = $response->body();
            Log::error('Error configurando empresa en QPse', [
                'company_id' => $company->id,
                'status' => $response->status(),
                'error' => $errorBody
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => $response->status(),
                    'message' => 'Error al configurar empresa en QPse: ' . $errorBody
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Excepción configurando empresa en QPse', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Error interno: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Renovar token de acceso para operaciones
     */
    public function refreshAccessToken(Company $company): array
    {
        try {
            if (!$company->hasQpseCredentials()) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'NO_CREDENTIALS',
                        'message' => 'La empresa no tiene credenciales QPse configuradas'
                    ]
                ];
            }

            // Usar el endpoint de la empresa si está configurado
            $baseUrl = $company->ose_endpoint ?: $this->baseUrl;

            Log::info('Renovando token de acceso QPse', [
                'company_id' => $company->id,
                'username' => $company->ose_username,
                'endpoint' => $baseUrl
            ]);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($baseUrl . '/api/auth/cpe/token', [
                'usuario' => $company->ose_username,
                'contraseña' => $company->ose_password
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Calcular fecha de expiración (QPse suele dar tokens por 24 horas)
                $expiresIn = $data['expira_en'] ?? 86400; // Default 24 horas
                $expiresAt = Carbon::now()->addSeconds($expiresIn);

                Log::info('Token de acceso renovado exitosamente', [
                    'company_id' => $company->id,
                    'expires_at' => $expiresAt->toISOString(),
                    'expires_in_hours' => $expiresAt->diffInHours()
                ]);

                // Guardar token y fecha de expiración
                $company->update([
                    'qpse_access_token' => $data['token_acceso'],
                    'qpse_token_expires_at' => $expiresAt,
                    'qpse_last_response' => $data
                ]);

                return [
                    'success' => true,
                    'message' => 'Token de acceso renovado exitosamente',
                    'expires_at' => $expiresAt,
                    'expires_in_hours' => $expiresAt->diffInHours()
                ];
            }

            $errorBody = $response->body();
            Log::error('Error renovando token de acceso QPse', [
                'company_id' => $company->id,
                'status' => $response->status(),
                'error' => $errorBody
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => $response->status(),
                    'message' => 'Error al renovar token: ' . $errorBody
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Excepción renovando token de acceso QPse', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Error interno: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Verificar estado del token y renovar automáticamente si es necesario
     */
    public function ensureValidToken(Company $company): array
    {
        $status = $company->getQpseTokenExpirationStatus();

        switch ($status) {
            case 'no_token':
            case 'expired':
                return $this->refreshAccessToken($company);
                
            case 'expires_soon':
                Log::info('Token expira pronto, renovando preventivamente', [
                    'company_id' => $company->id,
                    'expires_at' => $company->qpse_token_expires_at
                ]);
                return $this->refreshAccessToken($company);
                
            case 'valid':
                return [
                    'success' => true,
                    'message' => 'Token válido',
                    'expires_at' => $company->qpse_token_expires_at
                ];
                
            default:
                return $this->refreshAccessToken($company);
        }
    }

    /**
     * Probar conexión con QPse usando un endpoint válido
     */
    public function testConnection(Company $company): array
    {
        try {
            // Asegurar que tenemos un token válido
            $tokenResult = $this->ensureValidToken($company);
            
            if (!$tokenResult['success']) {
                return $tokenResult;
            }

            Log::info('Probando conexión QPse', [
                'company_id' => $company->id,
                'endpoint' => $company->ose_endpoint,
                'has_token' => !empty($company->qpse_access_token)
            ]);

            // Usar el endpoint del company en lugar del config global
            $baseUrl = $company->ose_endpoint ?: $this->baseUrl;
            
            // Hacer una petición de prueba a un endpoint que sabemos que existe
            // Intentamos consultar el estado de un documento con un nombre válido pero que probablemente no exista
            $testFileName = $company->ruc . '-01-F001-00000001'; // Formato válido QPse
            
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $company->qpse_access_token
            ])->get($baseUrl . '/api/cpe/consultar/' . $testFileName);

            Log::info('Respuesta de prueba de conexión QPse', [
                'company_id' => $company->id,
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            // QPse devuelve diferentes códigos según el estado:
            // 200: Documento encontrado
            // 404: Documento no encontrado (pero conexión OK)
            // 401: Token inválido
            // 500: Error del servidor
            
            if ($response->status() === 200 || $response->status() === 404) {
                // 200 o 404 significa que la conexión funciona
                return [
                    'success' => true,
                    'message' => 'Conexión con QPse exitosa',
                    'status_code' => $response->status(),
                    'test_file' => $testFileName
                ];
            }
            
            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 401,
                        'message' => 'Token de acceso inválido o expirado. Renueve el token.'
                    ]
                ];
            }

            // Otros errores
            $responseBody = $response->body();
            $errorMessage = 'Error de conexión';
            
            // Intentar extraer mensaje de error de la respuesta JSON
            try {
                $responseData = $response->json();
                if (isset($responseData['message'])) {
                    $errorMessage = $responseData['message'];
                } elseif (isset($responseData['error'])) {
                    $errorMessage = $responseData['error'];
                }
            } catch (\Exception $e) {
                // Si no es JSON válido, usar el cuerpo completo
                $errorMessage = $responseBody ?: 'Error desconocido';
            }

            return [
                'success' => false,
                'error' => [
                    'code' => $response->status(),
                    'message' => $errorMessage
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Excepción probando conexión QPse', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => [
                    'code' => 500,
                    'message' => 'Error de conexión: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Obtener información del estado de QPse para una empresa
     */
    public function getQpseStatus(Company $company): array
    {
        $status = [
            'company_id' => $company->id,
            'ruc' => $company->ruc,
            'ose_provider' => $company->ose_provider,
            'has_config_token' => $company->hasQpseConfigToken(),
            'has_credentials' => $company->hasQpseCredentials(),
            'has_access_token' => !empty($company->qpse_access_token),
            'token_status' => $company->getQpseTokenExpirationStatus(),
            'is_configured' => $company->isQpseConfigured(),
            'endpoint' => $company->ose_endpoint
        ];

        if ($company->qpse_token_expires_at) {
            $status['token_expires_at'] = $company->qpse_token_expires_at->toISOString();
            $status['token_expires_in_hours'] = $company->qpse_token_expires_at->diffInHours(null, false);
        }

        return $status;
    }
}