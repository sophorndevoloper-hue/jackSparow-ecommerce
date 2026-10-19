<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductModel>
 */
class ProductModelFactory extends Factory
{
    protected $model = ProductModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'brand_id' => Brand::factory(),
            'name' => ucwords($name),
            'slug' => str()->slug($name),
            'image' => null,
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
