<?php

namespace App\Enums;

use ReflectionClass;

class UgcWaterFlowValidatedStatus
{
    const Valid = 'valid';
    const Invalid = 'invalid';
    const NotValidated = 'not_validated';


    public static function cases()
    {
        $self = new self();
        $constants = $self->getConstants();

        $cases = [];
        foreach ($constants as $constant) {
            $cases[$constant] = $constant;
        }

        return $cases;
    }
    public function getConstants()
    {
        $reflectionClass = new ReflectionClass($this);
        return $reflectionClass->getConstants();
    }
}
