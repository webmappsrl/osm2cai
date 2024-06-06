<?php

namespace App\Enums;

use App\Traits\EnumsTrait;

class IssueStatus
{
    use EnumsTrait;


    const Unknown = 'sconosciuto';
    const Open = 'percorribile';
    const Closed = 'non percorribile';
    const PartiallyClosed = 'percorribile parzialmente';
}
