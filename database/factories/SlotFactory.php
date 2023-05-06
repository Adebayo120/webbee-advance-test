<?php

namespace Database\Factories;

use App\Enums\DaysOfTheWeekEnum;
use App\Models\BookableCalender;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Slot>
 */
class SlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bookableCalender = BookableCalender::factory()->create();

        $openingDate = now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                            ->addDays($bookableCalender->day)
                            ->startOfDay()
                            ->setMinutes($bookableCalender->opening_hour_in_minutes);

        return [
            'start_date' => $openingDate,
            'bookable_calender_id' => $bookableCalender->id
        ];
    }
}
