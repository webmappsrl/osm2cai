<?php

namespace App\Nova;

use App\Enums\IssueStatus;
use App\Models\HikingRoute as ModelsHikingRoute;
use App\Nova\HikingRoute;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use App\Nova\Filters\SectionRegionFilter;
use Laravel\Nova\Fields\HasMany;

class Section extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Section::class;
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
    ];
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static string $group = 'Territorio';


    public static $priority = 5;

    public static function label()
    {
        return 'Sezioni';
    }

    private static $indexDefaultOrder = [
        'name' => 'asc'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        $hikingRoutes = $this->hikingRoutes()->get();

        //define the hiking routes for each osm2cai status
        $hikingRoutesSDA1 = $hikingRoutes->filter(fn ($hikingRoute) => $hikingRoute->osm2cai_status == 1);
        $hikingRoutesSDA2 = $hikingRoutes->filter(fn ($hikingRoute) => $hikingRoute->osm2cai_status == 2);
        $hikingRoutesSDA3 = $hikingRoutes->filter(fn ($hikingRoute) => $hikingRoute->osm2cai_status == 3);
        $hikingRoutesSDA4 = $hikingRoutes->filter(fn ($hikingRoute) => $hikingRoute->osm2cai_status == 4);

        //define the hikingroutes for each issue status
        $hikingRoutesSPS = $hikingRoutes->filter(fn ($hikingRoute) => $hikingRoute->issues_status == IssueStatus::Unknown);
        $hikingRoutesSPP = $hikingRoutes->filter(fn ($hikingRoute) => $hikingRoute->issues_status == IssueStatus::Open);
        $hikingRouteSPPP = $hikingRoutes->filter(fn ($hikingRoute) => $hikingRoute->issues_status == IssueStatus::PartiallyClosed);
        $hikingRoutesSPNP = $hikingRoutes->filter(fn ($hikingRoute) => $hikingRoute->issues_status == IssueStatus::Closed);



        //create a string with the list of all the hiking routes and make it linkable to the hiking route resource detail
        $hikingRoutesString = '';
        foreach ($hikingRoutes as $hikingRoute) {

            $hikingRoutesString .=  "<a style='color:green; text-decoration:none;' href='/resources/hiking-routes/{$hikingRoute->id}'>{$hikingRoute->ref}</a>" . ', ';
        }
        $hikingRoutesString = rtrim($hikingRoutesString, ', ');


        return [
            ID::make()->sortable()
                ->hideFromIndex(),
            Text::make('Nome', 'name',)
                ->sortable()
                ->rules('required', 'max:255')
                ->displayUsing(function ($name, $a, $b) {
                    $wrappedName = wordwrap($name, 50, "\n", true);
                    $htmlName = str_replace("\n", '<br>', $wrappedName);
                    return $htmlName;
                })
                ->asHtml(),
            Text::make('Codice CAI', 'cai_code')
                ->sortable()
                ->rules('required', 'max:255'),
            BelongsTo::make('Regione', 'region', Region::class)
                ->searchable(),
            BelongsToMany::make('Sentieri', 'hikingRoutes', HikingRoute::class),
            HasMany::make('Utenti', 'users', User::class),
            Text::make('SDA1', function () use ($hikingRoutesSDA1) {
                return $hikingRoutesSDA1->count();
            })->onlyOnIndex()
                ->sortable(),
            Text::make('SDA2', function () use ($hikingRoutesSDA2) {
                return $hikingRoutesSDA2->count();
            })->onlyOnIndex()
                ->sortable(),
            Text::make('SDA3', function () use ($hikingRoutesSDA3) {
                return $hikingRoutesSDA3->count();
            })->onlyOnIndex()
                ->sortable(),
            Text::make('SDA4', function () use ($hikingRoutesSDA4) {
                return $hikingRoutesSDA4->count();
            })->onlyOnIndex()
                ->sortable(),
            Text::make('TOT', function () use ($hikingRoutes) {
                return $hikingRoutes->sum(function ($hikingRoute) {
                    return ($hikingRoute->osm2cai_status < 5 && $hikingRoute->osm2cai_status > 0) ? 1 : 0;
                });
            })->onlyOnIndex()
                ->sortable(),
            Text::make('SPS', function () use ($hikingRoutesSPS) {
                return $hikingRoutesSPS->count();
            })->onlyOnIndex()
                ->sortable(),
            Text::make('SPP', function () use ($hikingRoutesSPP) {
                return $hikingRoutesSPP->count();
            })->onlyOnIndex()
                ->sortable(),
            Text::make('SPPP', function () use ($hikingRouteSPPP) {
                return $hikingRouteSPPP->count();
            })->onlyOnIndex()
                ->sortable(),
            Text::make('SPNP', function () use ($hikingRoutesSPNP) {
                return $hikingRoutesSPNP->count();
            })->onlyOnIndex()
                ->sortable(),


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
        return [
            (new SectionRegionFilter)
        ];
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
        return [];
    }
}
