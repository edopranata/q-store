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
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique()->comment('Nomor transaksi unik');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->comment('ID customer, nullable untuk walk-in customer');
            $table->foreignId('warehouse_id')->constrained('warehouses')->comment('ID warehouse tempat transaksi');
            $table->datetime('transaction_date')->comment('Tanggal dan waktu transaksi');
            $table->enum('status', ['draft', 'pending', 'paid', 'partial', 'cancelled', 'refunded'])->default('draft')->comment('Status transaksi');
            $table->decimal('subtotal', 15, 2)->default(0)->comment('Subtotal sebelum diskon dan pajak');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('Persentase diskon');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Nominal diskon');
            $table->decimal('tax_percentage', 5, 2)->default(0)->comment('Persentase pajak');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Nominal pajak');
            $table->decimal('grand_total', 15, 2)->default(0)->comment('Total akhir setelah diskon dan pajak');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Jumlah yang sudah dibayar');
            $table->decimal('change_amount', 15, 2)->default(0)->comment('Kembalian');
            $table->text('notes')->nullable()->comment('Catatan transaksi');
            $table->foreignId('cashier_id')->nullable()->constrained('users')->comment('ID kasir yang melayani');
            $table->timestamps();
            
            // Indexes untuk performa
            $table->index('transaction_number', 'idx_trans_number');
            $table->index('transaction_date', 'idx_trans_date');
            $table->index(['status', 'transaction_date'], 'idx_status_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_transactions');
    }
};
