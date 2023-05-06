<?php

use App\Helpers\Models\PlannedOffHelper;
use App\Models\PlannedOff;

beforeEach(function(){
    $this->off = PlannedOff::factory()->create([
        'start_date' => now()->startOfDay()->setMinutes(480),
        'end_date' => now()->startOfDay()->setMinutes(600)
    ]);
});

it('checks if planned off date falls on input date', function () {
    expect((new PlannedOffHelper)
                ->forService($this->off->service)
                ->whereBetween(
                    now()->startOfDay()->setMinutes(500), 
                    now()->startOfDay()->setMinutes(550)
                )->exists()
    )->toBeTrue();
});

it('checks if start date is equal to input date', function () {
    expect((new PlannedOffHelper)
                ->forPlannedOff($this->off)
                ->startDateIsEqual($this->off->start_date)
    )->toBeTrue();
});

it('checks if start date is less than input date', function () {
    expect((new PlannedOffHelper)
                ->forPlannedOff($this->off)
                ->startDateIsLessThan(
                    $this->off->start_date->addMinutes(fake()->randomDigitNotNull())
                )
    )->toBeTrue();
});

it('checks if start date is greater than input date', function () {
    expect((new PlannedOffHelper)
                ->forPlannedOff($this->off)
                ->startDateIsGreaterThan(
                    $this->off->start_date->subMinutes(fake()->randomDigitNotNull())
                )
    )->toBeTrue();
});

it('checks if end date is equal to input date', function () {
    expect((new PlannedOffHelper)
                ->forPlannedOff($this->off)
                ->endDateIsEqual($this->off->end_date)
    )->toBeTrue();
});

it('checks if end date is less than input date', function () {
    expect((new PlannedOffHelper)
                ->forPlannedOff($this->off)
                ->endDateIsLessThan(
                    $this->off->end_date->addMinutes(fake()->randomDigitNotNull())
                )
    )->toBeTrue();
});

it('checks if end date is greater than input date', function () {
    expect((new PlannedOffHelper)
                ->forPlannedOff($this->off)
                ->endDateIsGreaterThan(
                    $this->off->end_date->subMinutes(fake()->randomDigitNotNull())
                )
    )->toBeTrue();
});