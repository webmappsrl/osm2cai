<?php

namespace App\Nova;

use App\Nova\HikingRoute;
use App\Enums\IssueStatus;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Helpers\Osm2CaiHelper;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\HasMany;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Actions\CacheMiturApi;
use Illuminate\Support\Facades\Auth;
use App\Nova\Actions\DownloadGeojson;
use Ericlagarda\NovaTextCard\TextCard;
use Laravel\Nova\Fields\BelongsToMany;
use App\Nova\Actions\DownloadRoutesCsv;
use App\Models\Section as ModelsSection;
use App\Nova\Actions\AddMembersToSection;
use App\Nova\Filters\SectionRegionFilter;
use App\Nova\Actions\AssignSectionManager;
use Laravel\Nova\Http\Requests\NovaRequest;

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
        $hikingRoutesSDA1 = $hikingRoutes->filter(fn($hikingRoute) => $hikingRoute->osm2cai_status == 1);
        $hikingRoutesSDA2 = $hikingRoutes->filter(fn($hikingRoute) => $hikingRoute->osm2cai_status == 2);
        $hikingRoutesSDA3 = $hikingRoutes->filter(fn($hikingRoute) => $hikingRoute->osm2cai_status == 3);
        $hikingRoutesSDA4 = $hikingRoutes->filter(fn($hikingRoute) => $hikingRoute->osm2cai_status == 4);

        //define the hikingroutes for each issue status
        $hikingRoutesSPS = $hikingRoutes->filter(fn($hikingRoute) => $hikingRoute->issues_status == IssueStatus::Unknown);
        $hikingRoutesSPP = $hikingRoutes->filter(fn($hikingRoute) => $hikingRoute->issues_status == IssueStatus::Open);
        $hikingRouteSPPP = $hikingRoutes->filter(fn($hikingRoute) => $hikingRoute->issues_status == IssueStatus::PartiallyClosed);
        $hikingRoutesSPNP = $hikingRoutes->filter(fn($hikingRoute) => $hikingRoute->issues_status == IssueStatus::Closed);



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
            HasMany::make('Utenti', 'users', User::class),
            Text::make('Responsabile sezione', function () {
                $sectionManager = $this->sectionManager;
                return $sectionManager ? "<a href='/resources/users/{$sectionManager->id}'>{$sectionManager->name}</a>" : '/';
            })->asHtml(),
            BelongsToMany::make('Sentieri della sezione', 'hikingRoutes', HikingRoute::class)
                ->help('Solo i referenti nazionali possono aggiungere percorsi alla sezione'),
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
        $exploreBaseUrl = '/resources/hiking-routes/lens/hiking-routes-status-';
        $sectionId = $request->resourceId;
        $filters = json_encode([
            ["class" => "App\\Nova\\Filters\\HikingRoutesRegionFilter", "value" => ""],
            ["class" => "App\\Nova\\Filters\\HikingRoutesProvinceFilter", "value" => ""],
            ["class" => "App\\Nova\\Filters\\HikingRoutesAreaFilter", "value" => ""],
            ["class" => "App\\Nova\\Filters\\HikingRoutesSectorFilter", "value" => ""],
            ["class" => "App\\Nova\\Filters\\HikingRoutesSectionFilter", "value" => strval($sectionId)],
        ]);
        $base64Filters = base64_encode($filters);

        $section = ModelsSection::where('id', $sectionId)->first();
        $hr = $section ?  $section->hikingRoutes()->get() : [];
        if (!Auth::user()->is_administrator && Auth::user()->section_id != null) {
            $data = DB::table('sections_view')
                ->select(['tot', 'tot1', 'tot2', 'tot3', 'tot4'])
                ->where('id', Auth::user()->section->id)
                ->get();
            $numbers[1] = $data[0]->tot1;
            $numbers[2] = $data[0]->tot2;
            $numbers[3] = $data[0]->tot3;
            $numbers[4] = $data[0]->tot4;
            $cards[] = [
                (new TextCard())
                    ->onlyOnDetail()
                    ->forceFullWidth()
                    ->heading(\auth()->user()->region->name),
                $this->_getSdaCard(1, $numbers[1], $exploreBaseUrl, $base64Filters),
                $this->_getSdaCard(2, $numbers[2], $exploreBaseUrl, $base64Filters),
                $this->_getSdaCard(3, $numbers[3], $exploreBaseUrl, $base64Filters),
                $this->_getSdaCard(4, $numbers[4], $exploreBaseUrl, $base64Filters),
                (new \App\Nova\Metrics\SectionSALPercorribilitá($hr))->onlyOnDetail(),
                (new \App\Nova\Metrics\SectionSALPercorsi($hr))->onlyOnDetail(),
            ];
            return $cards;
        } else {
            $values = DB::table('hiking_routes')
                ->join('hiking_route_section', 'hiking_routes.id', '=', 'hiking_route_section.hiking_route_id')
                ->where('hiking_route_section.section_id', $sectionId)
                ->select('hiking_routes.osm2cai_status', DB::raw('count(*) as num'))
                ->groupBy('hiking_routes.osm2cai_status')
                ->get();


            $numbers = [];
            $numbers[1] = 0;
            $numbers[2] = 0;
            $numbers[3] = 0;
            $numbers[4] = 0;

            $exploreUrlSDA1 = url($exploreBaseUrl . '1-lens') . '?hiking-routes_page=1&hiking-routes_filter=' . $base64Filters;
            $exploreUrlSDA2 = url($exploreBaseUrl . '2-lens') . '?hiking-routes_page=1&hiking-routes_filter=' . $base64Filters;
            $exploreUrlSDA3 = url($exploreBaseUrl . '3-lens') . '?hiking-routes_page=1&hiking-routes_filter=' . $base64Filters;
            $exploreUrlSDA4 = url($exploreBaseUrl . '4-lens') . '?hiking-routes_page=1&hiking-routes_filter=' . $base64Filters;

            if (count($values) > 0) {
                foreach ($values as $value) {
                    $numbers[$value->osm2cai_status] = $value->num;
                }
            }

            $tot = array_sum($numbers);

            $cards = [
                (new TextCard())->width('1/4')
                    ->text('<div>#sda 1 <a href="' . $exploreUrlSDA1 . '">[Esplora]</a></div>')
                    ->textAsHtml()
                    ->onlyOnDetail()
                    ->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor(1) . '; color: white; font-size: xx-large">' . $numbers[1] . '</div>')
                    ->headingAsHtml(),
                (new TextCard())->width('1/4')
                    ->text('<div>#sda 2 <a href="' . $exploreUrlSDA2 . '">[Esplora]</a></div>')
                    ->textAsHtml()
                    ->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor(2) . '; color: white; font-size: xx-large">' . $numbers[2] . '</div>')
                    ->headingAsHtml()
                    ->onlyOnDetail(),
                (new TextCard())->width('1/4')
                    ->text('<div>#sda 3 <a href="' . $exploreUrlSDA3 . '">[Esplora]</a></div>')
                    ->textAsHtml()
                    ->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor(3) . '; color: white; font-size: xx-large">' . $numbers[3] . '</div>')
                    ->headingAsHtml()
                    ->onlyOnDetail(),
                (new TextCard())->width('1/4')
                    ->text('<div>#sda 4 <a href="' . $exploreUrlSDA4 . '">[Esplora]</a></div>')
                    ->textAsHtml()
                    ->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor(4) . '; color: white; font-size: xx-large">' . $numbers[4] . '</div>')
                    ->headingAsHtml()
                    ->onlyOnDetail(),
                (new \App\Nova\Metrics\SectionSALPercorribilitá($hr))->onlyOnDetail()->width('1/3'),
                (new \App\Nova\Metrics\SectionSALPercorsi($hr))->onlyOnDetail()->width('1/3'),
                (new TextCard())->width('1/6')
                    ->heading($tot)
                    ->text('Totale Percorsi')
                    ->onlyOnDetail(),
            ];

            //$hr is filled only when in detail view
            if (count($hr) > 0) {
                $cards[] = (new TextCard())->width('1/6')
                    ->heading(number_format($hr->sum('distance'), 2, ',', '.'))
                    ->text('Totale km')
                    ->onlyOnDetail();
            }
            return $cards;
        }
    }

    private function _getSdaCard(int $sda, int $num, string $baseUrl, string $base64Filters): TextCard
    {
        $exploreUrlSDA = url($baseUrl . $sda . '-lens') . '?hiking-routes_page=1&hiking-routes_filter=' . $base64Filters;

        $path = '/resources/hiking-routes/lens/hiking-routes-status-' . $sda . '-lens';
        return (new TextCard())->width('1/4')
            ->text('<div>#sda ' . $sda . ' <a href="' . $exploreUrlSDA . '">[Esplora]</a></div>')
            ->textAsHtml()
            ->onlyOnDetail()
            ->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor($sda) . '; color: white; font-size: xx-large">' . $num . '</div>')
            ->headingAsHtml();
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
        return [
            (new AssignSectionManager())
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(function ($request) {
                    return true;
                }),
            (new AddMembersToSection())
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(function ($request) {
                    return true;
                }),
            (new DownloadGeojson)
                ->canRun(
                    function ($request, $model) {
                        return true;
                    }
                ),
            (new DownloadRoutesCsv)
                ->canRun(
                    function ($request, $model) {
                        return true;
                    }
                ),
            (new CacheMiturApi())->canSee(function ($request) {
                return $request->user()->is_administrator;
            })->canRun(function ($request) {
                $user = $request->user();
                return $user->is_administrator;
            }),
        ];
    }

    public function authorizedToDetach(NovaRequest $request, $model, $relationship)
    {
        return Auth::user()->is_administrator || Auth::user()->is_national_referent;
    }

    public function authorizedToAttach(NovaRequest $request, $model)
    {
        return Auth::user()->is_administrator || Auth::user()->is_national_referent;
    }
}
