<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'category_id', 'status', 'search', 'sku', 'barcode'
            ]);
            
            $with = ['category', 'productUnits.unit'];
            
            if ($request->boolean('paginate', true)) {
                $perPage = $request->integer('per_page', 15);
                $products = $this->productService->getPaginatedProducts($filters, $with, $perPage);
            } else {
                $products = $this->productService->getAllProducts($filters, $with);
            }

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Products retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'sku' => 'required|string|max:100|unique:products,sku',
                'barcode' => 'nullable|string|max:100|unique:products,barcode',
                'status' => 'required|in:active,inactive',
                'units' => 'nullable|array',
                'units.*.unit_id' => 'required_with:units|exists:units,id',
                'units.*.conversion_factor' => 'required_with:units|numeric|min:0.01',
                'units.*.is_base_unit' => 'required_with:units|boolean',
                'prices' => 'nullable|array',
                'prices.*.unit_id' => 'required_with:prices|exists:units,id',
                'prices.*.price_type' => 'required_with:prices|in:purchase,selling,wholesale',
                'prices.*.price' => 'required_with:prices|numeric|min:0',
                'prices.*.effective_date' => 'required_with:prices|date',
                'prices.*.end_date' => 'nullable|date|after:effective_date',
                'prices.*.status' => 'required_with:prices|in:active,inactive'
            ]);

            $product = $this->productService->createProduct($validatedData);

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Product created successfully'
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
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        try {
            $productWithDetails = $this->productService->getProductWithPricing($product->id);
            
            if (!$productWithDetails) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $productWithDetails,
                'message' => 'Product retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'category_id' => 'sometimes|exists:categories,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'sku' => 'sometimes|string|max:100|unique:products,sku,' . $product->id,
                'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
                'status' => 'sometimes|in:active,inactive',
                'units' => 'nullable|array',
                'units.*.unit_id' => 'required_with:units|exists:units,id',
                'units.*.conversion_factor' => 'required_with:units|numeric|min:0.01',
                'units.*.is_base_unit' => 'required_with:units|boolean',
                'prices' => 'nullable|array',
                'prices.*.unit_id' => 'required_with:prices|exists:units,id',
                'prices.*.price_type' => 'required_with:prices|in:purchase,selling,wholesale',
                'prices.*.price' => 'required_with:prices|numeric|min:0',
                'prices.*.effective_date' => 'required_with:prices|date',
                'prices.*.end_date' => 'nullable|date|after:effective_date',
                'prices.*.status' => 'required_with:prices|in:active,inactive'
            ]);

            $updatedProduct = $this->productService->updateProduct($product, $validatedData);

            return response()->json([
                'success' => true,
                'data' => $updatedProduct,
                'message' => 'Product updated successfully'
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
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->productService->deleteProduct($product);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            $query = $request->input('query');
            $limit = $request->integer('limit', 10);
            $with = ['category', 'productUnits.unit'];

            $products = $this->productService->searchProducts($query, $with, $limit);

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Search completed successfully'
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
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products by category.
     */
    public function byCategory(Request $request, int $categoryId): JsonResponse
    {
        try {
            $with = ['category', 'productUnits.unit'];
            $products = $this->productService->getProductsByCategory($categoryId, $with);

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Products retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products by category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products.
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $threshold = $request->integer('threshold', 10);
            $products = $this->productService->getLowStockProducts($threshold);

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Low stock products retrieved successfully'
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
     * Get product statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $statistics = $this->productService->getProductStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Product statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle product status.
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        try {
            $updatedProduct = $this->productService->toggleProductStatus($product);

            return response()->json([
                'success' => true,
                'data' => $updatedProduct,
                'message' => 'Product status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a product.
     */
    public function duplicate(Request $request, Product $product): JsonResponse
    {
        try {
            $overrides = $request->validate([
                'name' => 'nullable|string|max:255',
                'sku' => 'nullable|string|max:100|unique:products,sku',
                'barcode' => 'nullable|string|max:100|unique:products,barcode'
            ]);

            $duplicatedProduct = $this->productService->duplicateProduct($product, $overrides);

            return response()->json([
                'success' => true,
                'data' => $duplicatedProduct,
                'message' => 'Product duplicated successfully'
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
                'message' => 'Failed to duplicate product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
