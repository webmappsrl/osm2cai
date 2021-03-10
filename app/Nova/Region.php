<?php

namespace App\Nova;

use App\Nova\Actions\DownloadGeojson;
use App\Nova\Actions\DownloadShape;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

class Region extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Region::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
        'code'
    ];

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Territorio';

    public static $priority = 1;

    public static function label()
    {
        return 'Regioni';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
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
        return [
//            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('Name'), 'name')->sortable(),
            Text::make(__('Code'), 'code')->sortable(),
            Number::make(__('Provinces'), 'provinces', function () use ($provincesCount) {
                return $provincesCount;
            }),
            Number::make(__('Areas'), 'provinces', function () use ($areasCount) {
                return $areasCount;
            }),
            Number::make(__('Sectors'), 'provinces', function () use ($sectorsCount) {
                return $sectorsCount;
            }),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
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
            })
        ];
    }
}
