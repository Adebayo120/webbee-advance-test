<?php

use App\Models\Service;
use App\Models\PlannedOff;
use App\Models\Appointment;
use App\Models\ConfiguredBreak;
use App\Enums\DaysOfTheWeekEnum;
use App\Models\BookableCalender;
use App\Helpers\Models\SlotHelper;

test('request require slot id', function () {
    $response = $this->postJson('api/v1/book/appointment');

    $response->assertInvalid('service_id');
});

test('request validate slot exists on database', function () {
    $slotId = fake()->randomDigitNotNull();

    $response = $this->postJson("api/v1/book/appointment", [
        'service_id' => $slotId
    ]);

    $response->assertInvalid(['service_id' => 'id is invalid']);
});

test('request require profiles', function () {
    $response = $this->postJson('api/v1/book/appointment');

    $response->assertInvalid('profiles');
});

test('request validates slot is available', function () {
    $service = Service::factory()->create([
        'bookable_appointments_per_slot_count' => $num = fake()->randomDigitNotNull()
    ]);

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    $slot = (new SlotHelper)->forService($service)->forSlot($startDate);

    Appointment::factory()->count($num)->create([
        'start_date' => $slot->getStartDate(),
        'end_date' => $slot->getEndDate(),
    ]);

    $response = $this->postJson('api/v1/book/appointment', [
        'service_id' => $service->id,
        'start_date_in_timestamp' => $startDate->timestamp,
        'profiles' => [[
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName()
        ]]
    ]);

    $response->assertInvalid(['slot' => __('validation.custom.slot.is-available')]);
});

test('request validates slot is on planned off date', function () {
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

    $response = $this->postJson('api/v1/book/appointment', [
        'service_id' => $service->id,
        'start_date_in_timestamp' => $startDate->timestamp,
        'profiles' => [[
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName()
        ]]
    ]);

    $response->assertInvalid(['slot' => __('validation.custom.slot.not-fall-on-planned-off-date')]);
});

test('request validates slot exist on bookable calender', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    $response = $this->postJson('api/v1/book/appointment', [
        'service_id' => $service->id,
        'start_date_in_timestamp' => $startDate->subMinute()->timestamp,
        'profiles' => [[
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName()
        ]]
    ]);

    $response->assertInvalid(['slot' => __('validation.custom.slot.exists-in-bookable-calender')]);
});

test('request validates slot exist on bookable slot', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    $response = $this->postJson('api/v1/book/appointment', [
        'service_id' => $service->id,
        'start_date_in_timestamp' => $startDate->subMinute()->timestamp,
        'profiles' => [[
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName()
        ]]
    ]);

    $response->assertInvalid(['slot' => __('validation.custom.slot.exists-in-bookable-slots')]);
});

test('request validates slot falls on future bookable date', function () {
    $service = Service::factory()->create([
        'future_bookable_days' => $days = fake()->randomDigitNotNull()
    ]);

    $calender = BookableCalender::factory()->for($service)->create();

    $startDate =now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->addWeeks($days)
                    ->setMinutes($calender->opening_hour_in_minutes);

    $response = $this->postJson('api/v1/book/appointment', [
        'service_id' => $service->id,
        'start_date_in_timestamp' => $startDate->subMinute()->timestamp,
        'profiles' => [[
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName()
        ]]
    ]);

    $response->assertInvalid(['slot' => __('validation.custom.slot.fall-between-future-bookable-date')]);
});

test('request validates slot does not fall on configured break', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();
    
    ConfiguredBreak::factory()->for($service)->create([
        'start_hour_in_minutes' => $calender->opening_hour_in_minutes
    ]);

    $startDate = now()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                    ->addDays($calender->day)
                    ->startOfDay()
                    ->setMinutes($calender->opening_hour_in_minutes);

    $response = $this->postJson('api/v1/book/appointment', [
        'service_id' => $service->id,
        'start_date_in_timestamp' => $startDate->timestamp,
        'profiles' => [[
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName()
        ]]
    ]);

    $response->assertInvalid(['slot' => __('validation.custom.slot.not-fall-between-configured-breaks')]);
});