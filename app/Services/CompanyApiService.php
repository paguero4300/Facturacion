<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CompanyApiService
{
    /**
     * Consultar RUC usando la API de Factiliza
     */
    public function consultRucWithFactiliza(string $ruc, ?string $token = null): array
    {
        try {
            // Validar RUC
            if (strlen($ruc) !== 11 || !is_numeric($ruc)) {
                return [
                    'success' => false,
                    'error' => 'RUC debe tener 11 dígitos numéricos'
                ];
            }

            // Cache key
            $cacheKey = "factiliza_ruc_{$ruc}";
            
            // Verificar cache (24 horas)
            if ($cachedData = Cache::get($cacheKey)) {
                Log::info('Consulta RUC desde cache', ['ruc' => $ruc]);
                return array_merge($cachedData, ['from_cache' => true]);
            }

            // Token desde parámetro o configuración
            $apiToken = $token ?: config('services.factiliza.token');
            
            if (!$apiToken) {
                return [
                    'success' => false,
                    'error' => 'Token de Factiliza no configurado'
                ];
            }

            Log::info('Consultando RUC en Factiliza', ['ruc' => $ruc]);

            // Llamada a API de Factiliza
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
            ->timeout(30)
            ->get('https://facturacion.factiliza.com/api/v1/ruc/' . $ruc);

            if ($response->successful()) {
                $data = $response->json();
                
                $result = [
                    'success' => true,
                    'data' => [
                        'ruc' => $data['ruc'] ?? $ruc,
                        'business_name' => $data['razon_social'] ?? null,
                        'commercial_name' => $data['nombre_comercial'] ?? null,
                        'address' => $data['direccion'] ?? null,
                        'district' => $data['distrito'] ?? null,
                        'province' => $data['provincia'] ?? null,
                        'department' => $data['departamento'] ?? null,
                        'ubigeo' => $data['ubigeo'] ?? null,
                        'status' => $data['estado'] ?? null,
                        'condition' => $data['condicion'] ?? null,
                        'phone' => $data['telefono'] ?? null,
                        'email' => $data['email'] ?? null,
                    ],
                    'raw_response' => $data
                ];

                // Guardar en cache por 24 horas
                Cache::put($cacheKey, $result, now()->addHours(24));
                
                Log::info('RUC consultado exitosamente', ['ruc' => $ruc, 'name' => $result['data']['business_name']]);
                
                return $result;
            } else {
                Log::warning('Error en API Factiliza', [
                    'ruc' => $ruc,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Error en consulta: ' . $response->status(),
                    'details' => $response->json()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Excepción consultando RUC', [
                'ruc' => $ruc,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Consultar RUC usando API SUNAT (alternativa gratuita)
     */
    public function consultRucWithSunat(string $ruc): array
    {
        try {
            if (strlen($ruc) !== 11 || !is_numeric($ruc)) {
                return [
                    'success' => false,
                    'error' => 'RUC debe tener 11 dígitos numéricos'
                ];
            }

            $cacheKey = "sunat_ruc_{$ruc}";
            
            if ($cachedData = Cache::get($cacheKey)) {
                return array_merge($cachedData, ['from_cache' => true]);
            }

            Log::info('Consultando RUC en API SUNAT pública', ['ruc' => $ruc]);

            // API pública de consulta RUC (puede variar)
            $response = Http::timeout(15)
                ->get('https://api.sunat.gob.pe/v1/contrib/' . $ruc);

            if ($response->successful()) {
                $data = $response->json();
                
                $result = [
                    'success' => true,
                    'data' => [
                        'ruc' => $ruc,
                        'business_name' => $data['ddp_nombre'] ?? null,
                        'address' => trim(($data['desc_domi_fiscal'] ?? '') . ' ' . ($data['desc_distrito'] ?? '')),
                        'district' => $data['desc_distrito'] ?? null,
                        'province' => $data['desc_provincia'] ?? null,
                        'department' => $data['desc_departamento'] ?? null,
                        'status' => $data['desc_estado'] ?? null,
                        'condition' => $data['desc_condicion'] ?? null,
                    ],
                    'raw_response' => $data
                ];

                Cache::put($cacheKey, $result, now()->addHours(12));
                
                return $result;
            } else {
                return [
                    'success' => false,
                    'error' => 'RUC no encontrado en SUNAT',
                    'status_code' => $response->status()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error consultando RUC en SUNAT', [
                'ruc' => $ruc,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Error de conexión con SUNAT'
            ];
        }
    }

    /**
     * Probar conexión con QPse
     */
    public function testQpseConnection(string $endpoint, string $username, string $password): array
    {
        try {
            Log::info('Probando conexión QPse', [
                'endpoint' => $endpoint, 
                'username' => $username,
                'username_length' => strlen($username),
                'password_length' => strlen($password)
            ]);

            // Validar que el endpoint tenga el formato correcto
            if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
                return [
                    'success' => false,
                    'error' => 'El endpoint no tiene un formato de URL válido: ' . $endpoint
                ];
            }

            // Validar credenciales básicas
            if (empty($username) || empty($password)) {
                return [
                    'success' => false,
                    'error' => 'Usuario y contraseña son requeridos'
                ];
            }

            // Intentar obtener token de acceso
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'QPOS-Laravel-App/1.0'
            ])
            ->timeout(30)
            ->post($endpoint . '/api/auth/cpe/token', [
                'usuario' => $username,
                'contraseña' => $password
            ]);

            Log::info('Respuesta QPse recibida', [
                'status_code' => $response->status(),
                'headers' => $response->headers(),
                'body_preview' => substr($response->body(), 0, 500)
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Conexión QPse exitosa', [
                    'endpoint' => $endpoint,
                    'response_keys' => array_keys($data ?? [])
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con QPse',
                    'data' => [
                        'token_obtained' => !empty($data['token_acceso']),
                        'expires_in' => $data['expira_en'] ?? null,
                        'response' => $data
                    ]
                ];
            } else {
                // Extraer mensaje de error específico de QPse
                $errorMessage = 'Error de autenticación QPse';
                $errorDetails = [];
                
                try {
                    $responseData = $response->json();
                    $errorDetails = $responseData;
                    
                    // Buscar mensaje de error en diferentes campos posibles
                    if (isset($responseData['message'])) {
                        $errorMessage = $responseData['message'];
                    } elseif (isset($responseData['error'])) {
                        $errorMessage = $responseData['error'];
                    } elseif (isset($responseData['mensaje'])) {
                        $errorMessage = $responseData['mensaje'];
                    } elseif (isset($responseData['descripcion'])) {
                        $errorMessage = $responseData['descripcion'];
                    }
                } catch (\Exception $e) {
                    // Si no es JSON válido, usar el cuerpo de la respuesta
                    $errorMessage = $response->body() ?: 'Error desconocido de QPse';
                }

                Log::warning('Error conectando con QPse', [
                    'endpoint' => $endpoint,
                    'username' => $username,
                    'status' => $response->status(),
                    'error_message' => $errorMessage,
                    'full_response' => $response->body()
                ]);
                
                // Proporcionar mensajes más específicos según el código de estado
                $specificError = match($response->status()) {
                    401 => 'Credenciales inválidas. Verifique usuario y contraseña.',
                    403 => 'Acceso denegado. Su cuenta puede estar desactivada.',
                    404 => 'Endpoint no encontrado. Verifique la URL del servicio.',
                    422 => 'Datos de entrada inválidos. Verifique el formato de las credenciales.',
                    500 => 'Error interno del servidor QPse. Intente más tarde.',
                    503 => 'Servicio QPse no disponible temporalmente.',
                    default => $errorMessage
                };
                
                return [
                    'success' => false,
                    'error' => $specificError,
                    'details' => $errorDetails,
                    'status_code' => $response->status(),
                    'raw_response' => $response->body()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Excepción probando conexión QPse', [
                'endpoint' => $endpoint,
                'username' => $username,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Proporcionar mensajes más específicos según el tipo de excepción
            $errorMessage = 'Error de conexión: ' . $e->getMessage();
            
            if (str_contains($e->getMessage(), 'timeout')) {
                $errorMessage = 'Tiempo de espera agotado. Verifique la conectividad de red y el endpoint.';
            } elseif (str_contains($e->getMessage(), 'Connection refused')) {
                $errorMessage = 'Conexión rechazada. Verifique que el endpoint esté correcto y el servicio esté disponible.';
            } elseif (str_contains($e->getMessage(), 'Could not resolve host')) {
                $errorMessage = 'No se pudo resolver el host. Verifique la URL del endpoint.';
            }
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'exception_type' => get_class($e)
            ];
        }
    }

    /**
     * Probar API de Factiliza
     */
    public function testFactilizaApi(string $token): array
    {
        try {
            Log::info('Probando API Factiliza');

            // Probar con un RUC conocido (SUNAT)
            $testRuc = '20131312955'; // RUC de SUNAT para prueba
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
            ->timeout(20)
            ->get('https://facturacion.factiliza.com/api/v1/ruc/' . $testRuc);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'message' => 'API Factiliza funcionando correctamente',
                    'test_data' => [
                        'ruc' => $testRuc,
                        'company' => $data['razon_social'] ?? 'N/A',
                        'response_time' => $response->transferStats?->getTransferTime() ?? null
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error en API Factiliza',
                    'status_code' => $response->status(),
                    'details' => $response->json()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error probando API Factiliza', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => 'Error de conexión con Factiliza: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar datos de empresa con información de API
     */
    public function updateCompanyFromApi(Company $company, array $apiData): bool
    {
        try {
            $updateData = [];

            // Mapear datos de API a campos del modelo
            if (!empty($apiData['business_name']) && empty($company->business_name)) {
                $updateData['business_name'] = $apiData['business_name'];
            }

            if (!empty($apiData['commercial_name']) && empty($company->commercial_name)) {
                $updateData['commercial_name'] = $apiData['commercial_name'];
            }

            if (!empty($apiData['address']) && empty($company->address)) {
                $updateData['address'] = $apiData['address'];
            }

            if (!empty($apiData['district']) && empty($company->district)) {
                $updateData['district'] = $apiData['district'];
            }

            if (!empty($apiData['province']) && empty($company->province)) {
                $updateData['province'] = $apiData['province'];
            }

            if (!empty($apiData['department']) && empty($company->department)) {
                $updateData['department'] = $apiData['department'];
            }

            if (!empty($apiData['ubigeo']) && empty($company->ubigeo)) {
                $updateData['ubigeo'] = $apiData['ubigeo'];
            }

            if (!empty($apiData['phone']) && empty($company->phone)) {
                $updateData['phone'] = $apiData['phone'];
            }

            if (!empty($apiData['email']) && empty($company->email)) {
                $updateData['email'] = $apiData['email'];
            }

            if (!empty($updateData)) {
                $company->update($updateData);
                
                Log::info('Empresa actualizada con datos de API', [
                    'company_id' => $company->id,
                    'updated_fields' => array_keys($updateData)
                ]);
                
                return true;
            }

            return false; // No hay datos para actualizar

        } catch (\Exception $e) {
            Log::error('Error actualizando empresa con datos de API', [
                'company_id' => $company->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Limpiar cache de consultas RUC
     */
    public function clearRucCache(string $ruc): void
    {
        Cache::forget("factiliza_ruc_{$ruc}");
        Cache::forget("sunat_ruc_{$ruc}");
    }
}