<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Filters\Filter;

class SectorsAreaFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Area';

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
        return $query->where('area_id', $value);

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
            $provinces = \App\Models\Area::whereIn('province_id', Auth::user()->region->provinces->pluck('id')->toArray())->orderBy('name')->get();
            foreach ($provinces as $item) {
                $options[$item->name] = $item->id;
            }

        } else {
            foreach (\App\Models\Area::orderBy('name')->get() as $item) {
                $options[$item->name] = $item->id;
            }
        }
        return $options;
    }
}
