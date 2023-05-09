<?php

namespace App\Services;

use App\Models\Service;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Actions\CreateMultipleAppointmentsAction;
use App\Helpers\Models\SlotHelper;
use Carbon\Carbon;

class BookingService
{
    public function getAvailableSlots(Service $service, Carbon $startDate, Carbon $endDate): array
    {
        $slotHelper = (new SlotHelper)
                            ->whereBetween($startDate, $endDate)
                            ->forService($service)
                            ->forAvailableBookableCalenders()
                            ->generateBookedAppointments()
                            ->generateAvailableSlots();

        return [
            'available_dates' => $slotHelper->getAvailableDates(),
            'bookable_duration_in_minutes' => $service->bookable_duration_in_minutes,
            'available_slots' => $slotHelper->getAvailableSlots(),
        ];
    }

    public function bookAppointment(array $data): JsonResource
    {
        $data = (new CreateMultipleAppointmentsAction())->handle($data);

        return AppointmentResource::collection($data);
    }
}