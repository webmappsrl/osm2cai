<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class GeometrySyncFilter extends BooleanFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public $component = 'select-filter';

    public function apply(Request $request, $query, $value)
    {
        return $query->where('geometry_sync', $value);
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
            'Si'=>1,
            'No'=>0
        ];
    }

    /**
     * The default value of the filter.
     *
     * @var string
     */
    public function default()
    {
        return "";
    }

}
