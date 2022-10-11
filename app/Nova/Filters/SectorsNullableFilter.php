<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Filters\Filter;

class SectorsNullableFilter extends BooleanFilter
{


    public $name = 'Ha il campo vuoto';

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
        if ( isset( $value['manager'] ) && $value['manager'] )
        {
            $query->whereNull('manager')->orWhere('manager','=','');
        }

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

        return ['Responsabili' => 'manager'];
    }


}
