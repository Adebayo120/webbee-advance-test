<?php

namespace App\Helpers\Models;

use App\Models\Slot;
use App\Models\Service;
use App\Helpers\Models\BookableCalenderHelper;
use Carbon\Carbon;

class SlotHelper
{
    private Slot $slot;

    private Service $service;

    private BookableCalenderHelper $bookableCalender;

    private ConfiguredBreakHelper $configuredBreak;

    private int $day;

    private int $startDateInMinutes;

    private int $endDateInMinutes;

    public function forSlot(Slot $slot): self
    {
        $this->slot = $slot;

        $this->bookableCalender = (new BookableCalenderHelper())->forBookableCalender($slot->bookableCalender);

        $this->service = $slot->bookableCalender->service;

        $this->configuredBreak = (new ConfiguredBreakHelper)->forService($this->service);

        $startDate = $this->slot->start_date;

        $this->day = $startDate->dayOfWeek;

        $this->startDateInMinutes = $startDate->hour * 60 + $startDate->minute;

        $this->endDateInMinutes = $this->startDateInMinutes + $this->service->bookable_duration_in_minutes;

        return $this;
    }


    public function forService(Service $service): self
    {
        $this->service = $service;

        $this->configuredBreak = (new ConfiguredBreakHelper)->forService($this->service);

        return $this;
    }

    public function getEndHourInMinutes(?int $startHourInMinutes = null): int
    {
        $startHourInMinutes = $startHourInMinutes ?? $this->startDateInMinutes;

        return $startHourInMinutes + $this->service->bookable_duration_in_minutes;
    }

    public function bookableAppointmentCount(): int
    {
        return $this->service->bookable_appointments_per_slot_count - $this->slot->appointments_count;
    }

    public function isAvailable(int $additionalAppointmentsCount = 0): bool
    {
        return  $this->bookableCalender->isAvailable() && 
                $this->bookableAppointmentCount() >= $additionalAppointmentsCount;
    }

    public function existsInBookableSlots(): bool
    {
        $calenderBookableSlotHoursInMinutes = $this->bookableCalender
                                                    ->generateCalenderSlotHoursInMinutes()
                                                    ->getBookableSlotsHoursInMinutes();
        
        return $this->bookableCalender->dayIsEqual($this->day) &&
                in_array([$this->startDateInMinutes, $this->endDateInMinutes], $calenderBookableSlotHoursInMinutes);
    }

    public function existsInBookableCalender(): bool
    {
        return $this->bookableCalender->dayIsEqual($this->day) &&
                $this->bookableCalender->openingHourIsLessThanOrEqual($this->startDateInMinutes) &&
                $this->bookableCalender->closingHourIsGreaterThanOrEqual($this->endDateInMinutes);
    }

    public function fallBetweenConfiguredBreaks(): bool
    {
        return $this->configuredBreak->whereBetweenHours(
            $this->startDateInMinutes, 
            $this->getEndHourInMinutes()
        )->exists();
    }

    public function addBreaksHoursInMinutes(?int $hourInMinutes = null): int
    {
        $hourInMinutes = $hourInMinutes ?? $this->endDateInMinutes;

        $hourPlusBreakBetweenSlot = $this->addBreakBetweenSlot($hourInMinutes);

        $sumOfConfiguredBreakHoursInMinutes = $this->configuredBreak
                                                ->whereBetweenHours($hourInMinutes, $hourPlusBreakBetweenSlot)
                                                ->sumOfHoursInMinutes();

        return $sumOfConfiguredBreakHoursInMinutes > $this->service->break_between_slots_in_minutes ?
                $hourInMinutes + $sumOfConfiguredBreakHoursInMinutes :
                $hourPlusBreakBetweenSlot;
    }

    public function addBreakBetweenSlot(?int $hourInMinutes = null): int
    {
        $hourInMinutes = $hourInMinutes ?? $this->endDateInMinutes;

        return $hourInMinutes + $this->service->break_between_slots_in_minutes;
    }

    public function fallOnPlannedOffDate(?Carbon $startDate = null, ?Carbon $endDate = null): bool
    {
        $startDate = $startDate ?? $this->slot->start_date;

        $endDate = $endDate ?? $this->slot->start_date->addMinutes(
            $this->service->bookable_duration_in_minutes
        );

        return (bool) (new PlannedOffHelper)
                        ->forService($this->service)
                        ->whereBetween(
                            $startDate, 
                            $endDate
                        )->exists();
    }

    public function fallBetweenFutureBookableDate(?Carbon $startDate = null): bool
    {
        $service = (new ServiceHelper())->forService($this->service);

        $startDate = $startDate ?? $this->slot->start_date;

        return $service->hasFutureBookableDayLimit() ?
                $service->futureBookableDateIsGreaterThanOrEqual($startDate) :
                true;
    }
}