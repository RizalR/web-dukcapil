<?php

namespace Database\Factories;

use App\LeadershipRole;
use App\Models\LeadershipProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadershipProfile>
 */
class LeadershipProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = fake()->randomElement(LeadershipRole::cases());

        return [
            'role' => $role,
            'name' => fake()->name(),
            'photo_path' => 'leadership/photos/profile.jpg',
            'sort_order' => $role->sortOrder(),
            'is_published' => true,
            'updated_by' => User::factory()->superAdmin(),
        ];
    }

    public function regent(): static
    {
        return $this->state(fn (): array => [
            'role' => LeadershipRole::Regent,
            'sort_order' => LeadershipRole::Regent->sortOrder(),
        ]);
    }

    public function viceRegent(): static
    {
        return $this->state(fn (): array => [
            'role' => LeadershipRole::ViceRegent,
            'sort_order' => LeadershipRole::ViceRegent->sortOrder(),
        ]);
    }

    public function departmentHead(): static
    {
        return $this->state(fn (): array => [
            'role' => LeadershipRole::DepartmentHead,
            'sort_order' => LeadershipRole::DepartmentHead->sortOrder(),
        ]);
    }
}
