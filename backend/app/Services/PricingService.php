<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PricingService
{
    /**
     * Get the current effective price for a product and unit.
     */
    public function getCurrentPrice(Product $product, int $unitId, string $priceType = 'selling', ?string $date = null): ?ProductPrice
    {
        $date = $date ?? now()->toDateString();

        return $product->productPrices()
            ->with(['unit'])
            ->where('unit_id', $unitId)
            ->byPriceType($priceType)
            ->active()
            ->effective($date)
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    /**
     * Get all current prices for a product across all units.
     */
    public function getAllCurrentPrices(Product $product, string $priceType = 'selling', ?string $date = null): Collection
    {
        $date = $date ?? now()->toDateString();

        return $product->productPrices()
            ->with(['unit'])
            ->byPriceType($priceType)
            ->active()
            ->effective($date)
            ->orderBy('effective_date', 'desc')
            ->get()
            ->groupBy('unit_id')
            ->map(function ($prices) {
                return $prices->first(); // Get the most recent price for each unit
            })
            ->values();
    }

    /**
     * Calculate price conversion between units.
     */
    public function convertPrice(Product $product, float $price, int $fromUnitId, int $toUnitId, string $priceType = 'selling'): ?float
    {
        // Get unit conversion factors
        $fromUnit = $product->productUnits()->where('unit_id', $fromUnitId)->first();
        $toUnit = $product->productUnits()->where('unit_id', $toUnitId)->first();

        if (!$fromUnit || !$toUnit) {
            return null;
        }

        // Convert to base unit first, then to target unit
        $basePrice = $price / $fromUnit->conversion_factor;
        $convertedPrice = $basePrice * $toUnit->conversion_factor;

        return round($convertedPrice, 2);
    }

    /**
     * Get price history for a product and unit.
     */
    public function getPriceHistory(Product $product, int $unitId, string $priceType = 'selling', int $limit = 10): Collection
    {
        return $product->productPrices()
            ->with(['unit'])
            ->where('unit_id', $unitId)
            ->byPriceType($priceType)
            ->orderBy('effective_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if a price change would create overlapping periods.
     */
    public function hasOverlappingPrices(Product $product, int $unitId, string $priceType, string $effectiveDate, ?string $endDate = null, ?int $excludePriceId = null): bool
    {
        $query = $product->productPrices()
            ->where('unit_id', $unitId)
            ->where('price_type', $priceType)
            ->where('effective_date', '<=', $effectiveDate);

        if ($excludePriceId) {
            $query->where('id', '!=', $excludePriceId);
        }

        $query->where(function ($q) use ($effectiveDate, $endDate) {
            $q->whereNull('end_date');
            if ($endDate) {
                $q->orWhere('end_date', '>=', $effectiveDate);
            }
        });

        return $query->exists();
    }

    /**
     * Create a new price with automatic end date management.
     */
    public function createPrice(Product $product, array $priceData): ProductPrice
    {
        // Check if there's an existing active price that needs to be ended
        $existingPrice = $product->productPrices()
            ->where('unit_id', $priceData['unit_id'])
            ->where('price_type', $priceData['price_type'])
            ->active()
            ->whereNull('end_date')
            ->where('effective_date', '<', $priceData['effective_date'])
            ->first();

        if ($existingPrice) {
            // Set end date to one day before the new price becomes effective
            $endDate = Carbon::parse($priceData['effective_date'])->subDay()->toDateString();
            $existingPrice->update(['end_date' => $endDate]);
        }

        return $product->productPrices()->create($priceData);
    }

    /**
     * Update price with overlap validation.
     */
    public function updatePrice(ProductPrice $productPrice, array $updateData): ProductPrice
    {
        // If key fields are being updated, check for overlaps
        if (isset($updateData['unit_id']) || isset($updateData['price_type']) || isset($updateData['effective_date'])) {
            $unitId = $updateData['unit_id'] ?? $productPrice->unit_id;
            $priceType = $updateData['price_type'] ?? $productPrice->price_type;
            $effectiveDate = $updateData['effective_date'] ?? $productPrice->effective_date;
            $endDate = $updateData['end_date'] ?? $productPrice->end_date;

            if ($this->hasOverlappingPrices($productPrice->product, $unitId, $priceType, $effectiveDate, $endDate, $productPrice->id)) {
                throw new \InvalidArgumentException('Price update would create overlapping periods');
            }
        }

        $productPrice->update($updateData);
        return $productPrice->fresh();
    }

    /**
     * Get price statistics for a product.
     */
    public function getPriceStatistics(Product $product, string $priceType = 'selling'): array
    {
        $prices = $product->productPrices()
            ->byPriceType($priceType)
            ->active()
            ->get();

        if ($prices->isEmpty()) {
            return [
                'total_prices' => 0,
                'min_price' => null,
                'max_price' => null,
                'avg_price' => null,
                'units_with_prices' => 0
            ];
        }

        return [
            'total_prices' => $prices->count(),
            'min_price' => $prices->min('price'),
            'max_price' => $prices->max('price'),
            'avg_price' => round($prices->avg('price'), 2),
            'units_with_prices' => $prices->unique('unit_id')->count()
        ];
    }

    /**
     * Bulk update prices for multiple units.
     */
    public function bulkUpdatePrices(Product $product, array $pricesData): array
    {
        $results = [];
        
        foreach ($pricesData as $priceData) {
            try {
                if (isset($priceData['id'])) {
                    // Update existing price
                    $price = ProductPrice::findOrFail($priceData['id']);
                    $results[] = $this->updatePrice($price, $priceData);
                } else {
                    // Create new price
                    $results[] = $this->createPrice($product, $priceData);
                }
            } catch (\Exception $e) {
                $results[] = ['error' => $e->getMessage(), 'data' => $priceData];
            }
        }

        return $results;
    }

    /**
     * Get upcoming price changes.
     */
    public function getUpcomingPriceChanges(Product $product, int $days = 30): Collection
    {
        $futureDate = now()->addDays($days)->toDateString();
        
        return $product->productPrices()
            ->with(['unit'])
            ->where('effective_date', '>', now()->toDateString())
            ->where('effective_date', '<=', $futureDate)
            ->orderBy('effective_date', 'asc')
            ->get();
    }
}