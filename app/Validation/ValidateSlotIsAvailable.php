<?php

namespace App\Validation;

use App\Helpers\Models\SlotHelper;
use Illuminate\Validation\Validator;

class ValidateSlotIsAvailable extends ValidatorAbstract
{
    public function __construct(
        private SlotHelper $slot,
        private int $profilesCount
    ){}

    public function __invoke(Validator $validator): void
    {
        $this->slot->isAvailable($this->profilesCount) ?: $this->addErrorMessage($validator);
    }

    protected function addErrorMessage(Validator $validator): void
    {
        $validator->errors()->add(
            'slot',
            __('validation.custom.slot.is-available')
        );
    }
}