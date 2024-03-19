<?php

namespace App\Nova\Filters;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Filters\Filter;

class HikingRoutesSectionFilter extends Filter
{
    public $name = 'Sezione';

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

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
        //pivot table hiking_route_section
        return $query->whereHas('sections', function ($query) use ($value) {
            $query->where('section_id', $value);
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
        $sections = DB::select('select id, name from sections');
        $options = [];
        foreach ($sections as $section) {
            $options[$section->name] = $section->id;
        }

        //order options by name
        ksort($options);

        return $options;
    }
}
