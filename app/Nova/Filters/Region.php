<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class Region extends BooleanFilter
{
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
        $ids = [];
        foreach ($value as $id => $val) {
            if ($val) $ids[] = $id;
        }
        if (count($ids) > 0)
            return $query->whereIn('region_id', $ids);
        else
            return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function options(Request $request)
    {
        $regions = \App\Models\Region::select('id', 'name')->get()->toArray();
        $result = [];
        foreach ($regions as $region) {
            $result[$region['name']] = $region['id'];
        }
        return $result;
    }
}
