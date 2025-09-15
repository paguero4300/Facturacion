<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FactilizaService
{
    private string $baseUrl = 'https://api.factiliza.com/pe/v1';
    private string $exchangeRateUrl = 'https://api.factiliza.com/v1';
    private ?string $token;

    public function __construct()
    {
        // Obtener el token de la empresa activa
        $company = Company::active()->first();
        $this->token = $company?->factiliza_token;
    }

    /**
     * Consultar información de DNI
     */
    public function consultarDni(string $dni): array
    {
        if (!$this->token) {
            return [
                'success' => false,
                'message' => 'Token de Factiliza no configurado',
                'data' => null
            ];
        }

        if (!$this->validarDni($dni)) {
            return [
                'success' => false,
                'message' => 'DNI inválido. Debe tener 8 dígitos',
                'data' => null
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->timeout(30)->get("{$this->baseUrl}/dni/info/{$dni}");

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Consulta DNI exitosa', [
                    'dni' => $dni,
                    'response' => $data
                ]);

                return [
                    'success' => true,
                    'message' => 'Consulta exitosa',
                    'data' => $this->formatearDatosDni($data['data'] ?? [])
                ];
            } else {
                Log::error('Error en consulta DNI', [
                    'dni' => $dni,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Error en la consulta: ' . $response->status(),
                    'data' => null
                ];
            }
        } catch (\Exception $e) {
            Log::error('Excepción en consulta DNI', [
                'dni' => $dni,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Consultar información de RUC
     */
    public function consultarRuc(string $ruc): array
    {
        if (!$this->token) {
            return [
                'success' => false,
                'message' => 'Token de Factiliza no configurado',
                'data' => null
            ];
        }

        if (!$this->validarRuc($ruc)) {
            return [
                'success' => false,
                'message' => 'RUC inválido. Debe tener 11 dígitos',
                'data' => null
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->timeout(30)->get("{$this->baseUrl}/ruc/info/{$ruc}");

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Consulta RUC exitosa', [
                    'ruc' => $ruc,
                    'response' => $data
                ]);

                return [
                    'success' => true,
                    'message' => 'Consulta exitosa',
                    'data' => $this->formatearDatosRuc($data['data'] ?? [])
                ];
            } else {
                Log::error('Error en consulta RUC', [
                    'ruc' => $ruc,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Error en la consulta: ' . $response->status(),
                    'data' => null
                ];
            }
        } catch (\Exception $e) {
            Log::error('Excepción en consulta RUC', [
                'ruc' => $ruc,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Consultar tipo de cambio del día (con cache)
     */
    public function consultarTipoCambio(?string $fecha = null, bool $forceRefresh = false): array
    {
        // Si no se proporciona fecha, usar la fecha actual
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }

        // Verificar si ya existe en cache y no se fuerza la actualización
        if (!$forceRefresh) {
            $cached = \App\Models\ExchangeRate::getForDate($fecha);
            if ($cached) {
                Log::info('Tipo de cambio obtenido desde cache', [
                    'fecha' => $fecha,
                    'buy_rate' => $cached->buy_rate,
                    'sell_rate' => $cached->sell_rate,
                    'fetched_at' => $cached->fetched_at
                ]);

                return [
                    'success' => true,
                    'message' => 'Consulta exitosa (desde cache)',
                    'data' => [
                        'fecha' => $cached->date->format('Y-m-d'),
                        'compra' => (float) $cached->buy_rate,
                        'venta' => (float) $cached->sell_rate,
                        'cached' => true,
                        'fetched_at' => $cached->fetched_at->format('Y-m-d H:i:s')
                    ]
                ];
            }
        }

        // Si no hay cache o se fuerza la actualización, consultar API
        return $this->fetchExchangeRateFromApi($fecha);
    }

    /**
     * Consultar tipo de cambio directamente desde la API
     */
    private function fetchExchangeRateFromApi(string $fecha): array
    {
        if (!$this->token) {
            return [
                'success' => false,
                'message' => 'Token de Factiliza no configurado',
                'data' => null
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->timeout(30)->get("{$this->exchangeRateUrl}/tipocambio/info/dia", [
                'fecha' => $fecha
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $formattedData = $this->formatearDatosTipoCambio($data['data'] ?? []);
                
                // Guardar en cache
                try {
                    \App\Models\ExchangeRate::createOrUpdate($formattedData, $fecha);
                    Log::info('Tipo de cambio guardado en cache', [
                        'fecha' => $fecha,
                        'data' => $formattedData
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Error al guardar tipo de cambio en cache', [
                        'fecha' => $fecha,
                        'error' => $e->getMessage()
                    ]);
                }
                
                Log::info('Consulta tipo de cambio exitosa desde API', [
                    'fecha' => $fecha,
                    'response' => $data
                ]);

                return [
                    'success' => true,
                    'message' => 'Consulta exitosa (desde API)',
                    'data' => array_merge($formattedData, ['cached' => false])
                ];
            } else {
                Log::error('Error en consulta tipo de cambio', [
                    'fecha' => $fecha,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Error en la consulta: ' . $response->status(),
                    'data' => null
                ];
            }
        } catch (\Exception $e) {
            Log::error('Excepción en consulta tipo de cambio', [
                'fecha' => $fecha,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validar formato de DNI
     */
    private function validarDni(string $dni): bool
    {
        return preg_match('/^\d{8}$/', $dni);
    }

    /**
     * Validar formato de RUC
     */
    private function validarRuc(string $ruc): bool
    {
        return preg_match('/^\d{11}$/', $ruc);
    }

    /**
     * Formatear datos de DNI para uso interno
     */
    private function formatearDatosDni(array $data): array
    {
        return [
            'numero' => $data['numero'] ?? '',
            'nombres' => $data['nombres'] ?? '',
            'apellido_paterno' => $data['apellido_paterno'] ?? '',
            'apellido_materno' => $data['apellido_materno'] ?? '',
            'nombre_completo' => $data['nombre_completo'] ?? '',
            'departamento' => $data['departamento'] ?? '',
            'provincia' => $data['provincia'] ?? '',
            'distrito' => $data['distrito'] ?? '',
            'direccion' => $data['direccion'] ?? '',
            'direccion_completa' => $data['direccion_completa'] ?? '',
            'ubigeo_reniec' => $data['ubigeo_reniec'] ?? '',
            'ubigeo_sunat' => $data['ubigeo_sunat'] ?? '',
            'ubigeo' => $data['ubigeo'] ?? [],
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? '',
            'sexo' => $data['sexo'] ?? ''
        ];
    }

    /**
     * Formatear datos de RUC para uso interno
     */
    private function formatearDatosRuc(array $data): array
    {
        return [
            'numero' => $data['numero'] ?? '',
            'nombre_o_razon_social' => $data['nombre_o_razon_social'] ?? '',
            'tipo_contribuyente' => $data['tipo_contribuyente'] ?? '',
            'estado' => $data['estado'] ?? '',
            'condicion' => $data['condicion'] ?? '',
            'departamento' => $data['departamento'] ?? '',
            'provincia' => $data['provincia'] ?? '',
            'distrito' => $data['distrito'] ?? '',
            'direccion' => $data['direccion'] ?? '',
            'direccion_completa' => $data['direccion_completa'] ?? '',
            'ubigeo_sunat' => $data['ubigeo_sunat'] ?? '',
            'ubigeo' => $data['ubigeo'] ?? []
        ];
    }

    /**
     * Formatear datos de tipo de cambio para uso interno
     */
    private function formatearDatosTipoCambio(array $data): array
    {
        return [
            'fecha' => $data['fecha'] ?? '',
            'compra' => (float) ($data['compra'] ?? 0),
            'venta' => (float) ($data['venta'] ?? 0)
        ];
    }

    /**
     * Verificar si el token está configurado
     */
    public function tokenConfigurado(): bool
    {
        return !empty($this->token);
    }

    /**
     * Obtener estadísticas de uso del cache de tipos de cambio
     */
    public function getExchangeRateStats(): array
    {
        return \App\Models\ExchangeRate::getStats();
    }

    /**
     * Limpiar cache de tipos de cambio antiguos
     */
    public function cleanOldExchangeRates(int $daysToKeep = 30): int
    {
        return \App\Models\ExchangeRate::cleanOldRates($daysToKeep);
    }

    /**
     * Verificar si existe tipo de cambio para hoy
     */
    public function hasExchangeRateForToday(): bool
    {
        return \App\Models\ExchangeRate::existsForToday();
    }

    /**
     * Obtener información del token (sin exponer el token completo)
     */
    public function infoToken(): array
    {
        if (!$this->token) {
            return [
                'configurado' => false,
                'mensaje' => 'Token no configurado'
            ];
        }

        return [
            'configurado' => true,
            'mensaje' => 'Token configurado correctamente',
            'longitud' => strlen($this->token),
            'inicio' => substr($this->token, 0, 20) . '...'
        ];
    }
}