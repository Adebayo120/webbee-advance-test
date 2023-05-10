<?php

namespace App\Helpers\Models;

use Carbon\Carbon;
use App\Models\Service;
use App\Models\Appointment;
use App\Enums\DaysOfTheWeekEnum;
use Illuminate\Database\Eloquent\Collection;
use App\Helpers\Models\BookableCalenderHelper;

class SlotHelper
{
    private Service $service;

    private ServiceHelper $serviceHelper;

    private BookableCalenderHelper $bookableCalender;

    private ConfiguredBreakHelper $configuredBreak;

    private Carbon $startDate;

    private Carbon $endDate;

    private Collection $availableBookableCalenders;

    private Collection $bookedAppointments;

    private int $day;

    private int $startDateInMinutes;

    private int $endDateInMinutes;

    private array $availableSlots = [];

    private array $availableDates = [];

    public function __construct()
    {
        $this->startDate = now()->startOfDay();
    }

    public function forSlot(Carbon $startDate): self
    {
        $this->startDate = $startDate;

        if ($bookableCalender = $this->serviceHelper->bookableCalenderForSlotDate($startDate)) {
            $this->bookableCalender = (new BookableCalenderHelper())->forBookableCalender($bookableCalender);
        }


        $this->configuredBreak = (new ConfiguredBreakHelper)->forService($this->service);

        $this->day = $startDate->dayOfWeek;

        $this->startDateInMinutes = $startDate->hour * 60 + $startDate->minute;

        $this->endDateInMinutes = $this->startDateInMinutes + $this->service->bookable_duration_in_minutes;

        return $this;
    }

    public function whereBetween(Carbon $startDate, Carbon $endDate): self
    {
        $this->startDate = $startDate;

        $this->endDate = $endDate;

        return $this;
    }

    public function forService(Service $service): self
    {
        $this->service = $service;

        $this->serviceHelper = (new ServiceHelper)->forService($service);

        $this->configuredBreak = (new ConfiguredBreakHelper)->forService($this->service);

        $this->endDate = $this->endDate ?? (new ServiceHelper)->forService($service)->futureBookableDate();

        return $this;
    }

    public function forAvailableBookableCalenders(): self
    {
        $this->availableBookableCalenders = $this->service->availableBookableCalenders->filter(function ($calender) {
            return $this->startDate->diffInDaysFiltered(function (Carbon $date) use($calender){
                return $date->dayOfWeek == $calender->day;
            }, $this->endDate);
        });

        return $this;
    }

    public function generateBookedAppointmentsBetweenDates(?Carbon $startDate = null, ?Carbon $endDate = null): self
    {
        $startDate = $startDate ?? $this->startDate;

        $endDate = $endDate ?? $this->getEndDate();

        $this->bookedAppointments = Appointment::whereBetween('end_date', [$startDate, $endDate])
                                                ->get();

        return $this;
    }

    public function generateBookedAppointments(?Carbon $startDate = null, ?Carbon $endDate = null): self
    {
        $startDate = $startDate ?? $this->startDate;

        $endDate = $endDate ?? $this->getEndDate();

        $this->bookedAppointments = Appointment::where('start_date', $startDate)
                                                ->where('end_date', $endDate)
                                                ->get();

        return $this;
    }

    public function getAppointmentCount(): int
    {
        return $this->bookedAppointments->count();
    }

    public function getEndDate(): Carbon
    {
        return $this->startDate->copy()->addMinutes($this->service->bookable_duration_in_minutes);
    }

    public function getStartDate(): Carbon
    {
        return $this->startDate;
    }

    public function startDateIsGreaterThanNow(): bool
    {
        return $this->startDate->greaterThan(now());
    }

    public function getEndHourInMinutes(?int $startHourInMinutes = null): int
    {
        $startHourInMinutes = $startHourInMinutes ?? $this->startDateInMinutes;

        return $startHourInMinutes + $this->service->bookable_duration_in_minutes;
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
        $startDate = $startDate ?? $this->startDate;

        $endDate = $endDate ?? $this->getEndDate();

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

        $startDate = $startDate ?? $this->startDate;

        return $service->hasFutureBookableDayLimit() ?
                $service->futureBookableDateIsGreaterThanOrEqual($startDate) :
                true;
    }

    public function generateAvailableSlots(): self
    {
        $this->availableBookableCalenders->each(function ($calender) {
            $bookableCalenderHelper = (new BookableCalenderHelper())->forBookableCalender($calender);

            $bookableSlotsHoursInMinutes = $bookableCalenderHelper->generateCalenderSlotHoursInMinutes()
                                                                ->getBookableSlotsHoursInMinutes();

            if (!count($bookableSlotsHoursInMinutes)) {
                return;
            }

            $startDate = $this->startDate->greaterThanOrEqualTo(now()->startOfDay()) ?
                            $this->startDate :
                            now();

            $bookableDate = $startDate->copy()->startOfWeek(DaysOfTheWeekEnum::SUNDAY->value)
                                    ->addDays($calender->day)
                                    ->endOfDay();
            while (
                $this->fallBetweenFutureBookableDate($bookableDate) && 
                $bookableDate->lessThanOrEqualTo($this->endDate)
            ) {
                $availableSlots = [];
                foreach ($bookableSlotsHoursInMinutes as $key => $arrayOfMinutes) {
                    $slotStartDate = $bookableDate->copy()->startOfDay()->addMinutes($arrayOfMinutes[0]);
                    $slotEndDate = $bookableDate->copy()->startOfDay()->addMinutes($arrayOfMinutes[1]);
                    
                    $bookedAppointmentsCount = $this->bookedAppointmentsCountForDate($slotStartDate, $slotEndDate);

                    if (
                        $slotStartDate->lessThan(now()) ||
                        $bookedAppointmentsCount >= $this->service->bookable_appointments_per_slot_count ||
                        $this->fallOnPlannedOffDate($slotStartDate, $slotEndDate)
                    ) {
                        continue;
                    }
                
                    $availableSlots[] = [
                        'start_date' => $slotStartDate->timestamp,
                        'bookable_appointments_count' => $this->bookableAppointmentCount($slotStartDate, $slotEndDate)
                    ];
                }

                $this->availableSlots = [...$this->availableSlots, ...$availableSlots];
                
                if (count($this->availableSlots)) {
                    $this->availableDates[] = $slotStartDate->startOfDay()->toDateTimeString();
                }

                $bookableDate->addWeek()->endOfDay();
            }
        });

        return $this;
    }

    public function bookableAppointmentCount(?Carbon $startDate = null, ?Carbon $endDate = null): int
    {
        $startDate = $startDate ?? $this->getStartDate();

        $endDate = $endDate ?? $this->getEndDate();

        return $this->service->bookable_appointments_per_slot_count - $this->generateBookedAppointments($startDate, $endDate)->getAppointmentCount();
    }

    public function bookedAppointmentsCountForDate(Carbon $startDate, Carbon $endDate): int
    {
        return $this->bookedAppointments->where('start_date', $startDate)
                                        ->where('end_date', $endDate)
                                        ->count();
    }

    public function getAvailableSlots (): array
    {
        return $this->availableSlots;
    }

    public function getAvailableDates(): array
    {
        return $this->availableDates;
    }
}