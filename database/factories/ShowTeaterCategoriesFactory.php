<?php

namespace Database\Factories;

use App\Models\ShowTeaterCategories;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShowTeaterCategories>
 */
class ShowTeaterCategoriesFactory extends Factory
{
    protected $model = ShowTeaterCategories::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => ShowTeaterCategories::TYPE_SETLIST,
            'name' => 'Setlist '.fake()->unique()->word(),
            'jp_name' => null,
            'setlist_id' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the category is a unit song under the given setlist.
     */
    public function unitSong(?ShowTeaterCategories $setlist = null): static
    {
        return $this->state(fn (): array => [
            'type' => ShowTeaterCategories::TYPE_UNIT_SONG,
            'name' => 'Unit '.fake()->unique()->word(),
            'setlist_id' => $setlist?->getKey(),
        ]);
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
