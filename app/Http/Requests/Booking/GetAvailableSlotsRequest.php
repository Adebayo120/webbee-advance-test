<?php

namespace App\Http\Requests\Booking;

use Carbon\Carbon;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class GetAvailableSlotsRequest extends FormRequest
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
            'service_id' => ['required', 'exists:services,id'],
            'date_range' => ['required', 'array'],
            'date_range.start_date_in_unix_timestamp' => ['required', 'numeric', 'lt:date_range.end_date_in_unix_timestamp'],
            'date_range.end_date_in_unix_timestamp' => ['required', 'numeric', 'gt:date_range.start_date_in_unix_timestamp']
        ];
    }

    public function attributes(): array
    {
        return [
            'date_range.start_date_in_unix_timestamp' => 'start date',
            'date_range.end_date_in_unix_timestamp' => 'end date',
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'service' => Service::find($this->service_id),
            'start_date' => Carbon::createFromTimestamp($this->date_range['start_date_in_unix_timestamp'])->startOfDay(),
            'end_date' => Carbon::createFromTimestamp($this->date_range['end_date_in_unix_timestamp'])->endOfDay(),
        ]);
    }
}
