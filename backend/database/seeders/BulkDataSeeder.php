<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\ProductUnit;
use App\Models\ProductPrice;
use App\Models\InventoryStock;
use App\Models\StockBatch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use App\Models\PaymentMethod;
use App\Models\Payment;
use App\Models\User;
use Exception;

class BulkDataSeeder extends Seeder
{
    private $faker;
    private $config;
    private $createdData = [];
    
    public function __construct()
    {
        $this->faker = Faker::create('id_ID'); // Indonesian locale
        $this->config = $this->getSeederConfig();
    }

    /**
     * Konfigurasi jumlah data yang akan dibuat
     */
    private function getSeederConfig(): array
    {
        return [
            'categories' => env('SEED_CATEGORIES', 20),
            'units' => env('SEED_UNITS', 15),
            'products' => env('SEED_PRODUCTS', 500),
            'customers' => env('SEED_CUSTOMERS', 200),
            'suppliers' => env('SEED_SUPPLIERS', 50),
            'warehouses' => env('SEED_WAREHOUSES', 5),
            'purchase_orders' => env('SEED_PURCHASE_ORDERS', 100),
            'sales_transactions' => env('SEED_SALES_TRANSACTIONS', 300),
            'batch_size' => env('SEED_BATCH_SIZE', 100), // Ukuran batch untuk insert
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai bulk data seeding...');
        
        // Truncate tables untuk menghindari duplikasi
        $this->command->info('🗑️ Cleaning existing data...');
        $this->truncateTables();
        
        // Seed dalam urutan yang benar untuk menjaga relasi
        $this->seedCategories();
        $this->seedUnits();
        $this->seedSuppliers();
        $this->seedCustomers();
        $this->seedWarehouses();
        $this->seedProducts();
        $this->seedProductUnits();
        $this->seedProductPrices();
        $this->seedInventoryStocks();
        $this->seedPurchaseOrders();
        $this->seedSalesTransactions();
        
        $this->command->info('✅ Bulk data seeding berhasil diselesaikan!');
        $this->printSummary();
    }

    /**
     * Truncate tables untuk menghindari duplikasi
     */
    private function truncateTables(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate dalam urutan terbalik untuk menghindari constraint error
        $tables = [
            'sales_transactions',
            'purchase_orders', 
            'inventory_stocks',
            'product_prices',
            'product_units',
            'products',
            'warehouses',
            'customers',
            'suppliers',
            'units',
            'categories'
        ];
        
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
                $this->command->info("   Truncated {$table}");
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Seed categories dengan data realistis
     */
    private function seedCategories(): void
    {
        $this->command->info('📁 Seeding Categories...');
        
        $categories = [
            ['name' => 'Makanan & Minuman', 'description' => 'Produk makanan dan minuman'],
            ['name' => 'Elektronik', 'description' => 'Perangkat elektronik dan aksesoris'],
            ['name' => 'Pakaian', 'description' => 'Pakaian dan fashion'],
            ['name' => 'Kesehatan & Kecantikan', 'description' => 'Produk kesehatan dan kecantikan'],
            ['name' => 'Rumah & Taman', 'description' => 'Perlengkapan rumah dan taman'],
            ['name' => 'Olahraga & Outdoor', 'description' => 'Peralatan olahraga dan outdoor'],
            ['name' => 'Buku & Alat Tulis', 'description' => 'Buku dan alat tulis kantor'],
            ['name' => 'Mainan & Hobi', 'description' => 'Mainan anak dan hobi'],
            ['name' => 'Otomotif', 'description' => 'Suku cadang dan aksesoris kendaraan'],
            ['name' => 'Peralatan Dapur', 'description' => 'Peralatan masak dan dapur'],
        ];
        
        // Tambahkan kategori tambahan jika diperlukan
        while (count($categories) < $this->config['categories']) {
            $categories[] = [
                'name' => $this->faker->words(2, true) . ' Category',
                'description' => $this->faker->sentence(),
                'status' => 'active'
            ];
        }
        
        $this->insertInBatches(Category::class, $categories);
        $this->createdData['categories'] = Category::all();
    }

    /**
     * Seed units dengan data realistis
     */
    private function seedUnits(): void
    {
        $this->command->info('📏 Seeding Units...');
        
        $units = [
            ['name' => 'Piece', 'symbol' => 'pcs', 'description' => 'Satuan buah/unit'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'description' => 'Satuan berat kilogram'],
            ['name' => 'Gram', 'symbol' => 'g', 'description' => 'Satuan berat gram'],
            ['name' => 'Liter', 'symbol' => 'L', 'description' => 'Satuan volume liter'],
            ['name' => 'Milliliter', 'symbol' => 'mL', 'description' => 'Satuan volume milliliter'],
            ['name' => 'Box', 'symbol' => 'box', 'description' => 'Satuan kemasan kotak'],
            ['name' => 'Pack', 'symbol' => 'pack', 'description' => 'Satuan kemasan pak'],
            ['name' => 'Dozen', 'symbol' => 'dz', 'description' => 'Satuan lusin'],
            ['name' => 'Meter', 'symbol' => 'm', 'description' => 'Satuan panjang meter'],
            ['name' => 'Centimeter', 'symbol' => 'cm', 'description' => 'Satuan panjang centimeter'],
        ];
        
        // Tambahkan unit tambahan jika diperlukan
        while (count($units) < $this->config['units']) {
            $units[] = [
                'name' => $this->faker->word() . ' Unit',
                'symbol' => strtolower($this->faker->lexify('???')),
                'description' => $this->faker->sentence(),
                'status' => 'active'
            ];
        }
        
        $this->insertInBatches(Unit::class, $units);
        $this->createdData['units'] = Unit::all();
    }

    /**
     * Insert data dalam batch untuk performa yang lebih baik
     */
    private function insertInBatches(string $model, array $data, bool $addStatus = true): void
    {
        $chunks = array_chunk($data, $this->config['batch_size']);
        
        foreach ($chunks as $chunk) {
            // Tambahkan timestamp
            $chunk = array_map(function($item) use ($addStatus) {
                $item['created_at'] = now();
                $item['updated_at'] = now();
                if ($addStatus && !isset($item['status'])) {
                    $item['status'] = 'active';
                }
                return $item;
            }, $chunk);
            
            $model::insert($chunk);
        }
    }

    /**
     * Print summary setelah seeding selesai
     */
    private function printSummary(): void
    {
        $this->command->info('\n📊 RINGKASAN SEEDING:');
        $this->command->info('Categories: ' . Category::count());
        $this->command->info('Units: ' . Unit::count());
        $this->command->info('Products: ' . Product::count());
        $this->command->info('Customers: ' . Customer::count());
        $this->command->info('Suppliers: ' . Supplier::count());
        $this->command->info('Warehouses: ' . Warehouse::count());
    }

    /**
     * Seed suppliers dengan data realistis
     */
    private function seedSuppliers(): void
    {
        $this->command->info('🏢 Seeding Suppliers...');
        
        $suppliers = [];
        for ($i = 0; $i < $this->config['suppliers']; $i++) {
            $suppliers[] = [
                'name' => $this->faker->company(),
                'contact_person' => $this->faker->name(),
                'phone' => $this->faker->phoneNumber(),
                'email' => $this->faker->companyEmail(),
                'address' => $this->faker->address(),
                'status' => $this->faker->randomElement(['active', 'inactive'])
            ];
        }
        
        $this->insertInBatches(Supplier::class, $suppliers);
        $this->createdData['suppliers'] = Supplier::all();
    }

    /**
     * Seed customers dengan data realistis
     */
    private function seedCustomers(): void
    {
        $this->command->info('👥 Seeding Customers...');
        
        $customers = [];
        for ($i = 0; $i < $this->config['customers']; $i++) {
            $customers[] = [
                'name' => $this->faker->name(),
                'phone' => $this->faker->phoneNumber(),
                'email' => $this->faker->email(),
                'address' => $this->faker->address(),
                'status' => $this->faker->randomElement(['active', 'inactive'])
            ];
        }
        
        $this->insertInBatches(Customer::class, $customers);
        $this->createdData['customers'] = Customer::all();
    }

    /**
     * Seed warehouses dengan data realistis
     */
    private function seedWarehouses(): void
    {
        $this->command->info('🏪 Seeding Warehouses...');
        
        $warehouses = [];
        $warehouseNames = ['Gudang Utama', 'Gudang Cabang A', 'Gudang Cabang B', 'Gudang Online', 'Gudang Retur'];
        
        for ($i = 0; $i < $this->config['warehouses']; $i++) {
            $warehouses[] = [
                'name' => $warehouseNames[$i] ?? 'Gudang ' . ($i + 1),
                'code' => 'WH' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'address' => $this->faker->address(),
                'phone' => $this->faker->phoneNumber(),
                'manager_name' => $this->faker->name(),
                'status' => 'active'
            ];
        }
        
        $this->insertInBatches(Warehouse::class, $warehouses);
        $this->createdData['warehouses'] = Warehouse::all();
    }

    /**
     * Seed products dengan data realistis
     */
    private function seedProducts(): void
    {
        $this->command->info('📦 Seeding Products...');
        
        $products = [];
        $categories = $this->createdData['categories'];
        
        for ($i = 0; $i < $this->config['products']; $i++) {
            $category = $categories->random();
            $productName = $this->generateProductName($category->name);
            $costPrice = $this->faker->randomFloat(2, 1000, 100000);
            $sellingPrice = $costPrice * $this->faker->randomFloat(2, 1.2, 3.0);
            
            $products[] = [
                'category_id' => $category->id,
                'name' => $productName,
                'sku' => 'SKU' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'description' => $this->faker->sentence(),
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'min_stock' => $this->faker->numberBetween(5, 50),
                'barcode' => $this->faker->ean13(),
                'status' => $this->faker->randomElement(['active', 'inactive'])
            ];
        }
        
        $this->insertInBatches(Product::class, $products);
        $this->createdData['products'] = Product::all();
    }

    /**
     * Generate nama produk yang realistis berdasarkan kategori
     */
    private function generateProductName(string $categoryName): string
    {
        $productTemplates = [
            'Makanan & Minuman' => ['Kopi', 'Teh', 'Susu', 'Roti', 'Biskuit', 'Mie Instan', 'Air Mineral'],
            'Elektronik' => ['Smartphone', 'Laptop', 'Headphone', 'Charger', 'Speaker', 'Mouse', 'Keyboard'],
            'Pakaian' => ['Kaos', 'Kemeja', 'Celana', 'Jaket', 'Sepatu', 'Tas', 'Topi'],
            'Kesehatan & Kecantikan' => ['Shampoo', 'Sabun', 'Pasta Gigi', 'Vitamin', 'Masker', 'Lotion'],
            'default' => ['Produk', 'Item', 'Barang']
        ];
        
        $templates = $productTemplates[$categoryName] ?? $productTemplates['default'];
        $baseName = $this->faker->randomElement($templates);
        $variant = $this->faker->randomElement(['Premium', 'Deluxe', 'Standard', 'Mini', 'Jumbo']);
        
        return $baseName . ' ' . $variant;
    }

    /**
     * Seed product units
     */
    private function seedProductUnits(): void
    {
        $this->command->info('🔗 Seeding Product Units...');
        
        $productUnits = [];
        $products = $this->createdData['products'];
        $units = $this->createdData['units'];
        
        foreach ($products as $product) {
            // Setiap produk minimal punya 1 unit dasar
            $baseUnit = $units->random();
            $productUnits[] = [
                'product_id' => $product->id,
                'unit_id' => $baseUnit->id,
                'conversion_factor' => 1.0000,
                'is_base_unit' => true
            ];
            
            // 30% kemungkinan punya unit tambahan
            if ($this->faker->boolean(30)) {
                $additionalUnit = $units->where('id', '!=', $baseUnit->id)->random();
                $productUnits[] = [
                    'product_id' => $product->id,
                    'unit_id' => $additionalUnit->id,
                    'conversion_factor' => $this->faker->randomFloat(4, 0.1, 100),
                    'is_base_unit' => false
                ];
            }
        }
        
        $this->insertInBatches(ProductUnit::class, $productUnits, false);
    }

    /**
     * Seed product prices
     */
    private function seedProductPrices(): void
    {
        $this->command->info('💰 Seeding Product Prices...');
        
        $productPrices = [];
        $products = $this->createdData['products'];
        $units = $this->createdData['units'];
        
        foreach ($products as $product) {
            $unit = $units->random();
            $sellingPrice = $product->selling_price;
            $wholesalePrice = $sellingPrice * 0.9; // 10% discount for wholesale
            $retailPrice = $sellingPrice * 1.1; // 10% markup for retail
            
            // Selling price
            $productPrices[] = [
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'price_type' => 'selling',
                'price' => $sellingPrice,
                'effective_date' => now()->subDays($this->faker->numberBetween(0, 30)),
                'end_date' => null,
                'status' => 'active'
            ];
            
            // Wholesale price (30% chance)
            if ($this->faker->boolean(30)) {
                $productPrices[] = [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'price_type' => 'wholesale',
                    'price' => $wholesalePrice,
                    'effective_date' => now()->subDays($this->faker->numberBetween(0, 30)),
                    'end_date' => null,
                    'status' => 'active'
                ];
            }
            
            // Retail price (20% chance)
            if ($this->faker->boolean(20)) {
                $productPrices[] = [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'price_type' => 'retail',
                    'price' => $retailPrice,
                    'effective_date' => now()->subDays($this->faker->numberBetween(0, 30)),
                    'end_date' => null,
                    'status' => 'active'
                ];
            }
        }
        
        $this->insertInBatches(ProductPrice::class, $productPrices);
    }

    /**
     * Seed inventory stocks
     */
    private function seedInventoryStocks(): void
    {
        $this->command->info('📊 Seeding Inventory Stocks...');
        
        $inventoryStocks = [];
        $products = $this->createdData['products'];
        $warehouses = $this->createdData['warehouses'];
        
        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                $quantity = $this->faker->numberBetween(0, 1000);
                $reservedQty = $this->faker->numberBetween(0, min(50, $quantity));
                
                $inventoryStocks[] = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $quantity,
                    'reserved_qty' => $reservedQty,
                    'last_purchase_price' => $this->faker->randomFloat(2, 1000, 100000),
                    'average_cost' => $this->faker->randomFloat(2, 1000, 100000)
                ];
            }
        }
        
