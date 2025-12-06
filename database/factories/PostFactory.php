<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        $title = $this->faker->sentence();
        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'excerpt' => $this->faker->text(120),
            'body' => $this->faker->paragraphs(5, true),
            'featured_image' => null,
            'category' => $this->faker->randomElement(['General','News','Tutorial']),
            'tags' => [$this->faker->word(), $this->faker->word()],
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'status' => $this->faker->randomElement(['draft','published']),
            'published_at' => $this->faker->dateTimeBetween('-1 year','now'),
        ];
    }
}
