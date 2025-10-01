<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WarehouseController extends Controller
{
    /**
     * Obtener lista de almacenes disponibles
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $warehouses = Warehouse::with('company:id,district,province,department')
                ->active()
                ->orderBy('is_default', 'desc')
                ->orderBy('name', 'asc')
                ->get(['id', 'code', 'name', 'is_default', 'company_id']);

            $data = $warehouses->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'code' => $warehouse->code,
                    'name' => $warehouse->name,
                    'is_default' => $warehouse->is_default,
                    'location' => [
                        'district' => $warehouse->company->district ?? '',
                        'province' => $warehouse->company->province ?? '',
                        'department' => $warehouse->company->department ?? '',
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Almacenes obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los almacenes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos con stock disponible en un almacén específico
     *
     * @param Warehouse $warehouse
     * @param Request $request
     * @return JsonResponse
     */
    public function products(Warehouse $warehouse, Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $page = max(1, (int) $request->get('page', 1));
            $perPage = min(50, max(1, (int) $request->get('per_page', 20)));

            // Query base para productos con stock en el almacén
            $query = Product::with(['stocks' => function($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id);
                }])
                ->whereHas('stocks', function($q) use ($warehouse) {
                    $q->where('warehouse_id', $warehouse->id);
                })
                ->where('status', 'active')
                ->where('for_sale', true);

            // Aplicar filtro de búsqueda si existe
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            // Ordenamiento: primero por disponibilidad de stock, luego por cantidad y nombre
            $products = $query->get()->sortBy(function($product) {
                $stock = $product->stocks->first();
                if (!$stock) return 9999; // Sin stock al final
                
                if ($stock->qty <= 0) return 1000 + $stock->qty; // Sin stock
                if ($stock->qty <= $stock->min_qty) return 500 + $stock->qty; // Stock bajo
                return $stock->qty * -1; // Stock disponible (orden descendente)
            });

            // Paginación manual
            $total = $products->count();
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $paginatedProducts = $products->slice($offset, $perPage);

            // Formatear datos para respuesta
            $formattedProducts = $paginatedProducts->map(function ($product) {
                $stock = $product->stocks->first();
                
                // Determinar estado del stock
                $stockStatus = 'no_stock';
                if ($stock && $stock->qty > 0) {
                    if ($stock->qty <= $stock->min_qty) {
                        $stockStatus = 'low_stock';
                    } else {
                        $stockStatus = 'available';
                    }
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'price' => number_format($product->sale_price, 2),
                    'price_raw' => $product->sale_price,
                    'image_url' => $product->hasImage() ? $product->getImageUrl() : null,
                    'stock' => [
                        'qty' => $stock ? number_format($stock->qty, 2) : '0.00',
                        'qty_raw' => $stock ? $stock->qty : 0,
                        'min_qty' => $stock ? number_format($stock->min_qty, 2) : '0.00',
                        'min_qty_raw' => $stock ? $stock->min_qty : 0,
                        'status' => $stockStatus
                    ]
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'warehouse' => [
                        'id' => $warehouse->id,
                        'name' => $warehouse->name,
                        'code' => $warehouse->code,
                        'is_default' => $warehouse->is_default
                    ],
                    'products' => $formattedProducts,
                    'pagination' => [
                        'current_page' => $page,
                        'total_pages' => $totalPages,
                        'total_products' => $total,
                        'per_page' => $perPage,
                        'has_next' => $page < $totalPages,
                        'has_prev' => $page > 1
                    ]
                ],
                'message' => 'Productos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los productos: ' . $e->getMessage()
            ], 500);
        }
    }
}