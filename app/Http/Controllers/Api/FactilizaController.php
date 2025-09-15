<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FactilizaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FactilizaController extends Controller
{
    private FactilizaService $factilizaService;

    public function __construct(FactilizaService $factilizaService)
    {
        $this->factilizaService = $factilizaService;
    }

    /**
     * Consultar información de DNI
     */
    public function consultarDni(Request $request, string $dni): JsonResponse
    {
        // Validar el DNI
        $validator = Validator::make(['dni' => $dni], [
            'dni' => 'required|string|size:8|regex:/^\d{8}$/'
        ], [
            'dni.required' => 'El DNI es requerido',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos',
            'dni.regex' => 'El DNI debe contener solo números'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
                'data' => null
            ], 422);
        }

        // Verificar que el token esté configurado
        if (!$this->factilizaService->tokenConfigurado()) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no configurado. Token de Factiliza no encontrado.',
                'data' => null
            ], 503);
        }

        // Realizar la consulta
        $resultado = $this->factilizaService->consultarDni($dni);

        $statusCode = $resultado['success'] ? 200 : 400;

        return response()->json($resultado, $statusCode);
    }

    /**
     * Consultar información de RUC
     */
    public function consultarRuc(Request $request, string $ruc): JsonResponse
    {
        // Validar el RUC
        $validator = Validator::make(['ruc' => $ruc], [
            'ruc' => 'required|string|size:11|regex:/^\d{11}$/'
        ], [
            'ruc.required' => 'El RUC es requerido',
            'ruc.size' => 'El RUC debe tener exactamente 11 dígitos',
            'ruc.regex' => 'El RUC debe contener solo números'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
                'data' => null
            ], 422);
        }

        // Verificar que el token esté configurado
        if (!$this->factilizaService->tokenConfigurado()) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no configurado. Token de Factiliza no encontrado.',
                'data' => null
            ], 503);
        }

        // Realizar la consulta
        $resultado = $this->factilizaService->consultarRuc($ruc);

        $statusCode = $resultado['success'] ? 200 : 400;

        return response()->json($resultado, $statusCode);
    }

    /**
     * Obtener estado del servicio
     */
    public function estado(): JsonResponse
    {
        $info = $this->factilizaService->infoToken();

        return response()->json([
            'success' => true,
            'message' => 'Estado del servicio Factiliza',
            'data' => $info
        ]);
    }

    /**
     * Consultar tipo de cambio del día
     */
    public function consultarTipoCambio(): JsonResponse
    {
        // Verificar que el token esté configurado
        if (!$this->factilizaService->tokenConfigurado()) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no configurado. Token de Factiliza no encontrado.',
                'data' => null
            ], 503);
        }

        // Realizar la consulta
        $resultado = $this->factilizaService->consultarTipoCambio();

        $statusCode = $resultado['success'] ? 200 : 400;

        return response()->json($resultado, $statusCode);
    }

    /**
     * Consulta genérica por tipo de documento
     */
    public function consultar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|in:dni,ruc',
            'numero' => 'required|string'
        ], [
            'tipo.required' => 'El tipo de documento es requerido',
            'tipo.in' => 'El tipo debe ser dni o ruc',
            'numero.required' => 'El número de documento es requerido'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors(),
                'data' => null
            ], 422);
        }

        $tipo = $request->input('tipo');
        $numero = $request->input('numero');

        if ($tipo === 'dni') {
            return $this->consultarDni($request, $numero);
        } else {
            return $this->consultarRuc($request, $numero);
        }
    }
}