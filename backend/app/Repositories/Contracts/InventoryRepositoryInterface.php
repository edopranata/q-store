<?php

namespace App\Repositories\Contracts;

use App\Models\InventoryStock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface InventoryRepositoryInterface
{
    /**
     * Get all inventory stocks with optional filters.
     */
    public function getAllStocks(array $filters = [], array $with = []): Collection;

    /**
     * Get paginated inventory stocks.
     */
    public function getPaginatedStocks(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get inventory stock by product and warehouse.
     */
    public function getStockByProductAndWarehouse(int $productId, int $warehouseId): ?InventoryStock;

    /**
     * Get stocks by warehouse.
     */
    public function getStocksByWarehouse(int $warehouseId, array $with = []): Collection;

    /**
     * Get stocks by product.
     */
    public function getStocksByProduct(int $productId, array $with = []): Collection;

    /**
     * Get low stock products.
     */
    public function getLowStockProducts(int $threshold = 10, array $with = []): Collection;

    /**
     * Get out of stock products.
     */
    public function getOutOfStockProducts(array $with = []): Collection;

    /**
     * Create or update inventory stock.
     */
    public function createOrUpdateStock(array $data): InventoryStock;

    /**
     * Add stock quantity.
     */
    public function addStock(int $productId, int $warehouseId, float $quantity, ?float $purchasePrice = null): InventoryStock;

    /**
     * Reduce stock quantity.
     */
    public function reduceStock(int $productId, int $warehouseId, float $quantity): InventoryStock;

    /**
     * Reserve stock quantity.
     */
    public function reserveStock(int $productId, int $warehouseId, float $quantity): InventoryStock;

    /**
     * Release reserved stock.
     */
    public function releaseReservedStock(int $productId, int $warehouseId, float $quantity): InventoryStock;

    /**
     * Get total stock across all warehouses for a product.
     */
    public function getTotalStockByProduct(int $productId): float;

    /**
     * Get available stock across all warehouses for a product.
     */
    public function getAvailableStockByProduct(int $productId): float;

    /**
     * Check if sufficient stock is available.
     */
    public function hasSufficientStock(int $productId, int $warehouseId, float $requiredQuantity): bool;

    /**
     * Get stock movements for audit trail.
     */
    public function getStockMovements(array $filters = []): Collection;

    /**
     * Bulk update stocks.
     */
    public function bulkUpdateStocks(array $stockUpdates): bool;

    /**
     * Get inventory valuation.
     */
    public function getInventoryValuation(array $filters = []): array;
}