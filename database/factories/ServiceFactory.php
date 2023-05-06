<?php

namespace Database\Factories;

use App\Models\BusinessAdministrator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->jobTitle,
            'business_administrator_id' => BusinessAdministrator::factory(),
            'bookable_duration_in_minutes' => fake()->randomElement([10, 20, 30]),
            'break_between_slots_in_minutes' => fake()->randomElement([5, 10]),
            'future_bookable_days' => fake()->randomDigitNotNull(),
            'bookable_appointments_per_slot_count' => fake()->numberBetween(1, 5),
        ];
    }
}
