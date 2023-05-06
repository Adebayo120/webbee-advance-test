<?php

namespace App\Validation;

use Illuminate\Validation\Validator;

abstract class ValidatorAbstract
{
    protected abstract function addErrorMessage(Validator $validator): void;
}