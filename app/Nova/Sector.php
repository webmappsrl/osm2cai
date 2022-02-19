<?php

namespace App\Nova;

use App\Helpers\NovaCurrentResourceActionHelper;
use App\Helpers\Osm2CaiHelper;
use App\Nova\Actions\DownloadGeojson;
use App\Nova\Actions\DownloadKml;
use App\Nova\Actions\DownloadShape;
use Ericlagarda\NovaTextCard\TextCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Imumz\LeafletMap\LeafletMap;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Number;
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
                return $this->area->province->region->name;
            })->hideWhenUpdating(),
            Text::make(__('Province'), 'area_id', function () {
                return $this->area->province->name;
            })->hideWhenUpdating(),
            Text::make(__('Area'), 'area_id', function () {
                return $this->area->name;
            })->hideWhenUpdating(),

        ];

        if (NovaCurrentResourceActionHelper::isDetail($request)) {
            $fields[] = 
                LeafletMap::make('Mappa')
                ->type('GeoJson')
                ->geoJson(json_encode($this->getEmptyGeojson()))
                ->center($this->getCentroid()[1], $this->getCentroid()[0])
                ->zoom(12)
                ->onlyOnDetail();
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
        return (new TextCard())->width('1/4')
            ->text('<div>#sda ' . $sda . '</div>')
            ->textAsHtml()
            ->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor($sda) . '; color: white; font-size: xx-large">' . $num . '</div>')
            ->headingAsHtml()
            ->onlyOnDetail();
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
