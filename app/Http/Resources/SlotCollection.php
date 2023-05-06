<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SlotCollection extends ResourceCollection
{
    public bool $preserveKeys = true;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'available_dates' => $this->collection->pluck('start_date_without_time_in_unix_timestamp')->unique()->values(),
            'available_slots' => $this->collection,
            'bookable_duration_in_minutes' => $request->service->bookable_duration_in_minutes
        ];
    }
}
