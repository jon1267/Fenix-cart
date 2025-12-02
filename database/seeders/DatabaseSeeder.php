<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        //User::factory()->create([
        //    'name' => 'Test User',
        //    'email' => 'test@example.com',
        //]);

        //Products seeder
        $products = [
            ['title' => 'iPhone 15', 'price' => 999.00, 'description' => 'Apple smartphone'],
            ['title' => 'MacBook Air', 'price' => 1299.00, 'description' => 'Apple laptop'],
            ['title' => 'AirPods Pro', 'price' => 249.00, 'description' => 'Wireless headphones'],
            ['title' => 'Samsung S24', 'price' => 899.00, 'description' => 'Android smartphone'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

    }
}
