<?php

namespace Database\Factories;

use App\Models\NewsArticle;
use App\Models\User;
use App\NewsStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsArticle>
 */
class NewsArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'title' => fake()->unique()->sentence(7),
            'slug' => fake()->unique()->slug(7),
            'excerpt' => fake()->sentence(20),
            'body' => fake()->paragraphs(4, true),
            'status' => NewsStatus::Draft,
            'is_featured' => false,
            'source_url' => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => NewsStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }
}
