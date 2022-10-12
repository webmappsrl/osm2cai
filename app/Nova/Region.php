<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use App\Nova\Actions\DownloadKml;
use App\Nova\Actions\DownloadShape;
use App\Nova\Actions\DownloadGeojson;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Actions\DownloadRegionRoutesCsv;
use App\Nova\Actions\DownloadRegionRoutesKml;
use App\Nova\Actions\DownloadRegionRoutesShape;
use App\Nova\Actions\DownloadRegionRoutesGeojson;

class Region extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Region::class;
    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static string $title = 'name';
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static array $search = [
        'name',
        'code'
    ];
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static string $group = 'Territorio';
    public static $priority = 1;

    public static function label()
    {
        return 'Regioni';
    }

    private static $indexDefaultOrder = [
        'name' => 'asc'
    ];

    public static function indexQuery(NovaRequest $request, $query)
    {
        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];

            return $query->orderBy(key(static::$indexDefaultOrder), reset(static::$indexDefaultOrder));
        }

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        $provincesCount = count($this->provinces);
        $areasCount = 0;
        $sectorsCount = 0;

        foreach ($this->provinces as $province) {
            $areasCount += count($province->areas);
            foreach ($province->areas as $area) {
                $sectorsCount += count($area->sectors);
            }
        }

        $hikingRoutes4Count = $this->hikingRoutes()->where('osm2cai_status', '=', 4)->count();
        $hikingRoutes3Count = $this->hikingRoutes()->where('osm2cai_status', '=', 3)->count();
        $hikingRoutes2Count = $this->hikingRoutes()->where('osm2cai_status', '=', 2)->count();
        $hikingRoutes1Count = $this->hikingRoutes()->where('osm2cai_status', '=', 1)->count();
        $hikingRoutes0Count = $this->hikingRoutes()->where('osm2cai_status', '=', 0)->count();

        return [
            //            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('Regione'), 'name')->sortable(),
            Text::make(__('Codice CAI'), 'code')->sortable(),
            Number::make(__('# Province'), function () use ($provincesCount) {
                return $provincesCount;
            }),
            Number::make(__('# Aree'), function () use ($areasCount) {
                return $areasCount;
            }),
            Number::make(__('# Settori'), function () use ($sectorsCount) {
                return $sectorsCount;
            }),
            Number::make(__('# 4'), function () use ($hikingRoutes4Count) {
                return $hikingRoutes4Count;
            }),
            Number::make(__('# 3'), function () use ($hikingRoutes3Count) {
                return $hikingRoutes3Count;
            }),
            Number::make(__('# 2'), function () use ($hikingRoutes2Count) {
                return $hikingRoutes2Count;
            }),
            Number::make(__('# 1'), function () use ($hikingRoutes1Count) {
                return $hikingRoutes1Count;
            }),
            Number::make(__('# 0'), function () use ($hikingRoutes0Count) {
                return $hikingRoutes0Count;
            }),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function actions(Request $request): array
    {
        return [
            (new DownloadGeojson)->canRun(function ($request, $zone) {
                return $request->user()->can('downloadGeojson', $zone);
            }),
            (new DownloadShape)->canRun(function ($request, $zone) {
                return $request->user()->can('downloadShape', $zone);
            }),
            (new DownloadKml)->canRun(function ($request, $zone) {
                return $request->user()->can('downloadKml', $zone);
            }),
            (new DownloadRegionRoutesGeojson)->canRun(function ($request, $zone) {
                return $request->user()->can('downloadGeojson', $zone);
            }),
            (new DownloadRegionRoutesCsv)->canRun(function ($request, $zone) {
                return $request->user()->can('downloadKml', $zone);
            })

        ];
    }
}
