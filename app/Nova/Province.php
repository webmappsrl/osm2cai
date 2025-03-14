<?php

namespace App\Nova;

use App\Helpers\Osm2CaiHelper;
use App\Nova\Actions\DownloadGeojson;
use App\Nova\Actions\DownloadShape;
use App\Nova\Actions\DownloadKml;
use App\Nova\Filters\HikingRoutesProvinceFilter;
use App\Nova\Filters\HikingRoutesSectorFilter;
use Ericlagarda\NovaTextCard\TextCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;

class Province extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Province::class;
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
        'code',
        'full_code'
    ];
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static string $group = 'Territorio';
    /**
     * Custom priority level of the resource.
     *
     * @var int
     */
    public static $priority = 2;

    public static function label()
    {
        return 'Province';
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

        return $query->ownedBy( auth()->user() );
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
        $areasCount = count($this->areas);
        $sectorsCount = 0;

        foreach ($this->areas as $area) {
            $sectorsCount += count($area->sectors);
        }

        return [
            Text::make(__('Name'), 'name')->sortable(),
            Text::make(__('Code'), 'code')->sortable(),
            Text::make(__('Full code'), 'full_code')->sortable(),
            BelongsTo::make(__('Region')),
            Number::make(__('Areas'), 'areas', function () use ($areasCount) {
                return $areasCount;
            }),
            Number::make(__('Sectors'), 'areas', function () use ($sectorsCount) {
                return $sectorsCount;
            })
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
        if (!is_null($request['resourceId'])) {

            $province = Province::find($request['resourceId']);

            $data = DB::table('provinces_view')
                ->select(['tot', 'tot1', 'tot2', 'tot3', 'tot4'])
                ->where('id', $request['resourceId'])
                ->get();

            $numbers[1] = $data[0]->tot1;
            $numbers[2] = $data[0]->tot2;
            $numbers[3] = $data[0]->tot3;
            $numbers[4] = $data[0]->tot4;

            $sal = $province->getSal();

            return [
                (new TextCard())->width('1/4')->text($province->manager)->heading('Responsabili di settore')->onlyOnDetail(),
                (new TextCard())
                    ->width('1/4')
                    ->heading('<div style="background-color: ' . Osm2CaiHelper::getSalColor($sal) . '; color: white; font-size: xx-large">' . number_format($sal * 100, 2) . ' %</div>')
                    ->headingAsHtml()
                    ->text('SAL')->onlyOnDetail(),
                (new TextCard())->width('1/4')->text('Numero percorsi sda 3/4')->heading($numbers[3] + $numbers[4])->onlyOnDetail(),
                (new TextCard())->width('1/4')->text('Numero percorsi attesi')->heading($province->num_expected)->onlyOnDetail(),
                $this->_getSdaCard(1, $numbers[1]),
                $this->_getSdaCard(2, $numbers[2]),
                $this->_getSdaCard(3, $numbers[3]),
                $this->_getSdaCard(4, $numbers[4]),
            ];
        }
        return [];
    }

    private function _getSdaCard(int $sda, int $num): TextCard
    {
        $link = '#sda ' . $sda;
        if ($num > 0) {
            $resourceId = request()->get('resourceId');
            $filter = base64_encode(json_encode([
                ['class' => HikingRoutesProvinceFilter::class, 'value' => $resourceId]
            ]));
            $companyLinkWithFilter = trim(Nova::path(), '/') . "/resources/hiking-routes/lens/hiking-routes-status-$sda-lens?hiking-routes_filter=$filter";

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
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new \App\Nova\Filters\Region
        ];
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
            })
        ];
    }
}
