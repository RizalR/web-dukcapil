<?php

namespace Database\Factories;

use App\Models\WhistleblowingPublication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhistleblowingPublication>
 */
class WhistleblowingPublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'publication_id' => 'WBP-'.strtoupper(bin2hex(random_bytes(12))),
            'category_code' => 'pelanggaran_prosedur',
            'category_label' => 'Pelanggaran Prosedur',
            'summary' => fake()->text(240),
            'follow_up_result' => fake()->text(320),
            'published_at' => now()->subDay(),
            'source_updated_at' => now()->subHour(),
            'last_synced_at' => now(),
            'is_active' => true,
            'withdrawn_at' => null,
        ];
    }

    public function withdrawn(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
            'withdrawn_at' => now(),
        ]);
    }
}
