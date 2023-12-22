<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class HrCorrectGeometryFilter extends BooleanFilter
{

    public $name = 'Correttezza Geometria';
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        if ($value['correct']) {
            return $query->where('geometry_check', true);
        }
        if ($value['not_correct']) {
            return $query->where('geometry_check', false);
        }
        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            __('Geometria Corretta') => 'correct',
            __('Geometria Non Corretta') => 'not_correct',
        ];
    }
}
