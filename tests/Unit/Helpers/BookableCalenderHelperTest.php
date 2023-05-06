<?php

use App\Models\Service;
use App\Models\BookableCalender;
use App\Helpers\Models\BookableCalenderHelper;
use App\Models\ConfiguredBreak;

beforeEach(fn() => $this->calender = BookableCalender::factory()->create());

it('can help check that input is equal to calender day', function () {
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->dayIsEqual($this->calender->day)
    )->toBeTrue();
});

it('can help check that input is not equal to calender day', function () {
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->dayIsEqual($this->calender->day + fake()->randomDigitNotNull())
    )->toBeFalse();
});

it('can help check that calender opening hour is less than or equal to input', function () {
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->openingHourIsLessThanOrEqual($this->calender->opening_hour_in_minutes)
    )->toBeTrue();
});

it('can help check that calender opening hour is not less than or equal to input', function () {
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->openingHourIsLessThanOrEqual($this->calender->opening_hour_in_minutes - fake()->randomDigitNotNull())
    )->toBeFalse();
});

it('can help check that calender closing hour is greater than or equal to input', function () {
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->closingHourIsGreaterThanOrEqual($this->calender->closing_hour_in_minutes)
    )->toBeTrue();
});

it('can help check that calender closing hour is not greater than or equal to input', function () {
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->closingHourIsGreaterThanOrEqual($this->calender->closing_hour_in_minutes + fake()->randomDigitNotNull())
    )->toBeFalse();
});

it('can help check that calender closing hour is less than to input', function () {
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->closingHourIsLessThan($this->calender->closing_hour_in_minutes + fake()->randomDigitNotNull())
    )->toBeTrue();
});

it('can help check that calender closing hour is not less than to input', function () {
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->closingHourIsLessThan($this->calender->closing_hour_in_minutes)
    )->toBeFalse();
});

it('can help check that calender is available', function () {
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->isAvailable()
    )->toBeTrue();
});

it('can help check that calender is not available', function () {
    $this->calender->available = false;
    
    expect(
        (new BookableCalenderHelper())
            ->forBookableCalender($this->calender)
            ->isAvailable()
    )->toBeFalse();
});

it('can generate correct calender hours in minutes', function () {
    $service = Service::factory()->create([
        'bookable_duration_in_minutes' => 30,
        'break_between_slots_in_minutes' => 0
    ]);

    $calender = BookableCalender::factory()->for($service)->create([
        'opening_hour_in_minutes' => 480,
        'closing_hour_in_minutes' => 600,
    ]);

    $slots = (new BookableCalenderHelper())
                ->forBookableCalender($calender)
                ->generateCalenderSlotHoursInMinutes()
                ->getBookableSlotsHoursInMinutes();

    expect($slots)->toHaveCount(4);

    expect($slots[0])->toBe([480, 510]);
    expect($slots[1])->toBe([510, 540]);
    expect($slots[2])->toBe([540, 570]);
    expect($slots[3])->toBe([570, 600]);
});

it('can generate correct calender hours in minutes with configured breaks', function () {
    $service = Service::factory()->create([
        'bookable_duration_in_minutes' => 30,
        'break_between_slots_in_minutes' => 0
    ]);

    $calender = BookableCalender::factory()->for($service)->create([
        'opening_hour_in_minutes' => 480,
        'closing_hour_in_minutes' => 600,
    ]);

    ConfiguredBreak::factory()->for($service)->create([
        'start_hour_in_minutes' => 525,
        'end_hour_in_minutes' => 530,
    ]);

    $slots = (new BookableCalenderHelper())
                ->forBookableCalender($calender)
                ->generateCalenderSlotHoursInMinutes()
                ->getBookableSlotsHoursInMinutes();

    expect($slots)->toHaveCount(3);

    expect($slots[0])->toBe([480, 510]);
    expect($slots[1])->toBe([530, 560]);
    expect($slots[2])->toBe([560, 590]);
});

it('can generate correct calender hours in minutes with breaks between slot', function () {
    $service = Service::factory()->create([
        'bookable_duration_in_minutes' => 30,
        'break_between_slots_in_minutes' => 10
    ]);

    $calender = BookableCalender::factory()->for($service)->create([
        'opening_hour_in_minutes' => 480,
        'closing_hour_in_minutes' => 600,
    ]);

    $slots = (new BookableCalenderHelper())
                ->forBookableCalender($calender)
                ->generateCalenderSlotHoursInMinutes()
                ->getBookableSlotsHoursInMinutes();

    expect($slots)->toHaveCount(3);

    expect($slots[0])->toBe([480, 510]);
    expect($slots[1])->toBe([520, 550]);
    expect($slots[2])->toBe([560, 590]);
});

it('can generate correct calender hours in minutes with clashing configured breaks and breaks between slots', function () {
    $service = Service::factory()->create([
        'bookable_duration_in_minutes' => 30,
        'break_between_slots_in_minutes' => 10
    ]);

    $calender = BookableCalender::factory()->for($service)->create([
        'opening_hour_in_minutes' => 480,
        'closing_hour_in_minutes' => 600,
    ]);

    ConfiguredBreak::factory()->for($service)->create([
        'start_hour_in_minutes' => 510,
        'end_hour_in_minutes' => 525,
    ]);

    $slots = (new BookableCalenderHelper())
                ->forBookableCalender($calender)
                ->generateCalenderSlotHoursInMinutes()
                ->getBookableSlotsHoursInMinutes();

    expect($slots)->toHaveCount(3);

    expect($slots[0])->toBe([480, 510]);
    expect($slots[1])->toBe([525, 555]);
    expect($slots[2])->toBe([565, 595]);
});