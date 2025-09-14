<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT. Sumber Makmur',
                'contact_person' => 'John Doe',
                'phone' => '021-12345678',
                'email' => 'contact@sumbermakmur.com',
                'address' => 'Jl. Raya Jakarta No. 123, Jakarta',
                'status' => 'active'
            ],
            [
                'name' => 'CV. Berkah Jaya',
                'contact_person' => 'Jane Smith',
                'phone' => '021-87654321',
                'email' => 'info@berkahjaya.com',
                'address' => 'Jl. Sudirman No. 456, Jakarta',
                'status' => 'active'
            ],
            [
                'name' => 'UD. Maju Bersama',
                'contact_person' => 'Ahmad Rahman',
                'phone' => '021-11223344',
                'email' => 'admin@majubersama.com',
                'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                'status' => 'active'
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
