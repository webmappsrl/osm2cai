<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\BooleanFilter;

class UserTypeFilter extends BooleanFilter
{
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

        if (!in_array(true, $value)) {
            return $query;
        }

        if ($value['admin']) {
            if ($value['referente nazionale']) {
                return $query->where('is_national_referent', $value['referente nazionale'])
                    ->where('is_administrator', $value['admin']);
            }
            return $query->where('is_administrator', $value['admin']);
        }

        if ($value['referente nazionale']) {
            return $query->where('is_national_referent', $value['referente nazionale']);
        }

        if ($value['referente regionale']) {
            return $query->where('region_id', '!=', null);
        }

        if ($value['associazione provinciale'] || $value['associazione area'] || $value['associazione settore']) {
            if ($value['associazione provinciale']) {
                $query->whereHas('provinces', function ($query) {
                    $query->where('provinces.id', '>', 0);
                });
            }
            if ($value['associazione area']) {
                $query->whereHas('areas', function ($query) {
                    $query->where('areas.id', '>', 0);
                });
            }
            if ($value['associazione settore']) {
                $query->whereHas('sectors', function ($query) {
                    $query->where('sectors.id', '>', 0);
                });
            }
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
            'Admin' => 'admin',
            'Referente Nazionale' => 'referente nazionale',
            'Referente Regionale' => 'referente regionale',
            'Associazione Provinciale' => 'associazione provinciale',
            'Associazione Area' => 'associazione area',
            'Associazione Settore' => 'associazione settore',
        ];
    }
}