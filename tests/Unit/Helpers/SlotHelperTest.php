<?php

use App\Models\Slot;
use App\Models\Service;
use App\Models\PlannedOff;
use App\Models\Appointment;
use App\Models\ConfiguredBreak;
use App\Enums\DaysOfTheWeekEnum;
use App\Models\BookableCalender;
use App\Helpers\Models\SlotHelper;

it('can get end hour in minutes', function () {
    $startDateMinutes = fake()->randomDigitNotNull();

    $bookableDurationInMinutes = fake()->randomDigitNotNull();

    $service = Service::factory()->create(['bookable_duration_in_minutes' => $bookableDurationInMinutes]);

    $startDate = now()->startOfDay()->setMinutes($startDateMinutes);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)
                ->getEndHourInMinutes()
    )->toBe($startDateMinutes + $bookableDurationInMinutes);
});

it('can get the number of slots bookable appointments', function () {
    $service = Service::factory()->create(['bookable_appointments_per_slot_count' => 4]);

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    $slot = (new SlotHelper)->forService($service)->forSlot($startDate);

    Appointment::factory()->count(2)->create([
        'start_date' => $slot->getStartDate(),
        'end_date' => $slot->getEndDate(),
    ]);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)
                ->bookableAppointmentCount()
    )->toBe(2);
});

it('can check if slot is available', function () {
    $service = Service::factory()->create([
        'bookable_appointments_per_slot_count' => $num = fake()->randomDigitNotNull()
    ]);

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)
                ->isAvailable()
    )->toBeTrue();
});

it('can check if slot exists in bookable slots', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)
                ->existsInBookableSlots()
    )->toBeTrue();
});

it('can check if slot does not exists in bookable slots', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate->subHour())
                ->existsInBookableSlots()
    )->toBeFalse();
});

it('can check if slot exists in bookable calender', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)
                ->existsInBookableCalender()
    )->toBeTrue();
});

it('can check if slot does not exists in bookable calender', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate->subHour())
                ->existsInBookableCalender()
    )->toBeFalse();
});

it('can check if slot fall between configured breaks', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();
    
    ConfiguredBreak::factory()->for($service)->create([
        'start_hour_in_minutes' => $calender->opening_hour_in_minutes
    ]);

    $startDate = now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)
                ->fallBetweenConfiguredBreaks()
    )->toBeTrue();
});

it('can check if slot does not fall between configured breaks', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)                
                ->fallBetweenConfiguredBreaks()
    )->toBeFalse();
});

it('can add breaks hours in minutes to input', function () {

    $service = Service::factory()->create([
        'bookable_duration_in_minutes' => 15,
        'break_between_slots_in_minutes' => 10
    ]);

    ConfiguredBreak::factory()->for($service)->create([
        'start_hour_in_minutes' => 495,
        'end_hour_in_minutes' => 510
    ]);

    $startDate = now()->startOfDay()->addMinutes(480);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)
                ->addBreaksHoursInMinutes()
    )->toBe(510);
});

it('can add breaks between slots', function () {
    $breakBetweenSlot = 10;

    $service = Service::factory()->create([
        'bookable_duration_in_minutes' => 15,
        'break_between_slots_in_minutes' => $breakBetweenSlot
    ]);

    ConfiguredBreak::factory()->for($service)->create([
        'start_hour_in_minutes' => 495,
        'end_hour_in_minutes' => 510
    ]);

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate = now()->startOfDay()->addMinutes(480);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)                
                ->addBreakBetweenSlot()
    )->toBe(505);
});

it('can check if slot fall on planned off date', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();


    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);
    
    PlannedOff::factory()->for($service)->create([
        'start_date' => $startDate->copy()->startOfDay(),
        'end_date' => $startDate->copy()->endOfDay(),
    ]);

    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)                 
                ->fallOnPlannedOffDate()
    )->toBeTrue();
});

it('can check if slot fall between future bookable date', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);
                
    expect((new SlotHelper)
                ->forService($service)
                ->forSlot($startDate)                
                ->fallBetweenFutureBookableDate()
    )->toBeTrue();
});
