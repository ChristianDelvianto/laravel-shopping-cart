<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Active products
        Product::factory()
                ->count(20)
                ->create();

        // Low stock products
        Product::factory()
                ->count(6)
                ->lowStock()
                ->create();

        // Inactive products
        Product::factory()
                ->count(4)
                ->inactive()
                ->create();
    }
}
