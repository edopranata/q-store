<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolePermissionSeeder::class,
        ]);
        
        // Seed master data
        $this->call([
            CategorySeeder::class,
            UnitSeeder::class,
            PaymentMethodSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            WarehouseSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
