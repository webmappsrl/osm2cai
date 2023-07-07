<?php

namespace App\Nova\Filters;

use App\Enums\IssueStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class IssueStatusFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Stato di percorrenza';

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

        return $query->where('issues_status', $value);
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

        foreach (IssueStatus::cases() as $status) {
            $options[$status] = $status;
        }

        return $options;
    }
}
