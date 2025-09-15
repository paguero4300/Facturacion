<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FactilizaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ExchangeRateController extends Controller
{
    protected FactilizaService $factilizaService;

    public function __construct(FactilizaService $factilizaService)
    {
        $this->factilizaService = $factilizaService;
    }

    /**
     * Obtener el tipo de cambio del día desde Factiliza
     */
    public function getExchangeRate(): JsonResponse
    {
        try {
            Log::info('Iniciando consulta de tipo de cambio');

            // Verificar que el token esté configurado
            if (!$this->factilizaService->tokenConfigurado()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de Factiliza no configurado',
                    'data' => null
                ], 400);
            }

            // Consultar tipo de cambio
            $resultado = $this->factilizaService->consultarTipoCambio();

            if ($resultado['success']) {
                Log::info('Consulta de tipo de cambio exitosa', $resultado['data']);
                
                return response()->json([
                    'success' => true,
                    'message' => $resultado['message'],
                    'data' => $resultado['data']
                ], 200);
            } else {
                Log::warning('Error en consulta de tipo de cambio', [
                    'message' => $resultado['message']
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message'],
                    'data' => null
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Excepción en endpoint de tipo de cambio', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'data' => null
            ], 500);
        }
    }

    /**
     * Obtener información del servicio (estado del token)
     */
    public function getServiceInfo(): JsonResponse
    {
        try {
            $tokenInfo = $this->factilizaService->infoToken();
            
            return response()->json([
                'success' => true,
                'message' => 'Información del servicio obtenida',
                'data' => [
                    'service' => 'Factiliza Exchange Rate API',
                    'version' => '1.0',
                    'token_info' => $tokenInfo
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error obteniendo información del servicio', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo información del servicio',
                'data' => null
            ], 500);
        }
    }
}