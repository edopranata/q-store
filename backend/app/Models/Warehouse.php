<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'location',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function inventoryStocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }
}