        $this->insertInBatches(InventoryStock::class, $inventoryStocks, false);
    }

    /**
     * Seed purchase orders
     */
    private function seedPurchaseOrders(): void
    {
        $this->command->info('📋 Seeding Purchase Orders...');
        
        $purchaseOrders = [];
        $suppliers = $this->createdData['suppliers'];
        $warehouses = $this->createdData['warehouses'];
        for ($i = 0; $i < $this->config['purchase_orders']; $i++) {
            $poDate = $this->faker->dateTimeBetween('-6 months', 'now');
            $expectedDate = Carbon::parse($poDate)->addDays($this->faker->numberBetween(1, 14));
            
            $subtotal = $this->faker->randomFloat(2, 100000, 10000000);
            $taxAmount = $subtotal * 0.11; // PPN 11%
            $discountAmount = $this->faker->randomFloat(2, 0, $subtotal * 0.1);
            $shippingCost = $this->faker->randomFloat(2, 0, 100000);
            $grandTotal = $subtotal + $taxAmount - $discountAmount + $shippingCost;
            
            $purchaseOrders[] = [
                'po_number' => 'PO' . date('Ymd', $poDate->getTimestamp()) . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $suppliers->random()->id,
                'warehouse_id' => $warehouses->random()->id,
                'po_date' => $poDate,
                'expected_date' => $expectedDate,
                'status' => $this->faker->randomElement(['draft', 'ordered', 'partial', 'received', 'cancelled']),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'notes' => $this->faker->sentence(),
                'created_by' => null
            ];
        }
        
        $this->insertInBatches(PurchaseOrder::class, $purchaseOrders);
    }

    /**
     * Seed sales transactions
     */
    private function seedSalesTransactions(): void
    {
        $this->command->info('💳 Seeding Sales Transactions...');
        
        $salesTransactions = [];
        $customers = $this->createdData['customers'];
        $warehouses = $this->createdData['warehouses'];
        
        for ($i = 0; $i < $this->config['sales_transactions']; $i++) {
            $transactionDate = $this->faker->dateTimeBetween('-3 months', 'now');
            
            $subtotal = $this->faker->randomFloat(2, 10000, 1000000);
            $discountPercentage = $this->faker->randomFloat(2, 0, 20);
            $discountAmount = $subtotal * ($discountPercentage / 100);
            $taxPercentage = 11; // PPN 11%
            $taxAmount = ($subtotal - $discountAmount) * ($taxPercentage / 100);
            $grandTotal = $subtotal - $discountAmount + $taxAmount;
            $paidAmount = $grandTotal + $this->faker->randomFloat(2, 0, 50000); // Kembalian
            $changeAmount = $paidAmount - $grandTotal;
            
            $salesTransactions[] = [
                'transaction_number' => 'TRX' . date('Ymd', $transactionDate->getTimestamp()) . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'customer_id' => $this->faker->boolean(70) ? $customers->random()->id : null, // 70% ada customer
                'warehouse_id' => $warehouses->random()->id,
                'transaction_date' => $transactionDate,
                'status' => $this->faker->randomElement(['paid', 'pending', 'cancelled', 'draft']),
                'subtotal' => $subtotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'notes' => $this->faker->optional()->sentence(),
                'cashier_id' => null
            ];
        }
        
        $this->insertInBatches(SalesTransaction::class, $salesTransactions);
    }
}