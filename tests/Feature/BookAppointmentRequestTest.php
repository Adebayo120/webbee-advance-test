<?php

use App\Models\Slot;
use App\Models\Service;
use App\Models\PlannedOff;
use App\Models\Appointment;
use App\Models\BookableCalender;
use App\Models\ConfiguredBreak;

test('request require slot id', function () {
    $response = $this->postJson('api/v1/book/appointment');

    $response->assertInvalid('slot_id');
});

test('request validate slot exists on database', function () {
    $slotId = fake()->randomDigitNotNull();

    $response = $this->postJson("api/v1/book/appointment", [
        'slot_id' => $slotId
    ]);

    $response->assertInvalid(['slot_id' => 'id is invalid']);
});

test('request require profiles', function () {
    $response = $this->postJson('api/v1/book/appointment');

    $response->assertInvalid('profiles');
});

test('request validates slot is available', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $slot = Slot::factory()
                ->for($calender)
                ->has(
                    Appointment::factory()->count($service->bookable_appointments_per_slot_count)
                )->create();

    $response = $this->postJson('api/v1/book/appointment', [
        'slot_id' => $slot->id,
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

    $slot = Slot::factory()
                ->for($calender)
                ->create();
    
    PlannedOff::factory()->for($service)->create([
        'start_date' => $slot->start_date->startOfDay(),
        'end_date' => $slot->start_date->endOfDay(),
    ]);

    $response = $this->postJson('api/v1/book/appointment', [
        'slot_id' => $slot->id,
        'profiles' => [[
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName()
        ]]
    ]);

    $response->assertInvalid(['slot' => __('validation.custom.slot.not-fall-on-planned-off-date')]);
});

test('request validates slot exist on bookable calender', function () {
    $slot = Slot::factory()->create();
    
    $calender = $slot->bookableCalender;
    $calender->day = $calender->day + 1;
    $calender->save();

    $response = $this->postJson('api/v1/book/appointment', [
        'slot_id' => $slot->id,
        'profiles' => [[
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName()
        ]]
    ]);

    $response->assertInvalid(['slot' => __('validation.custom.slot.exists-in-bookable-calender')]);
});

test('request validates slot exist on bookable slot', function () {
    $slot = Slot::factory()->create();

    $slot->start_date = $slot->start_date->addMinute();
    $slot->save();

    $response = $this->postJson('api/v1/book/appointment', [
        'slot_id' => $slot->id,
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
        'future_bookable_days' => 1
    ]);

    $calender = BookableCalender::factory()->for($service)->create();

    $slot = Slot::factory()
                ->for($calender)
                ->create();
    
    $slot->start_date = $slot->start_date->addWeek();
    $slot->save();

    $response = $this->postJson('api/v1/book/appointment', [
        'slot_id' => $slot->id,
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

    $break = ConfiguredBreak::factory()->for($service)->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $slot = Slot::factory()
                ->for($calender)
                ->create();
    
    $slot->start_date = $slot->start_date->startOfDay()->addMinutes($break->start_hour_in_minutes);
    $slot->save();

    $response = $this->postJson('api/v1/book/appointment', [
        'slot_id' => $slot->id,
        'profiles' => [[
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName()
        ]]
    ]);

    $response->assertInvalid(['slot' => __('validation.custom.slot.not-fall-between-configured-breaks')]);
});