<?php

namespace Database\Seeders;

use App\Enums\DaysOfTheWeekEnum;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\matches;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BookableCalenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];

        Service::whereIn('name', ['Men Haircut', 'Women Haircut'])->get()->each(function ($service) use(&$data) {
            $data = [...$data, ...$this->getData($service)];
        });
        
        DB::table('bookable_calenders')->insert($data);
    }

    private function getData(Service $service): array
    {
        return [
            DaysOfTheWeekEnum::SUNDAY->value =>  [
                'day' => DaysOfTheWeekEnum::SUNDAY->value,
                'opening_hour_in_minutes' => null,
                'closing_hour_in_minutes' => null,
                'available' => false,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            DaysOfTheWeekEnum::MONDAY->value =>  [
                'day' => DaysOfTheWeekEnum::MONDAY->value,
                'opening_hour_in_minutes' => 480,
                'closing_hour_in_minutes' => 1320,
                'available' => true,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            DaysOfTheWeekEnum::TUESDAY->value =>  [
                'day' => DaysOfTheWeekEnum::TUESDAY->value,
                'opening_hour_in_minutes' => 480,
                'closing_hour_in_minutes' => 1320,
                'available' => true,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            DaysOfTheWeekEnum::WEDNESDAY->value =>  [
                'day' => DaysOfTheWeekEnum::WEDNESDAY->value,
                'opening_hour_in_minutes' => 480,
                'closing_hour_in_minutes' => 1320,
                'available' => true,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            DaysOfTheWeekEnum::THURSDAY->value =>  [
                'day' => DaysOfTheWeekEnum::THURSDAY->value,
                'opening_hour_in_minutes' => 480,
                'closing_hour_in_minutes' => 1320,
                'available' => true,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            DaysOfTheWeekEnum::FRIDAY->value =>  [
                'day' => DaysOfTheWeekEnum::FRIDAY->value,
                'opening_hour_in_minutes' => 480,
                'closing_hour_in_minutes' => 1320,
                'available' => true,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            DaysOfTheWeekEnum::SATURDAY->value =>  [
                'day' => DaysOfTheWeekEnum::SATURDAY->value,
                'opening_hour_in_minutes' => 600,
                'closing_hour_in_minutes' => 1320,
                'available' => true,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
    }
}
