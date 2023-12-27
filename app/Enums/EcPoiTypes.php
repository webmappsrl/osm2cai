<?php

namespace App\Enums;

use ReflectionClass;


class EcPoiTypes
{

    const Signed_Post = 'signed_post';
    const Huts = 'huts';
    const Water_Spring = 'water_spring';
    const Generic = 'generic';


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
