<?php

namespace App\Services;

use App\Models\Service;
use App\Processors\SlotProcessor;
use App\Http\Resources\SlotCollection;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Actions\CreateMultipleAppointmentsAction;

class BookingService
{
    public function getAvailableSlots(Service $service, array $data): JsonResource
    {
        $availableSlots = (new SlotProcessor($data))
                                ->withFilter()
                                ->forService($service)
                                ->isAvailable()
                                ->belongsToAvailableBookableCalender()
                                ->notFallOnPlannedOffDate()
                                ->getBookableSlots();

        return new SlotCollection($availableSlots->keyBy->start_date_in_unix_timestamp);
    }

    public function bookAppointment(array $data): JsonResource
    {
        $data = (new CreateMultipleAppointmentsAction())->handle($data);

        return AppointmentResource::collection($data);
    }
}