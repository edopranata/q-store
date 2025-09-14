<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class ProductPriceController extends Controller
{
    /**
     * Display a listing of product prices for a specific product.
     */
    public function index(Request $request, Product $product): JsonResponse
    {
        try {
            $query = $product->productPrices()->with(['unit']);

            // Filter by price type
            if ($request->has('price_type')) {
                $query->byPriceType($request->price_type);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            } else {
                $query->active();
            }

            // Filter by effective date
            if ($request->has('effective_date')) {
                $query->effective($request->effective_date);
            }

            $productPrices = $query->orderBy('effective_date', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Product prices retrieved successfully',
                'data' => $productPrices,
                'meta' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'total_prices' => $productPrices->count(),
                    'filters' => $request->only(['price_type', 'status', 'effective_date'])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product prices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product price.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        try {
            $validated = $request->validate([
                'unit_id' => 'required|exists:units,id',
                'price_type' => 'required|in:selling,wholesale,retail',
                'price' => 'required|numeric|min:0|max:999999999999.99',
                'effective_date' => 'required|date|after_or_equal:today',
                'end_date' => 'nullable|date|after:effective_date',
                'status' => 'sometimes|in:active,inactive'
            ]);

            // Check if there's an overlapping price for the same product, unit, and price type
            $overlappingPrice = $product->productPrices()
                ->where('unit_id', $validated['unit_id'])
                ->where('price_type', $validated['price_type'])
                ->where('effective_date', '<=', $validated['effective_date'])
                ->where(function ($query) use ($validated) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', $validated['effective_date']);
                })
                ->first();

            if ($overlappingPrice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Price overlap detected',
                    'errors' => [
                        'effective_date' => ['A price for this unit and price type already exists for the specified date range']
                    ]
                ], 422);
            }

            $productPrice = $product->productPrices()->create([
                'unit_id' => $validated['unit_id'],
                'price_type' => $validated['price_type'],
                'price' => $validated['price'],
                'effective_date' => $validated['effective_date'],
                'end_date' => $validated['end_date'] ?? null,
                'status' => $validated['status'] ?? 'active'
            ]);

            $productPrice->load(['unit']);

            return response()->json([
                'success' => true,
                'message' => 'Product price created successfully',
                'data' => $productPrice
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
                'message' => 'Failed to create product price',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product price.
     */
    public function show(Product $product, ProductPrice $productPrice): JsonResponse
    {
        try {
            // Ensure the product price belongs to the specified product
            if ($productPrice->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product price not found for this product'
                ], 404);
            }

            $productPrice->load(['unit']);

            return response()->json([
                'success' => true,
                'message' => 'Product price retrieved successfully',
                'data' => $productPrice
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product price not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product price',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product price.
     */
    public function update(Request $request, Product $product, ProductPrice $productPrice): JsonResponse
    {
        try {
            // Ensure the product price belongs to the specified product
            if ($productPrice->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product price not found for this product'
                ], 404);
            }

            $validated = $request->validate([
                'unit_id' => 'sometimes|exists:units,id',
                'price_type' => 'sometimes|in:selling,wholesale,retail',
                'price' => 'sometimes|numeric|min:0|max:999999999999.99',
                'effective_date' => 'sometimes|date',
                'end_date' => 'nullable|date|after:effective_date',
                'status' => 'sometimes|in:active,inactive'
            ]);

            // Check for overlapping prices if key fields are being updated
            if (isset($validated['unit_id']) || isset($validated['price_type']) || isset($validated['effective_date'])) {
                $unitId = $validated['unit_id'] ?? $productPrice->unit_id;
                $priceType = $validated['price_type'] ?? $productPrice->price_type;
                $effectiveDate = $validated['effective_date'] ?? $productPrice->effective_date;
                $endDate = $validated['end_date'] ?? $productPrice->end_date;

                $overlappingPrice = $product->productPrices()
                    ->where('id', '!=', $productPrice->id)
                    ->where('unit_id', $unitId)
                    ->where('price_type', $priceType)
                    ->where('effective_date', '<=', $effectiveDate)
                    ->where(function ($query) use ($effectiveDate, $endDate) {
                        $query->whereNull('end_date');
                        if ($endDate) {
                            $query->orWhere('end_date', '>=', $effectiveDate);
                        }
                    })
                    ->first();

                if ($overlappingPrice) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Price overlap detected',
                        'errors' => [
                            'effective_date' => ['A price for this unit and price type already exists for the specified date range']
                        ]
                    ], 422);
                }
            }

            $productPrice->update($validated);
            $productPrice->load(['unit']);

            return response()->json([
                'success' => true,
                'message' => 'Product price updated successfully',
                'data' => $productPrice
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product price not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product price',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product price.
     */
    public function destroy(Product $product, ProductPrice $productPrice): JsonResponse
    {
        try {
            // Ensure the product price belongs to the specified product
            if ($productPrice->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product price not found for this product'
                ], 404);
            }

            // Check if this price is currently active and effective
            $isCurrentlyActive = $productPrice->status === 'active' && 
                                $productPrice->effective_date <= now()->toDateString() &&
                                ($productPrice->end_date === null || $productPrice->end_date >= now()->toDateString());

            if ($isCurrentlyActive) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete an active price that is currently effective. Please set an end date or deactivate it first.'
                ], 422);
            }

            $productPrice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product price deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product price not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product price',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current effective price for a product and unit.
     */
    public function getCurrentPrice(Request $request, Product $product): JsonResponse
    {
        try {
            $validated = $request->validate([
                'unit_id' => 'required|exists:units,id',
                'price_type' => 'sometimes|in:selling,wholesale,retail',
                'date' => 'sometimes|date'
            ]);

            $priceType = $validated['price_type'] ?? 'selling';
            $date = $validated['date'] ?? now()->toDateString();

            $currentPrice = $product->productPrices()
                ->with(['unit'])
                ->where('unit_id', $validated['unit_id'])
                ->byPriceType($priceType)
                ->active()
                ->effective($date)
                ->orderBy('effective_date', 'desc')
                ->first();

            if (!$currentPrice) {
                return response()->json([
                    'success' => false,
                    'message' => 'No effective price found for the specified criteria'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Current price retrieved successfully',
                'data' => $currentPrice,
                'meta' => [
                    'query_date' => $date,
                    'price_type' => $priceType
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
                'message' => 'Failed to retrieve current price',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
