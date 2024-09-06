<?php

namespace App\Nova;

class ArchaeologicalArea extends AbstractValidationResource
{
    public static function getFormId(): string
    {
        return 'archaeological_area';
    }

    public static function getLabel(): string
    {
        return 'Aree Archeologiche';
    }

    public static function getAuthorizationMethod(): string
    {
        return 'is_archaeological_area_validator';
    }
}
