<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Food & Beverages',
                'description' => 'Food and beverage products',
                'status' => 'active'
            ],
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'status' => 'active'
            ],
            [
                'name' => 'Clothing',
                'description' => 'Clothing and fashion items',
                'status' => 'active'
            ],
            [
                'name' => 'Health & Beauty',
                'description' => 'Health and beauty products',
                'status' => 'active'
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home and garden supplies',
                'status' => 'active'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
