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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade')->comment('Purchase Order reference');
            $table->foreignId('product_id')->constrained('products')->comment('Product reference');
            $table->foreignId('product_unit_id')->constrained('product_units')->comment('Product unit reference');
            $table->decimal('quantity', 10, 2)->comment('Ordered quantity');
            $table->decimal('unit_price', 15, 2)->comment('Price per unit');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Tax amount');
            $table->decimal('total_price', 15, 2)->comment('Total price for this item');
            $table->decimal('received_qty', 10, 2)->default(0)->comment('Quantity already received');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['purchase_order_id', 'product_id'], 'idx_po_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
