<?php

namespace App\Enums;

class IssueStatus
{
    const Unknown = 'sconosciuto';
    const Open = 'percorribile';
    const Closed = 'non percorribile';
    const PartiallyClosed = 'percorribile parzialmente';
}
