<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\PricingService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductService
{
    protected ProductRepositoryInterface $productRepository;
    protected PricingService $pricingService;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        PricingService $pricingService
    ) {
        $this->productRepository = $productRepository;
        $this->pricingService = $pricingService;
    }

    /**
     * Get all products with optional filters.
     */
    public function getAllProducts(array $filters = [], array $with = []): Collection
    {
        return $this->productRepository->getAll($filters, $with);
    }

    /**
     * Get paginated products.
     */
    public function getPaginatedProducts(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getPaginated($filters, $with, $perPage);
    }

    /**
     * Find product by ID.
     */
    public function findProduct(int $id, array $with = []): ?Product
    {
        return $this->productRepository->findById($id, $with);
    }

    /**
     * Find product by SKU.
     */
    public function findProductBySku(string $sku, array $with = []): ?Product
    {
        return $this->productRepository->findBySku($sku, $with);
    }

    /**
     * Find product by barcode.
     */
    public function findProductByBarcode(string $barcode, array $with = []): ?Product
    {
        return $this->productRepository->findByBarcode($barcode, $with);
    }

    /**
     * Create a new product with validation.
     */
    public function createProduct(array $data): Product
    {
        // Validate unique constraints
        $this->validateUniqueFields($data);

        DB::beginTransaction();
        try {
            $product = $this->productRepository->create($data);
            
            // If units data is provided, create product units
            if (isset($data['units']) && is_array($data['units'])) {
                $this->createProductUnits($product, $data['units']);
            }

            // If prices data is provided, create product prices
            if (isset($data['prices']) && is_array($data['prices'])) {
                $this->createProductPrices($product, $data['prices']);
            }

            DB::commit();
            return $product->load(['category', 'productUnits.unit', 'productPrices.unit']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        // Validate unique constraints (excluding current product)
        $this->validateUniqueFields($data, $product->id);

        DB::beginTransaction();
        try {
            $updatedProduct = $this->productRepository->update($product, $data);
            
            // Handle units update if provided
            if (isset($data['units']) && is_array($data['units'])) {
                $this->updateProductUnits($updatedProduct, $data['units']);
            }

            // Handle prices update if provided
            if (isset($data['prices']) && is_array($data['prices'])) {
                $this->updateProductPrices($updatedProduct, $data['prices']);
            }

            DB::commit();
            return $updatedProduct->load(['category', 'productUnits.unit', 'productPrices.unit']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): bool
    {
        // Check if product has any transactions
        if ($this->hasTransactions($product)) {
            throw new \InvalidArgumentException('Cannot delete product with existing transactions. Consider deactivating instead.');
        }

        DB::beginTransaction();
        try {
            // Delete related data
            $product->productUnits()->delete();
            $product->productPrices()->delete();
            $product->inventoryStocks()->delete();
            
            $result = $this->productRepository->delete($product);
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Search products.
     */
    public function searchProducts(string $query, array $with = [], int $limit = 10): Collection
    {
        return $this->productRepository->search($query, $with, $limit);
    }

    /**
     * Get products by category.
     */
    public function getProductsByCategory(int $categoryId, array $with = []): Collection
    {
        return $this->productRepository->getByCategory($categoryId, $with);
    }

    /**
     * Get products with current prices.
     */
    public function getProductsWithCurrentPrices(array $filters = [], string $priceType = 'selling'): Collection
    {
        return $this->productRepository->getWithCurrentPrices($filters, $priceType);
    }

    /**
     * Get product with all pricing information.
     */
    public function getProductWithPricing(int $productId): ?Product
    {
        $product = $this->productRepository->findById($productId, [
            'category',
            'productUnits.unit',
            'productPrices.unit'
        ]);

        if (!$product) {
            return null;
        }

        // Add current prices for each unit
        $currentPrices = [];
        foreach ($product->productUnits as $productUnit) {
            $currentPrice = $this->pricingService->getCurrentPrice(
                $product,
                $productUnit->unit_id,
                'selling'
            );
            
            if ($currentPrice) {
                $currentPrices[] = $currentPrice;
            }
        }

        $product->current_prices = collect($currentPrices);
        return $product;
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return $this->productRepository->getLowStockProducts($threshold, [
            'category',
            'productUnits.unit',
            'inventoryStocks.warehouse'
        ]);
    }

    /**
     * Get product statistics.
     */
    public function getProductStatistics(): array
    {
        $baseStats = $this->productRepository->getStatistics();
        
        // Add pricing statistics
        $pricingStats = [];
        $products = $this->productRepository->getAll();
        
        foreach ($products as $product) {
            $stats = $this->pricingService->getPriceStatistics($product);
            $pricingStats[] = $stats;
        }

        $baseStats['pricing_overview'] = [
            'products_with_pricing' => collect($pricingStats)->where('total_prices', '>', 0)->count(),
            'average_price_points_per_product' => collect($pricingStats)->avg('total_prices'),
            'total_price_records' => collect($pricingStats)->sum('total_prices')
        ];

        return $baseStats;
    }

    /**
     * Activate/Deactivate product.
     */
    public function toggleProductStatus(Product $product): Product
    {
        $newStatus = $product->status === 'active' ? 'inactive' : 'active';
        return $this->productRepository->update($product, ['status' => $newStatus]);
    }

    /**
     * Duplicate a product.
     */
    public function duplicateProduct(Product $product, array $overrides = []): Product
    {
        $productData = $product->toArray();
        
        // Remove unique fields and timestamps
        unset($productData['id'], $productData['created_at'], $productData['updated_at']);
        
        // Generate new SKU if not provided in overrides
        if (!isset($overrides['sku'])) {
            $productData['sku'] = $this->generateUniqueSku($product->sku);
        }
        
        // Generate new barcode if not provided in overrides
        if (!isset($overrides['barcode']) && $product->barcode) {
            $productData['barcode'] = $this->generateUniqueBarcode($product->barcode);
        }
        
        // Apply overrides
        $productData = array_merge($productData, $overrides);
        
        DB::beginTransaction();
        try {
            $newProduct = $this->productRepository->create($productData);
            
            // Duplicate units
            foreach ($product->productUnits as $unit) {
                $newProduct->productUnits()->create([
                    'unit_id' => $unit->unit_id,
                    'conversion_factor' => $unit->conversion_factor,
                    'is_base_unit' => $unit->is_base_unit
                ]);
            }
            
            // Duplicate prices (with future effective dates)
            foreach ($product->productPrices as $price) {
                $newProduct->productPrices()->create([
                    'unit_id' => $price->unit_id,
                    'price_type' => $price->price_type,
                    'price' => $price->price,
                    'effective_date' => now()->toDateString(),
                    'status' => 'inactive' // Start as inactive
                ]);
            }
            
            DB::commit();
            return $newProduct->load(['category', 'productUnits.unit', 'productPrices.unit']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate unique fields.
     */
    protected function validateUniqueFields(array $data, ?int $excludeId = null): void
    {
        if (isset($data['sku']) && $this->productRepository->skuExists($data['sku'], $excludeId)) {
            throw ValidationException::withMessages([
                'sku' => ['The SKU has already been taken.']
            ]);
        }

        if (isset($data['barcode']) && $this->productRepository->barcodeExists($data['barcode'], $excludeId)) {
            throw ValidationException::withMessages([
                'barcode' => ['The barcode has already been taken.']
            ]);
        }
    }

    /**
     * Check if product has transactions.
     */
    protected function hasTransactions(Product $product): bool
    {
        return $product->salesTransactionItems()->exists() || 
               $product->purchaseOrderItems()->exists();
    }

    /**
     * Create product units.
     */
    protected function createProductUnits(Product $product, array $unitsData): void
    {
        foreach ($unitsData as $unitData) {
            $product->productUnits()->create($unitData);
        }
    }

    /**
     * Create product prices.
     */
    protected function createProductPrices(Product $product, array $pricesData): void
    {
        foreach ($pricesData as $priceData) {
            $this->pricingService->createPrice($product, $priceData);
        }
    }

    /**
     * Update product units.
     */
    protected function updateProductUnits(Product $product, array $unitsData): void
    {
        // This is a simplified approach - in production, you might want more sophisticated sync logic
        $product->productUnits()->delete();
        $this->createProductUnits($product, $unitsData);
    }

    /**
     * Update product prices.
     */
    protected function updateProductPrices(Product $product, array $pricesData): void
    {
        $this->pricingService->bulkUpdatePrices($product, $pricesData);
    }

    /**
     * Generate unique SKU.
     */
    protected function generateUniqueSku(string $baseSku): string
    {
        $counter = 1;
        $newSku = $baseSku . '-COPY';
        
        while ($this->productRepository->skuExists($newSku)) {
            $newSku = $baseSku . '-COPY-' . $counter;
            $counter++;
        }
        
        return $newSku;
    }

    /**
     * Generate unique barcode.
     */
    protected function generateUniqueBarcode(string $baseBarcode): string
    {
        $counter = 1;
        $newBarcode = $baseBarcode . '1';
        
        while ($this->productRepository->barcodeExists($newBarcode)) {
            $newBarcode = $baseBarcode . $counter;
            $counter++;
        }
        
        return $newBarcode;
    }
}