<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'movement_type',
        'reference_type',
        'reference_id',
        'product_id',
        'batch_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'quantity',
        'unit_id',
        'unit_cost',
        'notes',
        'created_by',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class, 'batch_id');
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByMovementType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where(function ($q) use ($warehouseId) {
            $q->where('from_warehouse_id', $warehouseId)
              ->orWhere('to_warehouse_id', $warehouseId);
        });
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    public function scopeIncoming($query)
    {
        return $query->whereIn('movement_type', ['in', 'transfer', 'return', 'adjustment'])
                    ->where('quantity', '>', 0);
    }

    public function scopeOutgoing($query)
    {
        return $query->whereIn('movement_type', ['out', 'transfer'])
                    ->where('quantity', '>', 0);
    }

    // Helper methods
    public function isIncoming(): bool
    {
        return in_array($this->movement_type, ['in', 'return']) || 
               ($this->movement_type === 'transfer' && $this->to_warehouse_id) ||
               ($this->movement_type === 'adjustment' && $this->quantity > 0);
    }

    public function isOutgoing(): bool
    {
        return in_array($this->movement_type, ['out']) || 
               ($this->movement_type === 'transfer' && $this->from_warehouse_id) ||
               ($this->movement_type === 'adjustment' && $this->quantity < 0);
    }

    public function getMovementDescription(): string
    {
        switch ($this->movement_type) {
            case 'in':
                return 'Stock In';
            case 'out':
                return 'Stock Out';
            case 'transfer':
                return 'Stock Transfer';
            case 'adjustment':
                return 'Stock Adjustment';
            case 'return':
                return 'Stock Return';
            default:
                return 'Unknown Movement';
        }
    }

    public function getTotalValue(): float
    {
        return $this->quantity * ($this->unit_cost ?? 0);
    }
}