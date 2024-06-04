<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Wm\MapPointNova3\MapPointNova3;
use Laravel\Nova\Http\Requests\NovaRequest;

class NaturalSpring extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\NaturalSpring::class;

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
        return 'Database';
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static array $search = [
        'id', 'name'
    ];

    public static string $group = 'Acqua Sorgente';
    public static $priority = 2;

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
            Text::make('Codice', 'code')->hideFromIndex(),
            Text::make('Nome', 'name')->sortable(),
            Text::make('Regione', 'region')->sortable(),
            Text::make('Provincia', 'province')->sortable(),
            Text::make('Comune', 'municipality')->sortable(),
            Text::make('Fonte', 'source')->hideFromIndex(),
            Text::make('Riferimento Fonte', 'source_ref')->hideFromIndex(),
            Text::make('Codice Fonte', 'source_code')->hideFromIndex(),
            Text::make('Riferimento', 'loc_ref')->hideFromIndex(),
            Text::make('Operatore', 'operator')->hideFromIndex(),
            Text::make('Tipo', 'type')->hideFromIndex(),
            Text::make('Volume', 'volume')->hideFromIndex(),
            Text::make('Portata', 'mass_flow_rate')->hideFromIndex(),
            Text::make('Temperatura', 'temperature')->hideFromIndex(),
            Text::make('Conducibilità', 'conductivity')->hideFromIndex(),
            Text::make('Data Rilievo', 'survey_date')->hideFromIndex(),
            Text::make('Latitudine', 'lat')->hideFromIndex(),
            Text::make('Longitudine', 'lon')->hideFromIndex(),
            Text::make('Elevazione', 'elevation')->hideFromIndex(),
            Text::make('Note', 'note')->hideFromIndex(),
            MapPointNova3::make('geometry')->withMeta([
                'center' => [42, 10],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'minZoom' => 8,
                'maxZoom' => 17,
                'defaultZoom' => 13
            ])->hideFromIndex(),



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
