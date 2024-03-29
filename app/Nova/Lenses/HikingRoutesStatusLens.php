<?php

namespace App\Nova\Lenses;

use Illuminate\Support\Arr;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Lenses\Lens;
use Laravel\Nova\Fields\Number;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Http\Requests\LensRequest;
use App\Nova\Filters\HikingRoutesAreaFilter;
use App\Nova\Filters\HikingRoutesRegionFilter;
use App\Nova\Filters\HikingRoutesSectorFilter;
use App\Nova\Filters\HikingRoutesSectionFilter;
use App\Nova\Filters\HikingRoutesProvinceFilter;


class HikingRoutesStatusLens extends Lens
{

    public $name = 'SDA0';
    public static $sda = 0;

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param \Laravel\Nova\Http\Requests\LensRequest $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {

        if (Auth::user()->getTerritorialRole() == 'regional') {
            $value = Auth::user()->region->id;
            return $request->withOrdering($request->withFilters(
                $query->where('osm2cai_status', static::$sda)
                    ->whereHas('regions', function ($query) use ($value) {
                        $query->where('region_id', $value);
                    })
            ));
        } else {
            return $request->withOrdering($request->withFilters(
                $query->where('osm2cai_status', static::$sda)
            ));
        }
    }


    /**
     * Get the fields available to the lens.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('Regioni', function () {
                $val = "ND";
                if (Arr::accessible($this->regions)) {
                    if (count($this->regions) > 0) {
                        $val = implode(', ', $this->regions->pluck('name')->toArray());
                    }
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('Province', function () {
                $val = "ND";
                if (Arr::accessible($this->provinces)) {
                    if (count($this->provinces) > 0) {
                        $val = implode(', ', $this->provinces->pluck('name')->toArray());
                    }
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('Aree', function () {
                $val = "ND";
                if (Arr::accessible($this->areas)) {
                    if (count($this->areas) > 0) {
                        $val = implode(', ', $this->areas->pluck('name')->toArray());
                    }
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('Settori', function () {
                $val = "ND";
                if (Arr::accessible($this->sectors)) {
                    if (count($this->sectors) > 0) {
                        $val = implode(', ', $this->sectors->pluck('name')->toArray());
                    }
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('REF', 'ref')->onlyOnIndex(),
            Text::make('Cod. REI', 'ref_REI')->onlyOnIndex(),
            Text::make('Ultima ricognizione', 'survey_date')->onlyOnIndex(),
            Number::make('STATO', 'osm2cai_status')->sortable()->onlyOnIndex(),
            Number::make('OSMID', 'relation_id')->onlyOnIndex(),

        ];
    }

    /**
     * Get the cards available on the lens.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        if (Auth::user()->getTerritorialRole() == 'regional') {
            return [
                (new HikingRoutesProvinceFilter()),
                (new HikingRoutesAreaFilter()),
                (new HikingRoutesSectorFilter()),
                (new HikingRoutesSectionFilter()),
            ];
        } else {
            return [
                (new HikingRoutesRegionFilter()),
                (new HikingRoutesProvinceFilter()),
                (new HikingRoutesAreaFilter()),
                (new HikingRoutesSectorFilter()),
                (new HikingRoutesSectionFilter()),
            ];
        }
    }

    /**
     * Get the actions available on the lens.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'hiking-routes-status-lens';
    }
}

class HikingRoutesStatus0Lens extends HikingRoutesStatusLens
{
    public static $sda = 0;

    public function __construct($resource = null)
    {
        $this->name = Auth::user()->getTerritorialRole() == 'regional' ? 'SDA0 ' . Auth::user()->region->name : 'SDA0';
        parent::__construct($resource);
    }

    public function uriKey()
    {
        return 'hiking-routes-status-0-lens';
    }
}

class HikingRoutesStatus1Lens extends HikingRoutesStatusLens
{
    public static $sda = 1;

    public function __construct($resource = null)
    {
        $this->name = Auth::user()->getTerritorialRole() == 'regional' ? 'SDA1 ' . Auth::user()->region->name : 'SDA1';
        parent::__construct($resource);
    }

    public function uriKey()
    {
        return 'hiking-routes-status-1-lens';
    }
}


class HikingRoutesStatus2Lens extends HikingRoutesStatusLens
{
    public static $sda = 2;

    public function __construct($resource = null)
    {
        $this->name = Auth::user()->getTerritorialRole() == 'regional' ? 'SDA2 ' . Auth::user()->region->name : 'SDA2';
        parent::__construct($resource);
    }

    public function uriKey()
    {
        return 'hiking-routes-status-2-lens';
    }
}

class HikingRoutesStatus3Lens extends HikingRoutesStatusLens
{
    public static $sda = 3;

    public function __construct($resource = null)
    {
        $this->name = Auth::user()->getTerritorialRole() == 'regional' ? 'SDA3 ' . Auth::user()->region->name : 'SDA3';
        parent::__construct($resource);
    }

    public function uriKey()
    {
        return 'hiking-routes-status-3-lens';
    }
}

class HikingRoutesStatus4Lens extends HikingRoutesStatusLens
{
    public static $sda = 4;

    public function __construct($resource = null)
    {
        $this->name = Auth::user()->getTerritorialRole() == 'regional' ? 'SDA4 ' . Auth::user()->region->name : 'SDA4';
        parent::__construct($resource);
    }

    public function uriKey()
    {
        return 'hiking-routes-status-4-lens';
    }
}
