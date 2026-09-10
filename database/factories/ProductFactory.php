<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $cost = fake()->randomFloat(2, 20, 500);
        $price = round($cost * 1.3, 2);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => ucwords($name),
            'slug' => str()->slug($name).'-'.fake()->unique()->randomNumber(4),
            'sku' => strtoupper(fake()->bothify('COMP-###-???')),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(2, true),
            'price' => $price,
            'sale_price' => fake()->boolean(30) ? round($price * 0.9, 2) : null,
            'cost_price' => $cost,
            'stock_quantity' => fake()->numberBetween(0, 50),
            'low_stock_threshold' => 5,
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'specifications' => [
                'Interface' => 'PCIe 4.0',
                'Warranty' => '3 Years',
                'Form Factor' => 'Standard',
            ],
            'warranty_period' => '3 Years',
        ];
    }
}
