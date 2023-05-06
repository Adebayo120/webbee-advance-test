<?php

use App\Helpers\Models\ConfiguredBreakHelper;
use App\Models\ConfiguredBreak;

beforeEach(function () {
    $this->break = ConfiguredBreak::factory()->create([
        'start_hour_in_minutes' => 540,
        'end_hour_in_minutes' => 570
    ]);

    ConfiguredBreak::factory()->for($this->break->service)->create([
        'start_hour_in_minutes' => 640,
        'end_hour_in_minutes' => 670
    ]);
});

it('can get break between hours', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forService($this->break->service)
            ->whereBetweenHours(550, 560)
            ->first()
    )->toBeObject($this->break);
});

it('can get that there is no break between hours', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forService($this->break->service)
            ->whereBetweenHours(580, 600)
            ->first()
    )->toBeNull();
});

it('can count number of breaks between hours', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forService($this->break->service)
            ->whereBetweenHours(480, 700)
            ->count()
    )->toBe(2);
});

it('can get sum of breaks hours between input', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forService($this->break->service)
            ->whereBetweenHours(480, 700)
            ->sumOfHoursInMinutes()
    )->toBe(60);
});

it('can check that break start hour is equal to input', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forBreak($this->break)
            ->startHourInMinutesIsEqual($this->break->start_hour_in_minutes)
    )->toBeTrue();
});

it('can check that break start hour is less than input', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forBreak($this->break)
            ->startHourInMinutesIsLessThan($this->break->start_hour_in_minutes + fake()->randomDigitNotNull())
    )->toBeTrue();
});

it('can check that break start hour is greater than input', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forBreak($this->break)
            ->startHourInMinutesIsGreaterThan($this->break->start_hour_in_minutes - fake()->randomDigitNotNull())
    )->toBeTrue();
});

it('can check that break end hour is equal to input', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forBreak($this->break)
            ->endHourInMinutesIsEqual($this->break->end_hour_in_minutes)
    )->toBeTrue();
});

it('can check that break end hour is less than input', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forBreak($this->break)
            ->endHourInMinutesIsLessThan($this->break->end_hour_in_minutes + fake()->randomDigitNotNull())
    )->toBeTrue();
});

it('can check that break end hour is greater than input', function () {
    expect(
        (new ConfiguredBreakHelper())
            ->forBreak($this->break)
            ->endHourInMinutesIsGreaterThan($this->break->end_hour_in_minutes - fake()->randomDigitNotNull())
    )->toBeTrue();
});