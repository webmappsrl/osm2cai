<?php

namespace App\Enums;

use App\Traits\EnumsTrait;

class UgcValidatedStatus
{
    use EnumsTrait;

    const Valid = 'valid';
    const Invalid = 'invalid';
    const NotValidated = 'not_validated';
}
