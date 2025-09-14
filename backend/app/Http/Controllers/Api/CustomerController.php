<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerService;
use App\Services\BaseResponseService;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    use ApiResponseTrait;

    protected CustomerService $customerService;
    protected BaseResponseService $responseService;

    public function __construct(CustomerService $customerService, BaseResponseService $responseService)
    {
        $this->customerService = $customerService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of customers.
     * GET /api/v1/customers
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
                $customers = $this->customerService->getPaginatedCustomers($filters, $with, $withCount, $perPage, $sortBy, $sortOrder);
                return $this->paginatedResponse(
                    $customers,
                    'Customers retrieved successfully'
                );
            } else {
                $customers = $this->customerService->getAllCustomers($with);
                return $this->successResponse(
                    $customers,
                    'Customers retrieved successfully'
                );
            }
        });
    }

    /**
     * Store a newly created customer.
     * POST /api/v1/customers
     */
    public function store(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:customers,name',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'status' => 'required|in:active,inactive'
            ]);

            $customer = $this->customerService->createCustomer($validated);

            return $this->createdResponse(
                $customer,
                'Customer created successfully'
            );
        });
    }

    /**
     * Display the specified customer.
     * GET /api/v1/customers/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $with = $request->get('with', []);
            $customer = $this->customerService->getCustomerById($id, $with);

            if (!$customer) {
                return $this->notFoundResponse(
                    'Customer not found'
                );
            }

            return $this->successResponse(
                $customer,
                'Customer retrieved successfully'
            );
        });
    }

    /**
     * Update the specified customer.
     * PUT/PATCH /api/v1/customers/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:customers,name,' . $id,
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            $customer = $this->customerService->updateCustomer($id, $validated);

            return $this->successResponse(
                $customer,
                'Customer updated successfully'
            );
        });
    }

    /**
     * Remove the specified customer.
     * DELETE /api/v1/customers/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $this->customerService->deleteCustomer($id);

            return $this->noContentResponse(
                'Customer deleted successfully'
            );
        });
    }

    /**
     * Get customers for dropdown options.
     * GET /api/v1/customers/options
     */
    public function options(): JsonResponse
    {
        return $this->handleRequest(function () {
            $options = $this->customerService->getActiveCustomersForOptions();

            return $this->successResponse(
                $options,
                'Customer options retrieved successfully'
            );
        });
    }

    /**
     * Search customers.
     * GET /api/v1/customers/search
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

            $customers = $this->customerService->searchCustomers(
                $validated['query'],
                $with,
                $perPage
            );

            return $this->successResponse(
                $customers,
                'Customers search completed successfully'
            );
        });
    }

    /**
     * Toggle customer status.
     * PATCH /api/v1/customers/{id}/toggle-status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $customer = $this->customerService->toggleCustomerStatus($id);

            return $this->successResponse(
                $customer,
                'Customer status updated successfully'
            );
        });
    }

    /**
     * Get customer statistics.
     * GET /api/v1/customers/statistics
     */
    public function statistics(): JsonResponse
    {
        return $this->handleRequest(function () {
            $statistics = $this->customerService->getCustomerStatistics();

            return $this->successResponse(
                $statistics,
                'Customer statistics retrieved successfully'
            );
        });
    }

    /**
     * Get customers by status.
     * GET /api/v1/customers/by-status/{status}
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
            $customers = $this->customerService->getCustomersByStatus($status, $with);

            return $this->successResponse(
                $customers,
                "Customers with status '{$status}' retrieved successfully"
            );
        });
    }
}