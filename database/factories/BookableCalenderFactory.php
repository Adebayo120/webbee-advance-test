<?php

namespace Database\Factories;

use App\Models\Service;
use App\Enums\DaysInWeekEnum;
use App\Enums\DaysOfTheWeekEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookableCalender>
 */
class BookableCalenderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day' => fake()->randomElement(DaysOfTheWeekEnum::getAllValues()),
            'opening_hour_in_minutes' => fake()->numberBetween(480, 720),
            'closing_hour_in_minutes' => fake()->numberBetween(960, 1080),
            'available' => true,
            'service_id' => Service::factory()
        ];
    }
}
