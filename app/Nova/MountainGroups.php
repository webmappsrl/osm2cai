<?php

namespace App\Nova;

use App\Nova\Actions\CacheMiturApi;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Imumz\LeafletMap\LeafletMap;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class MountainGroups extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\MountainGroups::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */

    public function title()
    {
        return $this->name;
    }

    public static function label()
    {
        return 'Gruppi Montuosi';
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static array $search = [
        'id', 'name'
    ];

    public static string $group = 'Territorio';
    public static $priority = 9;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $centerLat = $this->getCentroid()[1] ?? 0;
        $centerLng = $this->getCentroid()[0] ?? 0;
        $aggregatedData = json_decode($this->aggregated_data);
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make("Nome", "name")->sortable(),
            Textarea::make("Descrizione", "description")->hideFromIndex(),
            LeafletMap::make('Mappa')
                ->type('GeoJson')
                ->geoJson(json_encode($this->getEmptyGeojson()))
                ->center($centerLat, $centerLng)
                ->zoom(9)
                ->onlyOnDetail(),
            BelongsToMany::make('Regioni', 'regions', Region::class)
                ->searchable(),
            Text::make('POI Generico', function () use ($aggregatedData) {
                return $aggregatedData ? $aggregatedData->ec_pois_count : 'N/A';
            })->sortable(),
            Text::make('POI Rifugio', function () use ($aggregatedData) {
                return $aggregatedData ? $aggregatedData->cai_huts_count : 'N/A';
            })->sortable(),
            Text::make('Percorsi POI Totali', function () use ($aggregatedData) {
                return $aggregatedData ? $aggregatedData->poi_total : 'N/A';
            })->sortable(),
            Text::make('Attivitá o Esperienze', function () use ($aggregatedData) {
                return $aggregatedData ? $aggregatedData->sections_count : 'N/A';
            })->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            (new \App\Nova\Filters\MountainGroupsRegionFilter)
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new \App\Nova\Actions\DownloadMountainGroupsCsv)->canRun(
                function ($request) {
                    return true;
                }
            ),
            (new \App\Nova\Actions\CalculateIntersectionsAction)->canRun(
                function ($request) {
                    return true;
                }
            ),

            (new CacheMiturApi())->canRun(
                function ($request) {
                    return true;
                }
            )
        ];
    }
}