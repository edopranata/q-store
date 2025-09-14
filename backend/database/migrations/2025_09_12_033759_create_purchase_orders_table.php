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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique()->comment('Purchase Order Number');
            $table->foreignId('supplier_id')->constrained('suppliers')->comment('Supplier reference');
            $table->foreignId('warehouse_id')->constrained('warehouses')->comment('Destination warehouse');
            $table->date('po_date')->comment('Purchase Order date');
            $table->date('expected_date')->nullable()->comment('Expected delivery date');
            $table->enum('status', ['draft', 'ordered', 'partial', 'received', 'cancelled'])->default('draft')->comment('PO Status');
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Subtotal amount');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Tax amount');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Discount amount');
            $table->decimal('shipping_cost', 15, 2)->default(0)->comment('Shipping cost');
            $table->decimal('grand_total', 15, 2)->default(0)->comment('Grand total amount');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->foreignId('created_by')->nullable()->constrained('users')->comment('User who created the PO');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('po_number', 'idx_po_number');
            $table->index('po_date', 'idx_po_date');
            $table->index(['supplier_id', 'status'], 'idx_supplier_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
