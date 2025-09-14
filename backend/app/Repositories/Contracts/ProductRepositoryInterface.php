<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Get all products with optional filters.
     */
    public function getAll(array $filters = [], array $with = []): Collection;

    /**
     * Get paginated products with optional filters.
     */
    public function getPaginated(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find product by ID.
     */
    public function findById(int $id, array $with = []): ?Product;

    /**
     * Find product by SKU.
     */
    public function findBySku(string $sku, array $with = []): ?Product;

    /**
     * Find product by barcode.
     */
    public function findByBarcode(string $barcode, array $with = []): ?Product;

    /**
     * Create a new product.
     */
    public function create(array $data): Product;

    /**
     * Update an existing product.
     */
    public function update(Product $product, array $data): Product;

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool;

    /**
     * Get products by category.
     */
    public function getByCategory(int $categoryId, array $with = []): Collection;

    /**
     * Search products by name or SKU.
     */
    public function search(string $query, array $with = [], int $limit = 10): Collection;

    /**
     * Get products with low stock.
     */
    public function getLowStockProducts(int $threshold = 10, array $with = []): Collection;

    /**
     * Get active products.
     */
    public function getActive(array $with = []): Collection;

    /**
     * Get products with their current prices.
     */
    public function getWithCurrentPrices(array $filters = [], string $priceType = 'selling'): Collection;

    /**
     * Get products with their units.
     */
    public function getWithUnits(array $filters = []): Collection;

    /**
     * Check if SKU exists (excluding specific product ID).
     */
    public function skuExists(string $sku, ?int $excludeId = null): bool;

    /**
     * Check if barcode exists (excluding specific product ID).
     */
    public function barcodeExists(string $barcode, ?int $excludeId = null): bool;

    /**
     * Get product statistics.
     */
    public function getStatistics(): array;
}