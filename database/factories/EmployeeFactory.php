<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'position' => fake()->jobTitle(),
            'unit' => 'Dinas Kependudukan dan Pencatatan Sipil',
            'photo_url' => null,
            'sort_order' => fake()->unique()->numberBetween(1, 1000),
            'is_published' => true,
            'updated_by' => User::factory()->superAdmin(),
        ];
    }
}
