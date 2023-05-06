<?php

namespace App\Helpers\Models;

use App\Models\PlannedOff;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PlannedOffHelper
{
    private Service $service;

    private PlannedOff $plannedOff;

    private Collection $plannedOffs;

    private Collection $plannedOffsBetweenDates;

    public function forPlannedOff(PlannedOff $plannedOff): self
    {
        $this->plannedOff = $plannedOff;

        return $this;
    }

    public function forService(Service $service): self
    {
        $this->service = $service;

        $this->plannedOffs = $service->plannedOffs;

        return $this;
    }

    public function whereBetween(Carbon $startDate, Carbon $endDate): self
    {
        $this->plannedOffsBetweenDates = $this->plannedOffs->filter(function ($plannedOff) use($startDate, $endDate){
            $plannedOff = $this->forPlannedOff($plannedOff);

            return $plannedOff->startDateIsEqual($startDate) ||
                    $plannedOff->endDateIsEqual($endDate) ||
                    ($plannedOff->startDateIsLessThan($startDate) && $plannedOff->endDateIsGreaterThan($startDate)) ||
                    ($plannedOff->startDateIsLessThan($endDate) && $plannedOff->endDateIsGreaterThan($endDate)) ||
                    ($plannedOff->startDateIsGreaterThan($startDate) && $plannedOff->endDateIsLessThan($endDate));
        });

        return $this;
    }

    public function exists(): bool
    {
        return (bool) $this->plannedOffsBetweenDates->first();
    }

    public function startDateIsEqual(Carbon $date): bool
    {
        return $this->plannedOff->start_date->equalTo($date);
    }

    public function startDateIsLessThan(Carbon $date): bool
    {
        return $this->plannedOff->start_date->lessThan($date);
    }

    public function startDateIsGreaterThan(Carbon $date): bool
    {
        return $this->plannedOff->start_date->greaterThan($date);
    }

    public function endDateIsEqual(Carbon $date): bool
    {
        return $this->plannedOff->end_date->equalTo($date);
    }

    public function endDateIsLessThan(Carbon $date): bool
    {
        return $this->plannedOff->end_date->lessThan($date);
    }

    public function endDateIsGreaterThan(Carbon $date): bool
    {
        return $this->plannedOff->end_date->greaterThan($date);
    }
}