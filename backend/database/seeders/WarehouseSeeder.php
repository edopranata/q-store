<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'code' => 'WH001',
                'address' => 'Jl. Gudang Utama No. 1, Jakarta',
                'phone' => '021-12345678',
                'manager_name' => 'Agus Wijaya',
                'status' => 'active'
            ],
            [
                'name' => 'Branch Warehouse Bandung',
                'code' => 'WH002',
                'address' => 'Jl. Industri No. 25, Bandung',
                'phone' => '022-87654321',
                'manager_name' => 'Rina Sari',
                'status' => 'active'
            ],
            [
                'name' => 'Branch Warehouse Surabaya',
                'code' => 'WH003',
                'address' => 'Jl. Pelabuhan No. 15, Surabaya',
                'phone' => '031-11223344',
                'manager_name' => 'Dedi Kurniawan',
                'status' => 'active'
            ]
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}
