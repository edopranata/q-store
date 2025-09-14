<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'po_date',
        'expected_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'grand_total',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySupplier($query, int $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('po_date', [$startDate, $endDate]);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'ordered']);
    }

    public function scopePartiallyReceived($query)
    {
        return $query->where('status', 'partial');
    }

    // Helper methods
    public function getTotalItemsAttribute(): int
    {
        return $this->purchaseOrderItems()->count();
    }

    public function getPendingItemsAttribute(): int
    {
        return $this->purchaseOrderItems()
                   ->whereColumn('received_qty', '<', 'quantity')
                   ->count();
    }

    public function getReceivedPercentageAttribute(): float
    {
        $totalQty = $this->purchaseOrderItems()->sum('quantity');
        $receivedQty = $this->purchaseOrderItems()->sum('received_qty');
        
        return $totalQty > 0 ? ($receivedQty / $totalQty) * 100 : 0;
    }

    public function isFullyReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isPartiallyReceived(): bool
    {
        return $this->status === 'partial';
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, ['ordered', 'partial']);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->purchaseOrderItems()->sum('total_price');
        $this->grand_total = $this->subtotal + $this->tax_amount + $this->shipping_cost - $this->discount_amount;
        $this->save();
    }
}
