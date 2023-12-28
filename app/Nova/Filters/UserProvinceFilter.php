<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Filters\Filter;

class UserProvinceFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Province';

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
        return $query->whereHas('provinces', function ($q) use ($value) {
            return $q->where('provinces.id', $value);
        });
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $values = \App\Models\Province::pluck('id', 'name')->toArray();
        asort($values);

        return $values;
    }
}