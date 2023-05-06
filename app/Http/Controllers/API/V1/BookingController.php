<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\BookAppointmentRequest;
use App\Http\Requests\Booking\GetAvailableSlotsRequest;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ){}

    public function getAvailableSlots(GetAvailableSlotsRequest $request): JsonResponse
    {
        $data = $this->bookingService->getAvailableSlots($request->service, $request->validated());

        return (new ResponseHelper(
            data: $data,
            message: __('booking.available_slot_successful')
        ))->asSuccessful();
    }

    public function bookAppointment(BookAppointmentRequest $request): JsonResponse
    {
        $data = $this->bookingService->bookAppointment($request->validated());

        return (new ResponseHelper(
            data: $data,
            message: __('booking.book_appointment_successful')
        ))->asSuccessful();
    }
}
