<?php

namespace App\Nova;

class GeologicalSite extends AbstractValidationResource
{
    public static function getFormId(): string
    {
        return 'geological_site';
    }

    public static function getLabel(): string
    {
        return 'Siti Geologici';
    }

    public static function getAuthorizationMethod(): string
    {
        return 'is_geological_site_validator';
    }
}
