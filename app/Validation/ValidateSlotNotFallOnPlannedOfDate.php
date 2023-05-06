<?php

namespace App\Validation;

use App\Helpers\Models\SlotHelper;
use Illuminate\Validation\Validator;

class ValidateSlotNotFallOnPlannedOfDate extends ValidatorAbstract
{
    public function __construct(
        private SlotHelper $slot
    ){}

    public function __invoke(Validator $validator): void
    {
        $this->slot->fallOnPlannedOffDate() ? $this->addErrorMessage($validator) : null;
    }

    protected function addErrorMessage(Validator $validator): void
    {
        $validator->errors()->add(
            'slot',
            __('validation.custom.slot.not-fall-on-planned-off-date')
        );
    }
}