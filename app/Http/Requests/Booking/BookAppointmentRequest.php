<?php

namespace App\Http\Requests\Booking;

use Carbon\Carbon;
use App\Models\Service;
use App\Helpers\Models\SlotHelper;
use Illuminate\Validation\Validator;
use App\Validation\ValidateSlotIsAvailable;
use Illuminate\Foundation\Http\FormRequest;
use App\Validation\ValidateSlotNotFallOnPlannedOfDate;
use App\Validation\ValidateSlotExistsInBookableCalender;
use App\Validation\ValidateSlotFallBetweenFutureBookableDate;
use App\Validation\ValidateSlotNotFallBetweenConfiguredBreaks;
use App\Validation\ValidateSlotExistsInBookableSlotsInCalender;

class BookAppointmentRequest extends FormRequest
{
    private SlotHelper $slot;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'exists:services,id'],
            'start_date_in_timestamp' => ['required', 'numeric', 'min_digits:10'],
            'profiles' => ['required', 'array'],
            'profiles.*.first_name' => ['required', 'string'],
            'profiles.*.last_name' => ['required', 'string'],
            'profiles.*.email' => ['required', 'email']
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'slot' => $this->slot,
        ]);
    }

    public function after(Validator $validator): array
    {
        if ($validator->errors()->count()) {
            return [];
        }

        $startDate = Carbon::createFromTimestamp($this->start_date_in_timestamp);

        $this->slot = (new SlotHelper)
                            ->forService(Service::find($this->service_id))
                            ->forSlot($startDate);
    
        return [
            new ValidateSlotIsAvailable($this->slot, count($this->profiles)),
            new ValidateSlotExistsInBookableCalender($this->slot),
            new ValidateSlotNotFallOnPlannedOfDate($this->slot),
            new ValidateSlotFallBetweenFutureBookableDate($this->slot),
            new ValidateSlotNotFallBetweenConfiguredBreaks($this->slot),
            new ValidateSlotExistsInBookableSlotsInCalender($this->slot)
        ];
    }
}
