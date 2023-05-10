<?php

namespace App\Validation;

use App\Helpers\Models\SlotHelper;
use Illuminate\Validation\Validator;

class ValidateSlotIsGreaterThanNow extends ValidatorAbstract
{
    public function __construct(private SlotHelper $slot){}

    public function __invoke(Validator $validator): void
    {
        $this->slot->startDateIsGreaterThanNow() ?: $this->addErrorMessage($validator);
    }

    protected function addErrorMessage(Validator $validator): void
    {
        $validator->errors()->add(
            'slot',
            __('validation.custom.slot.greater-than-now')
        );
    }
}