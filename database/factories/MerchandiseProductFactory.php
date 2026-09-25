<?php

namespace Database\Factories;

use App\Models\MerchandiseProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MerchandiseProduct>
 */
class MerchandiseProductFactory extends Factory
{
    protected $model = MerchandiseProduct::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(50000, 500000),
            'images' => [],
            'shop_url' => fake()->boolean(50) ? fake()->url() : null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
