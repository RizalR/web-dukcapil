<?php

namespace Database\Factories;

use App\Models\PublicDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublicDocument>
 */
class PublicDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(5),
            'slug' => fake()->unique()->slug(5),
            'category' => fake()->randomElement(['Standar Pelayanan', 'Regulasi', 'Perencanaan']),
            'year' => now()->year,
            'description' => fake()->sentence(14),
            'url' => fake()->url(),
            'published_at' => now()->subDay(),
            'updated_by' => User::factory()->superAdmin(),
        ];
    }
}
