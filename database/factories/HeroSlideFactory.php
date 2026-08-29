<?php

namespace Database\Factories;

use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSlide>
 */
class HeroSlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->sentence(3),
            'image_path' => 'homepage/slides/example.jpg',
            'alt_text' => fake()->sentence(),
            'caption' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(0, 20),
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
            'updated_by' => User::factory(),
        ];
    }
}
