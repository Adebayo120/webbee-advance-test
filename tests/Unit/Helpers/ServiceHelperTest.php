<?php

use App\Helpers\Models\ServiceHelper;
use App\Models\Service;

beforeEach(fn() => $this->service = Service::factory()->create());

it('can check if service has a future bookable day limit', function () {
    expect((new ServiceHelper())
                ->forService($this->service)
                ->hasFutureBookableDayLimit()
    )->toBeTrue();
});

it('can check if service does not have future bookable day limit', function () {
    $this->service->future_bookable_days = null;

    expect((new ServiceHelper())
                ->forService($this->service)
                ->hasFutureBookableDayLimit()
    )->toBeFalse();
});

it('can generate service future bookable day limit', function () {
    $this->service->future_bookable_days = null;

    expect((new ServiceHelper())
                ->forService($this->service)
                ->futureBookableDate()
                ->toDateString()
    )->toBe(
        now()->addDays($this->service->future_bookable_days)->endOfDay()->toDateString()
    );
});

it('can check future bookable date is greater than or equal input', function () {
    expect((new ServiceHelper())
                ->forService($this->service)
                ->futureBookableDateIsGreaterThanOrEqual(now())
    )->toBeTrue();
});
