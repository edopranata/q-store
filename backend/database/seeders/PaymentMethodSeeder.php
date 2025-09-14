<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Cash',
                'code' => 'CASH',
                'description' => 'Cash payment',
                'type' => 'cash',
                'status' => 'active'
            ],
            [
                'name' => 'Credit Card',
                'code' => 'CREDIT_CARD',
                'description' => 'Credit card payment',
                'type' => 'card',
                'status' => 'active'
            ],
            [
                'name' => 'Debit Card',
                'code' => 'DEBIT_CARD',
                'description' => 'Debit card payment',
                'type' => 'card',
                'status' => 'active'
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'BANK_TRANSFER',
                'description' => 'Bank transfer payment',
                'type' => 'transfer',
                'status' => 'active'
            ],
            [
                'name' => 'E-Wallet',
                'code' => 'E_WALLET',
                'description' => 'Electronic wallet payment',
                'type' => 'ewallet',
                'status' => 'active'
            ]
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}
