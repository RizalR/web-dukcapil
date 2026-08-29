<?php

namespace Database\Factories;

use App\LinkCategory;
use App\Models\ExternalLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExternalLink>
 */
class ExternalLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'category' => LinkCategory::EmployeeApp,
            'description' => fake()->sentence(),
            'url' => fake()->url(),
            'is_active' => true,
            'sort_order' => fake()->unique()->numberBetween(1, 1000),
            'last_status_code' => null,
            'last_check_ok' => null,
            'last_checked_at' => null,
        ];
    }
}
