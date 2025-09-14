<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('reserved_qty', 15, 3)->default(0);
            $table->decimal('available_qty', 15, 3)->storedAs('quantity - reserved_qty');
            $table->decimal('last_purchase_price', 15, 2)->nullable();
            $table->decimal('average_cost', 15, 2)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'warehouse_id']);
            $table->index('quantity');
            
            // Unique constraint
            $table->unique(['product_id', 'warehouse_id'], 'unique_product_warehouse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
