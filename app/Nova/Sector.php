<?php

namespace App\Nova;

use App\Models\User;
use Laravel\Nova\Nova;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use App\Helpers\Osm2CaiHelper;
use Laravel\Nova\Fields\Number;
use Imumz\LeafletMap\LeafletMap;
use App\Nova\Actions\DownloadKml;
use Illuminate\Support\Facades\DB;
use App\Nova\Actions\DownloadShape;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Nova\Actions\DownloadGeojson;
use Ericlagarda\NovaTextCard\TextCard;
use Laravel\Nova\Fields\BelongsToMany;
use App\Nova\Filters\SectorsAreaFilter;
use App\Nova\Lenses\SectorsColumnsLens;
use App\Nova\Filters\SectorsRegionFilter;
use App\Nova\Filters\SectorsNullableFilter;
use App\Nova\Filters\SectorsProvinceFilter;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Lenses\NoNameSectorsColumnsLens;
use App\Nova\Lenses\NoNumExpectedColumnsLens;
use App\Nova\Filters\HikingRoutesSectorFilter;
use App\Helpers\NovaCurrentResourceActionHelper;
use Wm\MapMultiPolygonNova3\MapMultiPolygonNova3;
use App\Nova\Lenses\NoResponsabileSectorsColumnsLens;
use App\Nova\Actions\BulkSectorsModeratorAssignAction;
use App\Nova\Actions\UploadSectorGeometryDataAction;

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

            $query->orderBy(key(static::$indexDefaultOrder), reset(static::$indexDefaultOrder));
        }

        /**
         * @var \App\Models\User
         */
        $user = auth()->user();

        return $query->ownedBy($user);
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
        $fields = [
            Text::make(__('Codice'), 'name')->sortable()->hideWhenUpdating(),
            Text::make(__('Name'), 'human_name')
               ->sortable()
               ->help('Modifica il nome del settore'),
            Text::make(__('Code'), 'code')->sortable()->hideWhenUpdating(),
            Text::make(__('Responsabili'),'manager'),
            Number::make(__('Numero Atteso'),'num_expected'),
            Text::make(__('Full code'), 'full_code')->sortable()->hideWhenUpdating(),
            Text::make(__('Region'), 'area_id', function () {
                return $this->area->province->region->name ?? '';
            })->hideWhenUpdating(),
            Text::make(__('Province'), 'area_id', function () {
                return $this->area->province->name;
            })->hideWhenUpdating(),
            Text::make(__('Area'), 'area_id', function () {
                return $this->area->name;
            })->hideWhenUpdating(),
            BelongsToMany::make('Moderators','users')

        ];

        if (NovaCurrentResourceActionHelper::isDetail($request)) {
            $fields[] =
                LeafletMap::make('Mappa')
                ->type('GeoJson')
                ->geoJson(json_encode($this->getEmptyGeojson()))
                ->center($this->getCentroid()[1], $this->getCentroid()[0])
                ->zoom(9)
                ->onlyOnDetail();
                // MapMultiPolygonNova3::make('geometry')->withMeta([
                //     'geojson' => json_encode($this->getEmptyGeojson()),
                //     'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                // ])->onlyOnDetail();
        }

        return $fields;
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
        if(!is_null($request['resourceId'])) {

            $sector = Sector::find($request['resourceId']);

            $data = DB::table('sectors_view')
            ->select(['tot', 'tot1', 'tot2', 'tot3', 'tot4'])
            ->where('id', $request['resourceId'])
            ->get();

            $numbers[1] = $data[0]->tot1;
            $numbers[2] = $data[0]->tot2;
            $numbers[3] = $data[0]->tot3;
            $numbers[4] = $data[0]->tot4;

            $sal = $sector->getSal();

            return [
                (new TextCard())->width('1/4')->text($sector->manager)->heading('Resposabili di settore')->onlyOnDetail(),
                (new TextCard())
                ->width('1/4')
                ->heading('<div style="background-color: ' . Osm2CaiHelper::getSalColor($sal) . '; color: white; font-size: xx-large">' . number_format($sal * 100, 2) . ' %</div>')
                ->headingAsHtml()
                ->text('SAL')->onlyOnDetail(),
                (new TextCard())->width('1/4')->text('Numero percorsi sda 3/4')->heading($numbers[3]+$numbers[4])->onlyOnDetail(),
                (new TextCard())->width('1/4')->text('Numero percorsi atttesi')->heading($sector->num_expected)->onlyOnDetail(),
                $this->_getSdaCard(1,$numbers[1]),
                $this->_getSdaCard(2,$numbers[2]),
                $this->_getSdaCard(3,$numbers[3]),
                $this->_getSdaCard(4,$numbers[4]),
            ];

        }
        return [];
    }

    private function _getSdaCard(int $sda, int $num): TextCard
    {
        $link = '#sda ' . $sda;
        if ( $num > 0 )
        {
            $resourceId = request()->get('resourceId');
            $filter = base64_encode(json_encode([
                ['class' => HikingRoutesSectorFilter::class, 'value' => $resourceId]
            ]));
            $companyLinkWithFilter = trim(Nova::path(),'/') . "/resources/hiking-routes/lens/hiking-routes-status-$sda-lens?hiking-routes_filter=$filter";

            $link = "<a href=\"{$companyLinkWithFilter}\" target='_blank'>#sda $sda</a>";
        }


        return (new TextCard())->width('1/4')
            ->text('<div>' . $link . '</div>')
            ->textAsHtml()
            ->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor($sda) . '; color: white; font-size: xx-large">' . $num . '</div>')
            ->headingAsHtml()
            ->onlyOnDetail();
    }


    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        /**
         * @var \App\Models\User
         */
        $loggedInUser = Auth::user();

        //default filters
        $filters = [
            new SectorsRegionFilter,
            new SectorsProvinceFilter,
            new SectorsAreaFilter
        ];

        if ($loggedInUser->getTerritorialRole() == 'regional') {
            unset($filters[0]);
        }

        if ($loggedInUser->is_administrator) {
            $filters[] = new SectorsNullableFilter;
        }

        return $filters;
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
        return [
            new NoResponsabileSectorsColumnsLens,
            new NoNameSectorsColumnsLens,
            new NoNumExpectedColumnsLens
        ];
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
            (new BulkSectorsModeratorAssignAction)->canRun(function ($request, $zone) {
                return $request->user()->can('bulkAssignUser', $zone);
            }),
            (new UploadSectorGeometryDataAction)
            ->confirmText('Inserire un file con la nuova geometria del settore.')
            ->confirmButtonText('Aggiorna geometria')
            ->cancelButtonText("Annulla")
            ->canSee(function ($request) { return true;})
            ->canRun(function ($request, $user) { return true;}),
        ];
    }
}
