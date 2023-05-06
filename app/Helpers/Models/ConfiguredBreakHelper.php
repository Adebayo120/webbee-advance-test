<?php

namespace App\Helpers\Models;

use App\Models\ConfiguredBreak;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ConfiguredBreakHelper
{
    private ConfiguredBreak $break;

    private Collection $breaks;

    private Collection $breaksBetweenHours;

    public function forBreak(ConfiguredBreak $break): self
    {
        $this->break = $break;

        return $this;
    }

    public function forService(Service $service): self
    {
        $this->breaks = $service->configuredBreaks;

        return $this;
    }

    public function whereBetweenHours(int $startHourInMinutes, int $endHourInMinutes): self
    {
        $this->breaksBetweenHours = $this->breaks->filter(function ($break) use($startHourInMinutes, $endHourInMinutes){
            $break = $this->forBreak($break);

            return $break->startHourInMinutesIsEqual($startHourInMinutes) ||
                    $break->endHourInMinutesIsEqual($endHourInMinutes) ||
                    ($break->startHourInMinutesIsLessThan($startHourInMinutes) && $break->endHourInMinutesIsGreaterThan($startHourInMinutes)) ||
                    ($break->startHourInMinutesIsLessThan($endHourInMinutes) && $break->endHourInMinutesIsGreaterThan($endHourInMinutes)) ||
                    ($break->startHourInMinutesIsGreaterThan($startHourInMinutes) && $break->endHourInMinutesIsLessThan($endHourInMinutes));
        });

        return $this;
    }

    public function first(): ?ConfiguredBreak
    {
        return $this->breaksBetweenHours->first();
    }

    public function count(): int
    {
        return $this->breaksBetweenHours->count();
    }

    public function exists(): bool
    {
        return (bool) $this->breaksBetweenHours->count();
    }

    public function sumOfHoursInMinutes(): int
    {
        return $this->breaksBetweenHours->sum(function ($break) {
            return $break->end_hour_in_minutes - $break->start_hour_in_minutes;
        });
    }

    public function startHourInMinutesIsEqual(int $hourInMinutes): bool
    {
        return $this->break->start_hour_in_minutes == $hourInMinutes;
    }

    public function startHourInMinutesIsLessThan(int $hourInMinutes): bool
    {
        return $this->break->start_hour_in_minutes < $hourInMinutes;
    }

    public function startHourInMinutesIsGreaterThan(int $hourInMinutes): bool
    {
        return $this->break->start_hour_in_minutes > $hourInMinutes;
    }

    public function endHourInMinutesIsEqual(int $hourInMinutes): bool
    {
        return $this->break->end_hour_in_minutes == $hourInMinutes;
    }

    public function endHourInMinutesIsLessThan(int $hourInMinutes): bool
    {
        return $this->break->end_hour_in_minutes < $hourInMinutes;
    }

    public function endHourInMinutesIsGreaterThan(int $hourInMinutes): bool
    {
        return $this->break->end_hour_in_minutes > $hourInMinutes;
    }
}