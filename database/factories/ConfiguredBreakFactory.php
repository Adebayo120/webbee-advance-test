<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConfiguredBreak>
 */
class ConfiguredBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Launch Break', 'Coffee Break', 'Cleaning Break']),
            'start_hour_in_minutes' => fake()->numberBetween(600, 630),
            'end_hour_in_minutes' => fake()->numberBetween(630, 660),
            'service_id' => Service::factory()
        ];
    }
}
