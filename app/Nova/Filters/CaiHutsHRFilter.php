<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class CaiHutsHRFilter extends BooleanFilter
{

    public $name = 'Rifugi';

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
        if ($value['with_cai_huts']) {
            return $query->where('has_cai_huts', '=', true);
        } elseif ($value['without_cai_huts']) {
            return $query->where('has_cai_huts', '=', false);
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
            'Con Rifugi' => 'with_cai_huts',
            'Senza Rifugi' => 'without_cai_huts',
        ];
    }
}
