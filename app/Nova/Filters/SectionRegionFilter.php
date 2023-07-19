<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Filters\Filter;

class SectionRegionFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Regione';

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
        return $query->where('region_id', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {

        $options = [];
        foreach (\App\Models\Region::all() as $region) {
            $options[$region->name] = $region->id;
        }
        return $options;
    }
}
