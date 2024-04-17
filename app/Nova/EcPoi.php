<?php

namespace App\Nova;

use App\Enums\EcPoiTypes;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Wm\MapPointNova3\MapPointNova3;
use App\Nova\Filters\EcPoiTypeFilter;
use App\Nova\Filters\EcPoiRegionFilter;
use App\Nova\Filters\EcPoisScoreFilter;
use App\Nova\Filters\EcPoiUtenteFilter;
use App\Nova\Filters\EcPoiOsmTypeFilter;
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
            Textarea::make('Descrizione', 'description')->hideFromIndex(),
            Text::make('Score', 'score')->displayUsing(function ($value) {
                $stars = '';
                for ($i = 0; $i < $value; $i++) {
                    $stars .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="gold" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 12.794l-5.225 3.388 1.26-6.978-4.465-4.35 6.21-.906L10 1.106l2.22 4.844 6.21.906-4.465 4.35 1.26 6.978z" clip-rule="evenodd" />
                    </svg>';
                }
                return $stars;
            })->asHtml()->sortable()->onlyOnDetail(),
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
            Text::make('OSM URL', function () {
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
            })->asHtml(),
            MapPointNova3::make('geometry')->withMeta([
                'center' => [42, 10],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'minZoom' => 8,
                'maxZoom' => 17,
                'defaultZoom' => 13
            ])->hideFromIndex(),
            BelongsTo::make('Region', 'region', Region::class)->sortable()->searchable()->nullable(),
            Text::make('Score', 'score')->displayUsing(function ($value) {
                $stars = '';
                for ($i = 0; $i < $value; $i++) {
                    $stars .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="gold" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 12.794l-5.225 3.388 1.26-6.978-4.465-4.35 6.21-.906L10 1.106l2.22 4.844 6.21.906-4.465 4.35 1.26 6.978z" clip-rule="evenodd" />
                    </svg>';
                }
                return $stars;
            })->asHtml()->sortable()->onlyOnIndex(),
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
            (new EcPoiOsmTypeFilter)->canSee(function ($request) {
                return true;
            }),
            (new EcPoiRegionFilter)->canSee(function ($request) {
                return true;
            }),
            (new EcPoiTypeFilter)->canSee(function ($request) {
                return true;
            }),
            (new EcPoiUtenteFilter)->canSee(function ($request) {
                return $request->user()->is_administrator || $request->user()->getPermissionString() === 'Referente nazionale';
            }),
            (new EcPoisScoreFilter)->canSee(function ($request) {
                return true;
            }),
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
            (new Actions\EcPoisDownloadXlsx),
            (new \App\Nova\Actions\CalculateIntersectionsAction)->canRun(
                function ($request) {
                    return true;
                }
            ),
        ];
    }
}
