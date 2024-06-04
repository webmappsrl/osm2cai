<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

class SourceSurvey extends UgcPoi
{
    public static string $group = 'Acqua Sorgente';
    public static $priority = 1;

    public static function label()
    {
        $label = 'Monitoraggi';

        return __($label);
    }

    public function authorizeToView(Request $request)
    {
        return $request->user()->is_source_validator;
    }

    public function authorizeToViewAny(Request $request)
    {
        return $request->user()->is_source_validator;
    }

    public function authorizeToUpdate(Request $request)
    {
        return $request->user()->is_source_validator;
    }
}
