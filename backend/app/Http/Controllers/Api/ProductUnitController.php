<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductUnitController extends Controller
{
    /**
     * Display a listing of product units for a specific product.
     */
    public function index(Product $product): JsonResponse
    {
        try {
            $productUnits = $product->productUnits()->with('unit')->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Product units retrieved successfully',
                'data' => $productUnits,
                'meta' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'total_units' => $productUnits->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product units',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product unit.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        try {
            $validated = $request->validate([
                'unit_id' => 'required|exists:units,id',
                'conversion_factor' => 'required|numeric|min:0.0001|max:999999.9999',
                'is_base_unit' => 'boolean'
            ]);

            // Check if unit already exists for this product
            $existingUnit = $product->productUnits()->where('unit_id', $validated['unit_id'])->first();
            if ($existingUnit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit already exists for this product',
                    'errors' => ['unit_id' => ['This unit is already assigned to the product']]
                ], 422);
            }

            // If this is set as base unit, update existing base unit
            if ($validated['is_base_unit'] ?? false) {
                $product->productUnits()->update(['is_base_unit' => false]);
            }

            $productUnit = $product->productUnits()->create([
                'unit_id' => $validated['unit_id'],
                'conversion_factor' => $validated['conversion_factor'],
                'is_base_unit' => $validated['is_base_unit'] ?? false
            ]);

            $productUnit->load('unit');

            return response()->json([
                'success' => true,
                'message' => 'Product unit created successfully',
                'data' => $productUnit
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
                'message' => 'Failed to create product unit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product unit.
     */
    public function show(Product $product, ProductUnit $productUnit): JsonResponse
    {
        try {
            // Ensure the product unit belongs to the specified product
            if ($productUnit->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product unit not found for this product'
                ], 404);
            }

            $productUnit->load('unit');

            return response()->json([
                'success' => true,
                'message' => 'Product unit retrieved successfully',
                'data' => $productUnit
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product unit not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product unit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product unit.
     */
    public function update(Request $request, Product $product, ProductUnit $productUnit): JsonResponse
    {
        try {
            // Ensure the product unit belongs to the specified product
            if ($productUnit->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product unit not found for this product'
                ], 404);
            }

            $validated = $request->validate([
                'unit_id' => 'sometimes|exists:units,id',
                'conversion_factor' => 'sometimes|numeric|min:0.0001|max:999999.9999',
                'is_base_unit' => 'sometimes|boolean'
            ]);

            // Check if unit already exists for this product (excluding current)
            if (isset($validated['unit_id'])) {
                $existingUnit = $product->productUnits()
                    ->where('unit_id', $validated['unit_id'])
                    ->where('id', '!=', $productUnit->id)
                    ->first();
                    
                if ($existingUnit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unit already exists for this product',
                        'errors' => ['unit_id' => ['This unit is already assigned to the product']]
                    ], 422);
                }
            }

            // If this is set as base unit, update existing base unit
            if (($validated['is_base_unit'] ?? false) && !$productUnit->is_base_unit) {
                $product->productUnits()->where('id', '!=', $productUnit->id)->update(['is_base_unit' => false]);
            }

            $productUnit->update($validated);
            $productUnit->load('unit');

            return response()->json([
                'success' => true,
                'message' => 'Product unit updated successfully',
                'data' => $productUnit
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
                'message' => 'Product unit not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product unit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product unit.
     */
    public function destroy(Product $product, ProductUnit $productUnit): JsonResponse
    {
        try {
            // Ensure the product unit belongs to the specified product
            if ($productUnit->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product unit not found for this product'
                ], 404);
            }

            // Prevent deletion of base unit if it's the only unit
            if ($productUnit->is_base_unit && $product->productUnits()->count() === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the only base unit for this product'
                ], 422);
            }

            $productUnit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product unit deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product unit not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product unit',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
