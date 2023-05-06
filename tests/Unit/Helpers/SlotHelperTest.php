<?php

use App\Models\Slot;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\ConfiguredBreak;
use App\Models\BookableCalender;
use App\Helpers\Models\SlotHelper;
use App\Models\PlannedOff;

it('can get end hour in minutes', function () {
    $startDateMinutes = fake()->randomDigitNotNull();

    $bookableDurationInMinutes = fake()->randomDigitNotNull();

    $service = Service::factory()->create(['bookable_duration_in_minutes' => $bookableDurationInMinutes]);

    $calender = BookableCalender::factory()->for($service)->create();

    $slot = Slot::factory()->for($calender)->create([
        'start_date' => now()->startOfDay()->setMinutes($startDateMinutes)
    ]);

    expect((new SlotHelper)
                ->forSlot($slot)
                ->getEndHourInMinutes()
    )->toBe($startDateMinutes + $bookableDurationInMinutes);
});

it('can get the number of slots bookable appointments', function () {
    $bookableAppointmentsPerSlot = fake()->randomDigitNotNull();

    $service = Service::factory()->create(['bookable_appointments_per_slot_count' => $bookableAppointmentsPerSlot]);

    $calender = BookableCalender::factory()->for($service)->create();

    $slot = Slot::factory()->for($calender)->create();

    Appointment::factory()->for($slot)->create();

    Appointment::factory()->for($slot)->create();

    $slot->loadCount('appointments');

    expect((new SlotHelper)
                ->forSlot($slot)
                ->bookableAppointmentCount()
    )->toBe($bookableAppointmentsPerSlot - 2);
});

it('can check if slot is available', function () {
    $slot = Slot::factory()->create();

    expect((new SlotHelper)
                ->forSlot($slot)
                ->isAvailable()
    )->toBeTrue();
});

it('can check if slot exists in bookable slots', function () {
    $slot = Slot::factory()->create();

    expect((new SlotHelper)
                ->forSlot($slot)
                ->existsInBookableSlots()
    )->toBeTrue();
});

it('can check if slot does not exists in bookable slots', function () {
    $slot = Slot::factory()->create();

    $slot->start_date = $slot->start_date->subHour();

    expect((new SlotHelper)
                ->forSlot($slot)
                ->existsInBookableSlots()
    )->toBeFalse();
});

it('can check if slot exists in bookable calender', function () {
    $slot = Slot::factory()->create();

    expect((new SlotHelper)
                ->forSlot($slot)
                ->existsInBookableCalender()
    )->toBeTrue();
});

it('can check if slot does not exists in bookable calender', function () {
    $slot = Slot::factory()->create();

    $slot->start_date = $slot->start_date->subHour();

    expect((new SlotHelper)
                ->forSlot($slot)
                ->existsInBookableCalender()
    )->toBeFalse();
});

it('can check if slot fall between configured breaks', function () {
    $service = Service::factory()->create();

    $break = ConfiguredBreak::factory()->for($service)->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $slot = Slot::factory()
                ->for($calender)
                ->create();
    
    $slot->start_date = $slot->start_date->startOfDay()->addMinutes($break->start_hour_in_minutes);
    $slot->save();

    expect((new SlotHelper)
                ->forSlot($slot)
                ->fallBetweenConfiguredBreaks()
    )->toBeTrue();
});

it('can check if slot does not fall between configured breaks', function () {
    $break = ConfiguredBreak::factory()->create();

    $calender = BookableCalender::factory()->for($break->service)->create();

    $slot = Slot::factory()->for($calender)->create();

    $slot->start_date = $slot->start_date->subHour();

    expect((new SlotHelper)
                ->forSlot($slot)
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

    $calender = BookableCalender::factory()->for($service)->create();

    $slot = Slot::factory()->for($calender)->create([
        'start_date' => now()->startOfDay()->addMinutes(480)
    ]);

    expect((new SlotHelper)
                ->forSlot($slot)
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

    $slot = Slot::factory()->for($calender)->create([
        'start_date' => now()->startOfDay()->addMinutes(480)
    ]);

    expect((new SlotHelper)
                ->forSlot($slot)
                ->addBreakBetweenSlot()
    )->toBe(505);
});

it('can check if slot fall on planned off date', function () {
    $off = PlannedOff::factory()->create();

    $calender = BookableCalender::factory()->for($off->service)->create();

    $slot = Slot::factory()->for($calender)->create([
        'start_date' => $off->start_date->startOfDay()->addMinutes(480)
    ]);

    expect((new SlotHelper)
                ->forSlot($slot)
                ->fallOnPlannedOffDate()
    )->toBeTrue();
});

it('can check if slot fall between future bookable date', function () {
    $service = Service::factory()->create();

    $calender = BookableCalender::factory()->for($service)->create();

    $slot = Slot::factory()->for($calender)->create([
        'start_date' => now()->addDays($service->future_bookable_days)->startOfDay()->addMinutes(480)
    ]);

    expect((new SlotHelper)
                ->forSlot($slot)
                ->fallBetweenFutureBookableDate()
    )->toBeTrue();
});
