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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->enum('movement_type', ['in', 'out', 'transfer', 'adjustment', 'return'])->comment('Type of stock movement');
            $table->string('reference_type', 50)->nullable()->comment('Reference type: purchase, sales, transfer, etc');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('Reference ID to related transaction');
            $table->foreignId('product_id')->constrained('products')->comment('Product being moved');
            $table->foreignId('batch_id')->nullable()->constrained('stock_batches')->comment('Stock batch for FIFO tracking');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->comment('Source warehouse');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->comment('Destination warehouse');
            $table->decimal('quantity', 10, 2)->comment('Quantity moved');
            $table->foreignId('unit_id')->constrained('units')->comment('Unit of measurement');
            $table->decimal('unit_cost', 15, 2)->nullable()->comment('Cost per unit');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('User who created the movement');
            $table->dateTime('movement_date')->comment('Date and time of movement');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('movement_date', 'idx_movement_date');
            $table->index(['product_id', 'movement_date'], 'idx_movement_product');
            $table->index(['reference_type', 'reference_id'], 'idx_movement_reference');
            $table->index(['from_warehouse_id', 'to_warehouse_id'], 'idx_movement_warehouses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};