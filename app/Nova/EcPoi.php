<?php

namespace App\Nova;

use App\Enums\EcPoiTypes;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\BelongsTo;
use Wm\MapPointNova3\MapPointNova3;
use Laravel\Nova\Http\Requests\NovaRequest;

class EcPoi extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\EcPoi::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public function title()
    {
        return $this->name;
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
    public static $priority = 8;

    public static function label()
    {
        $label = 'Punti di Interesse';

        return __($label);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('OSM ID', 'osm_id')->sortable(),
            Text::make('Nome', 'name')->sortable(),
            Text::make('Descrizione', 'description')->sortable()->displayUsing(function ($value) {
                return strlen($value) > 10 ? substr($value, 0, 10) . '...' : $value;
            })->onlyOnIndex(),
            Text::make('Descrizione', 'description')->hideFromIndex(),
            BelongsTo::make('Utente', 'user', User::class)->sortable()->searchable(),
            Select::make('Type', 'type')
                ->options(EcPoiTypes::cases()),
            //osm_tags is a jsonb type data
            Code::make('OSM Tags', 'tags')
                ->hideFromIndex(),
            Text::make('OSM Type', 'osm_type')->displayUsing(function ($value) {
                //return a different color for each type
                switch ($value) {
                    case 'N':
                        return '<span class="bg-green-200 text-green-800 font-bold py-1 px-3 rounded-full text-xs">' . $value . '</span>';
                    case 'W':
                        return '<span class="bg-blue-200 text-blue-800 font-bold py-1 px-3 rounded-full text-xs">' . $value . '</span>';
                    case 'R':
                        return '<span class="bg-yellow-200 text-yellow-800 font-bold py-1 px-3 rounded-full text-xs">' . $value . '</span>';
                    default:
                        return '<span class="bg-gray-200 text-gray-800 font-bold py-1 px-3 rounded-full text-xs">' . $value . '</span>';
                }
            })->asHtml(),
            Text::make('OSM URL')->sortable()->displayUsing(
                function ($value) {
                    $type = $this->osm_type;
                    $osmId = $this->osm_id;
                    $urlType = '';
                    switch ($type) {
                        case 'N':
                            $urlType = 'node';
                            break;
                        case 'W':
                            $urlType = 'way';
                            break;
                        case 'R':
                            $urlType = 'relation';
                            break;
                    }
                    return "<a style='color:green;' href='https://www.openstreetmap.org/$urlType/$osmId' target='_blank'>$urlType/$osmId</a>";
                }
            )->asHtml(),
            MapPointNova3::make('geometry')->withMeta([
                'center' => [42, 10],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'minZoom' => 8,
                'maxZoom' => 17,
                'defaultZoom' => 13
            ])->hideFromIndex(),
            BelongsTo::make('Region', 'region', Region::class)->sortable()->searchable(),
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
        return [];
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
        return [];
    }
}
