<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ConditionSeeder::class,
            CategorySeeder::class,
            PaymentMethodSeeder::class,
            ItemSeeder::class,
        ]);
    }
}
