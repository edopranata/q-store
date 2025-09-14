<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get all inventory stocks with optional filters.
     * GET /api/v1/inventory/stocks
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'product_id',
                'warehouse_id',
                'search',
                'low_stock',
                'low_stock_threshold',
                'out_of_stock'
            ]);

            $perPage = $request->get('per_page', 15);
            $paginate = $request->boolean('paginate', true);

            if ($paginate) {
                $stocks = $this->inventoryService->getPaginatedStocks($filters, $perPage);
            } else {
                $stocks = $this->inventoryService->getAllStocks($filters);
            }

            return response()->json([
                'success' => true,
                'message' => 'Inventory stocks retrieved successfully',
                'data' => $stocks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve inventory stocks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory stocks by warehouse.
     * GET /api/v1/inventory/stocks/{warehouse_id}
     */
    public function getByWarehouse(int $warehouseId): JsonResponse
    {
        try {
            $stocks = $this->inventoryService->getStocksByWarehouse($warehouseId);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse inventory stocks retrieved successfully',
                'data' => $stocks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve warehouse inventory stocks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory stocks by product.
     * GET /api/v1/inventory/products/{product_id}/stocks
     */
    public function getByProduct(int $productId): JsonResponse
    {
        try {
            $stocks = $this->inventoryService->getStocksByProduct($productId);
            $totalStock = $this->inventoryService->getTotalStockByProduct($productId);
            $availableStock = $this->inventoryService->getAvailableStockByProduct($productId);

            return response()->json([
                'success' => true,
                'message' => 'Product inventory stocks retrieved successfully',
                'data' => [
                    'stocks' => $stocks,
                    'summary' => [
                        'total_stock' => $totalStock,
                        'available_stock' => $availableStock,
                        'reserved_stock' => $totalStock - $availableStock
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product inventory stocks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products.
     * GET /api/v1/inventory/low-stock
     */
    public function getLowStock(Request $request): JsonResponse
    {
        try {
            $threshold = $request->get('threshold', 10);
            $lowStockProducts = $this->inventoryService->getLowStockProducts($threshold);

            return response()->json([
                'success' => true,
                'message' => 'Low stock products retrieved successfully',
                'data' => [
                    'products' => $lowStockProducts,
                    'count' => $lowStockProducts->count(),
                    'threshold' => $threshold
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve low stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get out of stock products.
     * GET /api/v1/inventory/out-of-stock
     */
    public function getOutOfStock(): JsonResponse
    {
        try {
            $outOfStockProducts = $this->inventoryService->getOutOfStockProducts();

            return response()->json([
                'success' => true,
                'message' => 'Out of stock products retrieved successfully',
                'data' => [
                    'products' => $outOfStockProducts,
                    'count' => $outOfStockProducts->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve out of stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Perform stock adjustment.
     * POST /api/v1/inventory/adjustment
     */
    public function adjustment(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'adjustments' => 'required|array|min:1',
                'adjustments.*.product_id' => 'required|integer|exists:products,id',
                'adjustments.*.warehouse_id' => 'required|integer|exists:warehouses,id',
                'adjustments.*.new_quantity' => 'required|numeric|min:0',
                'adjustments.*.reason' => 'nullable|string|max:255'
            ]);

            $results = [];
            $errors = [];

            foreach ($validatedData['adjustments'] as $index => $adjustment) {
                try {
                    $stock = $this->inventoryService->adjustStock(
                        $adjustment['product_id'],
                        $adjustment['warehouse_id'],
                        $adjustment['new_quantity'],
                        $adjustment['reason'] ?? null
                    );
                    $results[] = $stock;
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'product_id' => $adjustment['product_id'],
                        'warehouse_id' => $adjustment['warehouse_id'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            $response = [
                'success' => empty($errors),
                'message' => empty($errors) 
                    ? 'Stock adjustments completed successfully' 
                    : 'Stock adjustments completed with some errors',
                'data' => [
                    'successful_adjustments' => $results,
                    'failed_adjustments' => $errors,
                    'total_processed' => count($validatedData['adjustments']),
                    'successful_count' => count($results),
                    'failed_count' => count($errors)
                ]
            ];

            $statusCode = empty($errors) ? 200 : 207; // 207 Multi-Status for partial success

            return response()->json($response, $statusCode);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform stock adjustment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add stock to inventory.
     * POST /api/v1/inventory/add-stock
     */
    public function addStock(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'warehouse_id' => 'required|integer|exists:warehouses,id',
                'quantity' => 'required|numeric|min:0.001',
                'purchase_price' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:255'
            ]);

            $stock = $this->inventoryService->addStock(
                $validatedData['product_id'],
                $validatedData['warehouse_id'],
                $validatedData['quantity'],
                $validatedData['purchase_price'] ?? null,
                $validatedData['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock added successfully',
                'data' => $stock->load(['product', 'warehouse'])
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reduce stock from inventory.
     * POST /api/v1/inventory/reduce-stock
     */
    public function reduceStock(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'warehouse_id' => 'required|integer|exists:warehouses,id',
                'quantity' => 'required|numeric|min:0.001',
                'notes' => 'nullable|string|max:255'
            ]);

            $stock = $this->inventoryService->reduceStock(
                $validatedData['product_id'],
                $validatedData['warehouse_id'],
                $validatedData['quantity'],
                $validatedData['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock reduced successfully',
                'data' => $stock->load(['product', 'warehouse'])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reduce stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reserve stock for pending transactions.
     * POST /api/v1/inventory/reserve-stock
     */
    public function reserveStock(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'warehouse_id' => 'required|integer|exists:warehouses,id',
                'quantity' => 'required|numeric|min:0.001',
                'notes' => 'nullable|string|max:255'
            ]);

            $stock = $this->inventoryService->reserveStock(
                $validatedData['product_id'],
                $validatedData['warehouse_id'],
                $validatedData['quantity'],
                $validatedData['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock reserved successfully',
                'data' => $stock->load(['product', 'warehouse'])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reserve stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Release reserved stock.
     * POST /api/v1/inventory/release-stock
     */
    public function releaseStock(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'warehouse_id' => 'required|integer|exists:warehouses,id',
                'quantity' => 'required|numeric|min:0.001',
                'notes' => 'nullable|string|max:255'
            ]);

            $stock = $this->inventoryService->releaseReservedStock(
                $validatedData['product_id'],
                $validatedData['warehouse_id'],
                $validatedData['quantity'],
                $validatedData['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Reserved stock released successfully',
                'data' => $stock->load(['product', 'warehouse'])
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to release reserved stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory valuation report.
     * GET /api/v1/inventory/valuation
     */
    public function getValuation(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['product_id', 'warehouse_id']);
            $valuation = $this->inventoryService->getInventoryValuation($filters);

            return response()->json([
                'success' => true,
                'message' => 'Inventory valuation retrieved successfully',
                'data' => $valuation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve inventory valuation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock alerts (low stock and out of stock).
     * GET /api/v1/inventory/alerts
     */
    public function getAlerts(Request $request): JsonResponse
    {
        try {
            $threshold = $request->get('threshold', 10);
            $alerts = $this->inventoryService->getStockAlerts($threshold);

            return response()->json([
                'success' => true,
                'message' => 'Stock alerts retrieved successfully',
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stock alerts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check stock availability.
     * POST /api/v1/inventory/check-availability
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.warehouse_id' => 'required|integer|exists:warehouses,id',
                'items.*.required_quantity' => 'required|numeric|min:0.001'
            ]);

            $results = [];
            foreach ($validatedData['items'] as $item) {
                $isAvailable = $this->inventoryService->hasSufficientStock(
                    $item['product_id'],
                    $item['warehouse_id'],
                    $item['required_quantity']
                );

                $stock = $this->inventoryService->getStockByProductAndWarehouse(
                    $item['product_id'],
                    $item['warehouse_id']
                );

                $results[] = [
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'required_quantity' => $item['required_quantity'],
                    'available_quantity' => $stock ? $stock->available_qty : 0,
                    'is_available' => $isAvailable
                ];
            }

            $allAvailable = collect($results)->every('is_available');

            return response()->json([
                'success' => true,
                'message' => 'Stock availability checked successfully',
                'data' => [
                    'all_available' => $allAvailable,
                    'items' => $results
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check stock availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}