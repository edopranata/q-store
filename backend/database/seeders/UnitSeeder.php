<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Piece',
                'symbol' => 'pcs',
                'description' => 'Individual pieces or items',
                'status' => 'active'
            ],
            [
                'name' => 'Kilogram',
                'symbol' => 'kg',
                'description' => 'Weight measurement in kilograms',
                'status' => 'active'
            ],
            [
                'name' => 'Gram',
                'symbol' => 'g',
                'description' => 'Weight measurement in grams',
                'status' => 'active'
            ],
            [
                'name' => 'Liter',
                'symbol' => 'L',
                'description' => 'Volume measurement in liters',
                'status' => 'active'
            ],
            [
                'name' => 'Milliliter',
                'symbol' => 'mL',
                'description' => 'Volume measurement in milliliters',
                'status' => 'active'
            ],
            [
                'name' => 'Box',
                'symbol' => 'box',
                'description' => 'Packaging unit - box',
                'status' => 'active'
            ],
            [
                'name' => 'Pack',
                'symbol' => 'pack',
                'description' => 'Packaging unit - pack',
                'status' => 'active'
            ]
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
