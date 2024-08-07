<?php

namespace App\Traits;

use ReflectionClass;

trait EnumsTrait
{
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
