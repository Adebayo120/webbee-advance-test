<?php

namespace App\Helpers\Models;

use App\Models\BookableCalender;
use App\Models\Service;
use Carbon\Carbon;

class ServiceHelper
{
    private Service $service;

    public function forService(Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function hasFutureBookableDayLimit(): bool
    {
        return (bool) $this->service->future_bookable_days;
    }

    public function futureBookableDate(): Carbon
    {
        return now()->addDays($this->service->future_bookable_days)->endOfDay();
    }

    public function futureBookableDateIsGreaterThanOrEqual(Carbon $date): bool
    {
        return $this->futureBookableDate()->greaterThanOrEqualTo($date);
    }

    public function bookableCalenderForSlotDate(Carbon $startDate): ?BookableCalender
    {
        return $this->service->bookableCalender()
                        ->where('day', $startDate->dayOfWeek)
                        ->first();
    }
}