<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('Categories not found. Please seed them first.');
            return;
        }

        $products = [
            [
                'name' => 'Coca Cola 330ml',
                'sku' => 'PROD001',
                'description' => 'Coca Cola Can 330ml',
                'category_id' => $categories->where('name', 'Food & Beverages')->first()->id,
                'cost_price' => 2500.00,
                'selling_price' => 3500.00,
                'min_stock' => 10,
                'barcode' => '1234567890123',
                'status' => 'active'
            ],
            [
                'name' => 'Samsung Galaxy A54',
                'sku' => 'PROD002',
                'description' => 'Samsung Galaxy A54 5G Smartphone',
                'category_id' => $categories->where('name', 'Electronics')->first()->id,
                'cost_price' => 4500000.00,
                'selling_price' => 5500000.00,
                'min_stock' => 2,
                'barcode' => '2345678901234',
                'status' => 'active'
            ],
            [
                'name' => 'T-Shirt Cotton',
                'sku' => 'PROD003',
                'description' => 'Cotton T-Shirt Various Sizes',
                'category_id' => $categories->where('name', 'Clothing')->first()->id,
                'cost_price' => 75000.00,
                'selling_price' => 125000.00,
                'min_stock' => 5,
                'barcode' => '3456789012345',
                'status' => 'active'
            ],
            [
                'name' => 'Shampoo 400ml',
                'sku' => 'PROD004',
                'description' => 'Anti-Dandruff Shampoo 400ml',
                'category_id' => $categories->where('name', 'Health & Beauty')->first()->id,
                'cost_price' => 25000.00,
                'selling_price' => 35000.00,
                'min_stock' => 8,
                'barcode' => '4567890123456',
                'status' => 'active'
            ],
            [
                'name' => 'Rice 5kg',
                'sku' => 'PROD005',
                'description' => 'Premium White Rice 5kg',
                'category_id' => $categories->where('name', 'Food & Beverages')->first()->id,
                'cost_price' => 45000.00,
                'selling_price' => 65000.00,
                'min_stock' => 15,
                'barcode' => '5678901234567',
                'status' => 'active'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
