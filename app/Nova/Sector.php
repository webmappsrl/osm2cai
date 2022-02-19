<?php

namespace App\Nova;

use App\Nova\Actions\DownloadGeojson;
use App\Nova\Actions\DownloadKml;
use App\Nova\Actions\DownloadShape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Sector extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Sector::class;
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
        'human_name',
        'code',
        'full_code'
    ];
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static string $group = 'Territorio';
    public static int $priority = 4;

    public static function label()
    {
        return __('Settori');
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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make(__('Codice'), 'name')->sortable()->hideWhenUpdating(),
            Text::make(__('Name'), 'human_name')
               ->sortable()
               ->help('Modifica il nome del settore'),
            Text::make(__('Code'), 'code')->sortable()->hideWhenUpdating(),
            Text::make(__('Full code'), 'full_code')->sortable()->hideWhenUpdating(),
            Text::make(__('Region'), 'area_id', function () {
                return $this->area->province->region->name;
            })->hideWhenUpdating(),
            Text::make(__('Province'), 'area_id', function () {
                return $this->area->province->name;
            })->hideWhenUpdating(),
            Text::make(__('Area'), 'area_id', function () {
                return $this->area->name;
            })->hideWhenUpdating(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
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
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new DownloadGeojson())->canRun(function ($request, $zone) {
                return $request->user()->can('downloadGeojson', $zone);
            }),
            (new DownloadShape())->canRun(function ($request, $zone) {
                return $request->user()->can('downloadShape', $zone);
            }),
            (new DownloadKml())->canRun(function ($request, $zone) {
                return $request->user()->can('downloadKml', $zone);
            }),
        ];
    }
}
