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
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 50)->unique();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('initial_qty', 10, 2);
            $table->decimal('current_qty', 10, 2);
            $table->foreignId('unit_id')->constrained('units');
            $table->date('expiry_date')->nullable();
            $table->date('received_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for FIFO performance
            $table->index(['product_id', 'warehouse_id', 'received_date'], 'idx_batch_fifo');
            $table->index('batch_code', 'idx_batch_code');
            $table->index(['product_id', 'warehouse_id', 'is_active', 'received_date'], 'idx_batch_fifo_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};
