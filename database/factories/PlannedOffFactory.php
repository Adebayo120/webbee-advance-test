<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlannedOff>
 */
class PlannedOffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'start_date' => $startDay = now()->addDays(fake()->numberBetween(1, 3))->startOfDay(),
            'end_date' => $startDay->copy()->endOfDay(),
            'service_id' => Service::factory()
        ];
    }
}
