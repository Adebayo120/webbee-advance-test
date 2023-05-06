<?php

namespace App\Helpers\Models;

use App\Models\Service;
use App\Models\BookableCalender;
use App\Helpers\Models\ConfiguredBreakHelper;

class BookableCalenderHelper
{
    private BookableCalender $bookableCalender;

    private Service $service;

    private ConfiguredBreakHelper $configuredBreak;

    private SlotHelper $slot;

    private array $bookableSlotsHoursInMinutes = [];

    public function forBookableCalender(BookableCalender $bookableCalender): self
    {
        $this->bookableCalender = $bookableCalender;
        
        $this->service = $this->bookableCalender->service;

        return $this;
    }

    public function dayIsEqual(int $day): bool
    {
        return $this->bookableCalender->day == $day;
    }

    public function openingHourIsLessThanOrEqual(int $hourInMinutes): bool
    {
        return $this->bookableCalender->opening_hour_in_minutes <= $hourInMinutes;
    }

    public function closingHourIsGreaterThanOrEqual(int $hourInMinutes): bool
    {
        return $this->bookableCalender->closing_hour_in_minutes >= $hourInMinutes;
    }

    public function closingHourIsLessThan(int $hourInMinutes): bool
    {
        return $this->bookableCalender->closing_hour_in_minutes < $hourInMinutes;
    }

    public function isAvailable(): bool
    {
        return $this->bookableCalender->available;
    }

    public function generateCalenderSlotHoursInMinutes(): self
    {
        $this->configuredBreak = (new ConfiguredBreakHelper())->forService($this->service);

        $this->slot = (new SlotHelper())->forService($this->service);

        $this->bookableSlotsHoursInMinutes = [];
        
        $this->generateSlotHoursInMinutes($this->bookableCalender->opening_hour_in_minutes);

        return $this;
    }

    private function generateSlotHoursInMinutes(int $slotStartHourInMinutes): void
    {
        $slotEndHourInMinutes = $this->slot->getEndHourInMinutes($slotStartHourInMinutes);

        $break = $this->configuredBreak
                    ->whereBetweenHours($slotStartHourInMinutes, $slotEndHourInMinutes)
                    ->first();
        
        if ($break) {
            $this->generateSlotHoursInMinutes($break->end_hour_in_minutes);
            return;
        }

        if ($this->closingHourIsLessThan($slotEndHourInMinutes)) {
            return;
        }

        $this->bookableSlotsHoursInMinutes[] = [$slotStartHourInMinutes, $slotEndHourInMinutes];

        $this->generateSlotHoursInMinutes($this->slot->addBreaksHoursInMinutes($slotEndHourInMinutes));
    }

    public function getBookableSlotsHoursInMinutes(): array
    {
        return $this->bookableSlotsHoursInMinutes;
    }
}