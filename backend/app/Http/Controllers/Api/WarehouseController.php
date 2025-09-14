<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WarehouseService;
use App\Services\BaseResponseService;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WarehouseController extends Controller
{
    use ApiResponseTrait;

    protected WarehouseService $warehouseService;
    protected BaseResponseService $responseService;

    public function __construct(WarehouseService $warehouseService, BaseResponseService $responseService)
    {
        $this->warehouseService = $warehouseService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of warehouses.
     * GET /api/v1/warehouses
     */
    public function index(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $filters = $request->only(['status', 'search']);
            $with = $request->get('with', []);
            $perPage = $request->get('per_page', 15);
            $paginate = $request->boolean('paginate', true);
            $withCount = $request->get('with_count', []);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if ($paginate) {
                $warehouses = $this->warehouseService->getPaginatedWarehouses($filters, $with, $withCount, $perPage, $sortBy, $sortOrder);
                return $this->paginatedResponse(
                    $warehouses,
                    'Warehouses retrieved successfully'
                );
            } else {
                $warehouses = $this->warehouseService->getAllWarehouses($with);
                return $this->successResponse(
                    $warehouses,
                    'Warehouses retrieved successfully'
                );
            }
        });
    }

    /**
     * Store a newly created warehouse.
     * POST /api/v1/warehouses
     */
    public function store(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:warehouses,name',
                'code' => 'nullable|string|max:50|unique:warehouses,code',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'manager_name' => 'nullable|string|max:255',
                'status' => 'required|in:active,inactive'
            ]);

            $warehouse = $this->warehouseService->createWarehouse($validated);

            return $this->createdResponse(
                $warehouse,
                'Warehouse created successfully'
            );
        });
    }

    /**
     * Display the specified warehouse.
     * GET /api/v1/warehouses/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $with = $request->get('with', []);
            $warehouse = $this->warehouseService->getWarehouseById($id, $with);

            if (!$warehouse) {
                return $this->notFoundResponse(
                    'Warehouse not found'
                );
            }

            return $this->successResponse(
                $warehouse,
                'Warehouse retrieved successfully'
            );
        });
    }

    /**
     * Update the specified warehouse.
     * PUT/PATCH /api/v1/warehouses/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:warehouses,name,' . $id,
                'code' => 'sometimes|nullable|string|max:50|unique:warehouses,code,' . $id,
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'manager_name' => 'nullable|string|max:255',
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $warehouse = $this->warehouseService->updateWarehouse($id, $validated);

            return $this->successResponse(
                $warehouse,
                'Warehouse updated successfully'
            );
        });
    }

    /**
     * Remove the specified warehouse.
     * DELETE /api/v1/warehouses/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $this->warehouseService->deleteWarehouse($id);

            return $this->noContentResponse(
                'Warehouse deleted successfully'
            );
        });
    }

    /**
     * Get warehouses for dropdown options.
     * GET /api/v1/warehouses/options
     */
    public function options(): JsonResponse
    {
        return $this->handleRequest(function () {
            $options = $this->warehouseService->getActiveWarehousesForOptions();

            return $this->successResponse(
                $options,
                'Warehouse options retrieved successfully'
            );
        });
    }

    /**
     * Search warehouses.
     * GET /api/v1/warehouses/search
     */
    public function search(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'query' => 'required|string|min:1',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $with = $request->get('with', []);
            $perPage = $validated['per_page'] ?? 15;

            $warehouses = $this->warehouseService->searchWarehouses(
                $validated['query'],
                $with,
                $perPage
            );

            return $this->successResponse(
                $warehouses,
                'Warehouses search completed successfully'
            );
        });
    }

    /**
     * Toggle warehouse status.
     * PATCH /api/v1/warehouses/{id}/toggle-status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $warehouse = $this->warehouseService->toggleWarehouseStatus($id);

            return $this->successResponse(
                $warehouse,
                'Warehouse status updated successfully'
            );
        });
    }

    /**
     * Get warehouse statistics.
     * GET /api/v1/warehouses/statistics
     */
    public function statistics(): JsonResponse
    {
        return $this->handleRequest(function () {
            $statistics = $this->warehouseService->getWarehouseStatistics();

            return $this->successResponse(
                $statistics,
                'Warehouse statistics retrieved successfully'
            );
        });
    }

    /**
     * Get warehouses by status.
     * GET /api/v1/warehouses/by-status/{status}
     */
    public function byStatus(Request $request, string $status): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $status) {
            if (!in_array($status, ['active', 'inactive'])) {
                return $this->badRequestResponse(
                    'Invalid status. Must be active or inactive.'
                );
            }

            $with = $request->get('with', []);
            $warehouses = $this->warehouseService->getWarehousesByStatus($status, $with);

            return $this->successResponse(
                $warehouses,
                "Warehouses with status '{$status}' retrieved successfully"
            );
        });
    }

    /**
     * Generate unique warehouse code.
     * POST /api/v1/warehouses/generate-code
     */
    public function generateCode(): JsonResponse
    {
        return $this->handleRequest(function () {
            $code = $this->warehouseService->generateUniqueCode();

            return $this->successResponse(
                ['code' => $code],
                'Warehouse code generated successfully'
            );
        });
    }
}