<?php

namespace App\Http\Requests\Booking;

use App\Models\Slot;
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
            'slot_id' => ['required', 'exists:slots,id'],
            'profiles' => ['required', 'array'],
            'profiles.*.first_name' => ['required', 'string'],
            'profiles.*.last_name' => ['required', 'string'],
            'profiles.*.email' => ['required', 'email']
        ];
    }

    public function after(Validator $validator): array
    {
        if ($validator->errors()->count()) {
            return [];
        }

        $slot = (new SlotHelper())->forSlot(
            Slot::withCount('appointments')->find($this->slot_id)
        );
    
        return [
            new ValidateSlotIsAvailable($slot, count($this->profiles)),
            new ValidateSlotExistsInBookableCalender($slot),
            new ValidateSlotNotFallOnPlannedOfDate($slot),
            new ValidateSlotFallBetweenFutureBookableDate($slot),
            new ValidateSlotNotFallBetweenConfiguredBreaks($slot),
            new ValidateSlotExistsInBookableSlotsInCalender($slot)
        ];
    }
}
