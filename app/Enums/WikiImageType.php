<?php

namespace App\Enums;

use App\Traits\EnumsTrait;

class WikiImageType
{
    use EnumsTrait;

    const Wikipedia_images = 'wikipedia_images';
    const Wikidata_images = 'wikidata_images';
    const Wikimedia_images = 'wikimedia_images';
}