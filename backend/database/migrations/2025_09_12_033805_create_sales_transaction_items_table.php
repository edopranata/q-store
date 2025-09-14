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
        Schema::create('sales_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_transaction_id')->constrained('sales_transactions')->onDelete('cascade')->comment('ID transaksi penjualan');
            $table->foreignId('product_id')->constrained('products')->comment('ID produk');
            $table->foreignId('product_unit_id')->constrained('product_units')->comment('ID unit produk');
            $table->foreignId('batch_id')->nullable()->constrained('stock_batches')->comment('ID batch untuk FIFO tracking');
            $table->decimal('quantity', 10, 2)->comment('Jumlah produk');
            $table->decimal('unit_price', 15, 2)->comment('Harga satuan');
            $table->decimal('purchase_price', 15, 2)->nullable()->comment('HPP untuk perhitungan profit');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('Persentase diskon item');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Nominal diskon item');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Nominal pajak item');
            $table->decimal('total_price', 15, 2)->comment('Total harga item');
            $table->text('notes')->nullable()->comment('Catatan item');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['sales_transaction_id', 'product_id'], 'idx_trans_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_transaction_items');
    }
};
