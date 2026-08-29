<?php

namespace Database\Factories;

use App\Models\PrintingStatus;
use App\Models\User;
use App\PrintingDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrintingStatus>
 */
class PrintingStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location' => fake()->unique()->company().' Loket Dukcapil',
            'document' => PrintingDocument::ElectronicIdentityCard,
            'sort_order' => PrintingDocument::ElectronicIdentityCard->sortOrder(),
            'is_available' => fake()->boolean(),
            'note' => fake()->sentence(),
            'source' => 'Pembaruan petugas Dukcapil',
            'checked_at' => now(),
            'updated_by' => User::factory()->superAdmin(),
        ];
    }

    public function childIdentityCard(): static
    {
        return $this->state(fn (): array => [
            'document' => PrintingDocument::ChildIdentityCard,
            'sort_order' => PrintingDocument::ChildIdentityCard->sortOrder(),
        ]);
    }
}
