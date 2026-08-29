<?php

namespace Database\Factories;

use App\Models\HomepageSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageSetting>
 */
class HomepageSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'background_image_path' => null,
            'updated_by' => null,
        ];
    }
}
