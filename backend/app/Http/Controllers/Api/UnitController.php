<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\UnitService;
use App\Services\BaseResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UnitController extends Controller
{
    use ApiResponseTrait;

    protected UnitService $unitService;
    protected BaseResponseService $responseService;

    public function __construct(UnitService $unitService, BaseResponseService $responseService)
    {
        $this->unitService = $unitService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $filters = [
                'status' => $request->get('status'),
                'search' => $request->get('search'),
                'sort_by' => $request->get('sort_by', 'name'),
                'sort_order' => $request->get('sort_order', 'asc'),
            ];

            $perPage = $request->get('per_page', 15);
            $units = $this->unitService->getPaginatedUnits($filters, [], $perPage);

            return $this->responseService->paginated(
                $units,
                'Units retrieved successfully'
            );
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'symbol' => 'required|string|max:10',
                'description' => 'nullable|string|max:500',
                'status' => 'required|in:active,inactive'
            ]);

            $unit = $this->unitService->createUnit($validated);

            return $this->responseService->success(
                $unit,
                'Unit created successfully',
                201
            );
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $unit = $this->unitService->getUnitById($id);
            
            if (!$unit) {
                return $this->responseService->error(
                    'Unit not found',
                    404
                );
            }

            return $this->responseService->success(
                $unit,
                'Unit retrieved successfully'
            );
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($request, $id) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'symbol' => 'required|string|max:10',
                'description' => 'nullable|string|max:500',
                'status' => 'required|in:active,inactive'
            ]);

            $unit = $this->unitService->updateUnit($id, $validated);

            return $this->responseService->success(
                $unit,
                'Unit updated successfully'
            );
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $this->unitService->deleteUnit($id);

            return $this->responseService->success(
                null,
                'Unit deleted successfully'
            );
        });
    }

    /**
     * Get unit options for dropdowns.
     */
    public function options(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $units = $this->unitService->getActiveUnitsForOptions();

            return $this->responseService->success(
                $units,
                'Unit options retrieved successfully'
            );
        });
    }
}