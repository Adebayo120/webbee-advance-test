<?php

namespace App\Http\Resources;

use App\Helpers\Models\SlotHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_date' => $this->start_date_in_unix_timestamp,
            'bookable_appointments_count' => (new SlotHelper())->forSlot($this->resource)->bookableAppointmentCount()
        ];
    }
}
