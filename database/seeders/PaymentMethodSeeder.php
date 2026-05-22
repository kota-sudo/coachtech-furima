<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            'コンビニ払い',
            'カード支払い',
        ];

        foreach ($paymentMethods as $name) {
            PaymentMethod::create(['name' => $name]);
        }
    }
}
