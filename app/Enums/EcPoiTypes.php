<?php

namespace App\Enums;

use App\Traits\EnumsTrait;


class EcPoiTypes

{
    use EnumsTrait;

    const Signed_Post = 'signed_post';
    const Huts = 'huts';
    const Water_Spring = 'water_spring';
    const Generic = 'generic';
}
