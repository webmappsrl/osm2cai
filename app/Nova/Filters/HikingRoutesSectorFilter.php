<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Filters\Filter;

class HikingRoutesSectorFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Settore';


    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->whereHas('sectors', function ($query) use ($value) {
            $query->where('sector_id', $value);
        });
    }

    /**
     * Get the filter's available options.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function options(Request $request)
    {

        $options = [];
        if (Auth::user()->getTerritorialRole() == 'regional') {
            $sectors_id = [];
            foreach (Auth::user()->region->provinces as $province) {
                foreach ($province->areas as $area) {
                    $sectors_id = array_merge($sectors_id, $area->sectors->pluck('id')->toArray());
                }
            }
            $sectors = \App\Models\Sector::whereIn('id', $sectors_id)->orderBy('full_code')->get();
            foreach ($sectors as $item) {
                $options[$item->full_code] = $item->id;
            }

        } else {
            foreach (\App\Models\Sector::orderBy('full_code')->get() as $item) {
                $options[$item->full_code] = $item->id;
            }
        }
        return $options;
    }
}
