<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Category;
use App\Services\CategoryService;
use App\Services\BaseResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    protected CategoryService $categoryService;
    protected BaseResponseService $responseService;

    public function __construct(CategoryService $categoryService, BaseResponseService $responseService)
    {
        $this->categoryService = $categoryService;
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
                'has_products' => $request->has('has_products') ? filter_var($request->has_products, FILTER_VALIDATE_BOOLEAN) : null,
                'sort_by' => $request->get('sort_by', 'name'),
                'sort_order' => $request->get('sort_order', 'asc'),
            ];

            $perPage = min($request->get('per_page', 15), 50);
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            
            // Remove null values from filters
            $filters = array_filter($filters, function($value) {
                return $value !== null;
            });
            
            $categories = $this->categoryService->getPaginated($filters, ['products'], ['products'], $perPage, $sortBy, $sortOrder);

            return $this->responseService->paginated(
                $categories,
                'Categories retrieved successfully'
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
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:500',
                'status' => 'required|in:active,inactive'
            ]);

            $category = $this->categoryService->createCategory($validated);

            return $this->responseService->success(
                $category,
                'Category created successfully',
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
            $category = $this->categoryService->find($id, ['products']);
            
            if (!$category) {
                return $this->responseService->error(
                    'Category not found',
                    404
                );
            }

            return $this->responseService->success(
                $category,
                'Category retrieved successfully'
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
                'name' => 'required|string|max:255|unique:categories,name,' . $id,
                'description' => 'nullable|string|max:500',
                'status' => 'required|in:active,inactive'
            ]);

            $category = $this->categoryService->find($id);
            
            if (!$category) {
                return $this->responseService->error(
                    'Category not found',
                    404
                );
            }
            
            $updatedCategory = $this->categoryService->updateCategory($category, $validated);

            return $this->responseService->success(
                $updatedCategory,
                'Category updated successfully'
            );
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $category = $this->categoryService->find($id);
            
            if (!$category) {
                return $this->responseService->error(
                    'Category not found',
                    404
                );
            }
            
            $this->categoryService->deleteCategory($category);

            return $this->responseService->success(
                null,
                'Category deleted successfully'
            );
        });
    }

    /**
     * Get categories for dropdown/select options
     */
    public function options(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $categories = $this->categoryService->getActiveCategories();

            return $this->responseService->success(
                $categories,
                'Category options retrieved successfully'
            );
        });
    }

    /**
     * Get active categories
     */
    public function active(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $with = $request->get('with', ['products']);
            $categories = $this->categoryService->getActiveCategories($with);

            return $this->responseService->success(
                $categories,
                'Active categories retrieved successfully'
            );
        });
    }

    /**
     * Get category statistics
     */
    public function statistics(): JsonResponse
    {
        return $this->handleRequest(function () {
            $stats = $this->categoryService->getStatistics();

            return $this->responseService->success(
                $stats,
                'Category statistics retrieved successfully'
            );
        });
    }
}
