<?php

namespace App\Processors;

use App\Helpers\Models\SlotHelper;
use App\Models\Slot;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SlotProcessor
{
    private Builder $slotQuery;

    private Service $service;

    public function __construct(private array $data = [])
    {
        $this->slotQuery =  Slot::query();
    }

    public function forService(Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function withFilter(): self
    {
        $this->slotQuery->filter($this->data);
        
        return $this;
    }

    public function isAvailable(): self
    {
        $this->slotQuery->isAvailable(
            $this->service->bookable_appointments_per_slot_count
        );

        return $this;
    }

    public function belongsToAvailableBookableCalender(): self
    {
        $this->slotQuery->belongsToAvailableBookableCalenderForService(
            $this->service
        );

        return $this;
    }

    public function notFallOnPlannedOffDate(): self
    {
        $this->service->plannedOffs->each(function ($plannedOff) {
            $this->slotQuery->whereNotBetween(
                'start_date', 
                [$plannedOff->start_date, $plannedOff->end_date]
            );
        });

        return $this;
    }

    public function getBookableSlots(): Collection
    {
        return $this->get()->filter(function ($slot) {
            return (new SlotHelper)
                        ->forSlot($slot)
                        ->existsInBookableSlots();
        });
    }

    public function get(): Collection
    {
        return $this->slotQuery->get();
    }
}