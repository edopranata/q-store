<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_transaction_id',
        'product_id',
        'product_unit_id',
        'batch_id',
        'quantity',
        'unit_price',
        'purchase_price',
        'discount_percentage',
        'discount_amount',
        'tax_amount',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function salesTransaction(): BelongsTo
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'batch_id');
    }

    // Scopes
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByTransaction($query, int $transactionId)
    {
        return $query->where('sales_transaction_id', $transactionId);
    }

    // Helper methods
    public function getGrossProfitAttribute(): float
    {
        if (!$this->purchase_price) {
            return 0;
        }
        
        $cogs = $this->purchase_price * $this->quantity;
        return $this->total_price - $cogs;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->total_price <= 0) {
            return 0;
        }
        
        return ($this->gross_profit / $this->total_price) * 100;
    }

    public function calculateTotalPrice(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        
        // Apply discount
        $discountAmount = $this->discount_percentage > 0 ? 
            ($subtotal * $this->discount_percentage) / 100 : 
            $this->discount_amount;
        
        $afterDiscount = $subtotal - $discountAmount;
        
        // Add tax
        $this->total_price = $afterDiscount + $this->tax_amount;
        $this->discount_amount = $discountAmount;
        
        $this->save();
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getNetPriceAttribute(): float
    {
        return $this->unit_price - ($this->discount_amount / $this->quantity);
    }
}
