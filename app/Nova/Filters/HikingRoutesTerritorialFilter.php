<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class HikingRoutesTerritorialFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Regione';
    public $type = '';
    public $relation_name = '';
    public $relation_field = '';

    public function __construct($type)
    {
        $this->type = $type;
        switch ($type) {
            case 'region' :
                $this->name = 'Regione';
                $this->relation_name = 'regions';
                $this->relation_field = 'region_id';
                break;
            case 'province' :
                $this->name = 'Provincia';
                $this->relation_name = 'provinces';
                $this->relation_field = 'province_id';
                break;
        }
    }

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
        $relation_field = $this->relation_field;
        return $query->whereHas($this->relation_name, function ($query) use ($relation_field, $value) {
            $query->where($relation_field, $value);
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
        switch ($this->type) {
            case 'region':
                foreach (\App\Models\Region::all() as $region) {
                    $options[$region->name] = $region->id;
                }
                break;
            case 'province':
                foreach (\App\Models\Province::all() as $province) {
                    $options[$province->name] = $province->id;
                }
                break;
        }
        return $options;
    }
}
