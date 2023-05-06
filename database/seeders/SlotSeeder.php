<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\DaysOfTheWeekEnum;
use App\Models\BookableCalender;
use App\Helpers\Models\SlotHelper;
use Illuminate\Support\Facades\DB;
use App\Helpers\Models\BookableCalenderHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SlotSeeder extends Seeder
{
    private array $slots = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BookableCalender::all()->each(function ($calender) {
            $bookableCalenderHelper = (new BookableCalenderHelper())->forBookableCalender($calender);

            if (!$bookableCalenderHelper->isAvailable()) {
                return;
            }

            $bookableSlotsHoursInMinutes = $bookableCalenderHelper->generateCalenderSlotHoursInMinutes()
                                                                ->getBookableSlotsHoursInMinutes();

            if (!count($bookableSlotsHoursInMinutes)) {
                return;
            }

            $bookableDate = now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                                ->addDays($calender->day)
                                ->endOfDay();

            $slotHelper = (new SlotHelper)->forService($calender->service);

            while ($slotHelper->fallBetweenFutureBookableDate($bookableDate)) {
                foreach ($bookableSlotsHoursInMinutes as $key => $arrayOfMinutes) {
                    $slotStartDate = $bookableDate->copy()->startOfDay()->addMinutes($arrayOfMinutes[0]);
                    $slotEndDate = $bookableDate->copy()->startOfDay()->addMinutes($arrayOfMinutes[1]);
                    
                    if (
                        $slotStartDate->lessThan(now()) ||
                        $slotHelper->fallOnPlannedOffDate($slotStartDate, $slotEndDate)
                    ) {
                        continue;
                    }

                    $this->slots[] = [
                        'bookable_calender_id' => $calender->id,
                        'start_date' => $slotStartDate,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $bookableDate->addWeek()->endOfDay();
            }
        });

        DB::table('slots')->insert($this->slots);
    }
}
