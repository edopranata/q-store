<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Walk-in Customer',
                'phone' => null,
                'email' => null,
                'address' => null,
                'status' => 'active'
            ],
            [
                'name' => 'Budi Santoso',
                'phone' => '081234567890',
                'email' => 'budi.santoso@email.com',
                'address' => 'Jl. Merdeka No. 123, Jakarta',
                'status' => 'active'
            ],
            [
                'name' => 'Siti Nurhaliza',
                'phone' => '081987654321',
                'email' => 'siti.nurhaliza@email.com',
                'address' => 'Jl. Pahlawan No. 456, Bandung',
                'status' => 'active'
            ],
            [
                'name' => 'PT. Mitra Sejahtera',
                'phone' => '021-55667788',
                'email' => 'procurement@mitrasejahtera.com',
                'address' => 'Jl. Industri No. 789, Surabaya',
                'status' => 'active'
            ]
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
