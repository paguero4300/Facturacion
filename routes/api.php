<?php

use App\Http\Controllers\Api\FactilizaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas de Factiliza para consultas de documentos
Route::prefix('factiliza')->group(function () {
    // Estado del servicio
    Route::get('/estado', [FactilizaController::class, 'estado']);
    
    // Consulta por DNI
    Route::get('/dni/{dni}', [FactilizaController::class, 'consultarDni'])
        ->where('dni', '[0-9]{8}');
    
    // Consulta por RUC
    Route::get('/ruc/{ruc}', [FactilizaController::class, 'consultarRuc'])
        ->where('ruc', '[0-9]{11}');
    
    // Consulta tipo de cambio
    Route::get('/tipo-cambio', [FactilizaController::class, 'consultarTipoCambio']);
    
    // Consulta genérica
    Route::post('/consultar', [FactilizaController::class, 'consultar']);
});

// Rutas para el modal de productos por almacén
Route::prefix('warehouses')->group(function () {
    // Lista de almacenes disponibles
    Route::get('/', [\App\Http\Controllers\Api\WarehouseController::class, 'index']);
    
    // Productos por almacén específico
    Route::get('/{warehouse}/products', [\App\Http\Controllers\Api\WarehouseController::class, 'products'])
        ->where('warehouse', '[0-9]+');
});