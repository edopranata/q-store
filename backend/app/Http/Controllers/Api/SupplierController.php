<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupplierService;
use App\Services\BaseResponseService;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    use ApiResponseTrait;

    protected SupplierService $supplierService;
    protected BaseResponseService $responseService;

    public function __construct(SupplierService $supplierService, BaseResponseService $responseService)
    {
        $this->supplierService = $supplierService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of suppliers.
     * GET /api/v1/suppliers
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
                $suppliers = $this->supplierService->getPaginatedSuppliers($filters, $with, $withCount, $perPage, $sortBy, $sortOrder);
                return $this->paginatedResponse(
                    $suppliers,
                    'Suppliers retrieved successfully'
                );
            } else {
                $suppliers = $this->supplierService->getAllSuppliers($with);
                return $this->successResponse(
                    $suppliers,
                    'Suppliers retrieved successfully'
                );
            }
        });
    }

    /**
     * Store a newly created supplier.
     * POST /api/v1/suppliers
     */
    public function store(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:suppliers,name',
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'status' => 'required|in:active,inactive'
            ]);

            $supplier = $this->supplierService->createSupplier($validated);

            return $this->createdResponse(
                $supplier,
                'Supplier created successfully'
            );
        });
    }

    /**
     * Display the specified supplier.
     * GET /api/v1/suppliers/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $with = $request->get('with', []);
            $supplier = $this->supplierService->getSupplierById($id, $with);

            if (!$supplier) {
                return $this->notFoundResponse(
                    'Supplier not found'
                );
            }

            return $this->successResponse(
                $supplier,
                'Supplier retrieved successfully'
            );
        });
    }

    /**
     * Update the specified supplier.
     * PUT/PATCH /api/v1/suppliers/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:suppliers,name,' . $id,
                'contact_person' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $supplier = $this->supplierService->updateSupplier($id, $validated);

            return $this->successResponse(
                $supplier,
                'Supplier updated successfully'
            );
        });
    }

    /**
     * Remove the specified supplier.
     * DELETE /api/v1/suppliers/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $this->supplierService->deleteSupplier($id);

            return $this->noContentResponse(
                'Supplier deleted successfully'
            );
        });
    }

    /**
     * Get suppliers for dropdown options.
     * GET /api/v1/suppliers/options
     */
    public function options(): JsonResponse
    {
        return $this->handleRequest(function () {
            $options = $this->supplierService->getActiveSuppliersForOptions();

            return $this->successResponse(
                $options,
                'Supplier options retrieved successfully'
            );
        });
    }

    /**
     * Search suppliers.
     * GET /api/v1/suppliers/search
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

            $suppliers = $this->supplierService->searchSuppliers(
                $validated['query'],
                $with,
                $perPage
            );

            return $this->successResponse(
                $suppliers,
                'Suppliers search completed successfully'
            );
        });
    }

    /**
     * Toggle supplier status.
     * PATCH /api/v1/suppliers/{id}/toggle-status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $supplier = $this->supplierService->toggleSupplierStatus($id);

            return $this->successResponse(
                $supplier,
                'Supplier status updated successfully'
            );
        });
    }

    /**
     * Get supplier statistics.
     * GET /api/v1/suppliers/statistics
     */
    public function statistics(): JsonResponse
    {
        return $this->handleRequest(function () {
            $statistics = $this->supplierService->getSupplierStatistics();

            return $this->successResponse(
                $statistics,
                'Supplier statistics retrieved successfully'
            );
        });
    }

    /**
     * Get suppliers by status.
     * GET /api/v1/suppliers/by-status/{status}
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
            $suppliers = $this->supplierService->getSuppliersByStatus($status, $with);

            return $this->successResponse(
                $suppliers,
                "Suppliers with status '{$status}' retrieved successfully"
            );
        });
    }
}