<?php

namespace App\Enums;

use ReflectionClass;

class IssueStatus
{
    const Unknown = 'sconosciuto';
    const Open = 'percorribile';
    const Closed = 'non percorribile';
    const PartiallyClosed = 'percorribile parzialmente';


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
