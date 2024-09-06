<?php

namespace App\Nova;

class ArchaeologicalSite extends AbstractValidationResource
{
    public static function getFormId(): string
    {
        return 'archaeological_site';
    }

    public static function getLabel(): string
    {
        return 'Siti Archeologici';
    }

    public static function getAuthorizationMethod(): string
    {
        return 'is_archaeological_site_validator';
    }
}
