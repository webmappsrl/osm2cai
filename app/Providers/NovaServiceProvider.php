<?php

namespace App\Providers;

use App\Helpers\Osm2CaiHelper;
use App\Models\HikingRoute;
use App\Models\Region;
use App\Models\User;
use App\Nova\Dashboards\ItalyDashboard;
use App\Nova\Dashboards\RegionReferentDashboard;
use App\Nova\Dashboards\UserSectors;
use App\Nova\Metrics\AreasNumberByMyRegionValueMetric;
use App\Nova\Metrics\HikingRoutesNumberByMyRegionValueMetric;
use App\Nova\Metrics\HikingRoutesNumberStatus1ByMyRegionValueMetric;
use App\Nova\Metrics\HikingRoutesNumberStatus2ByMyRegionValueMetric;
use App\Nova\Metrics\HikingRoutesNumberStatus3ByMyRegionValueMetric;
use App\Nova\Metrics\HikingRoutesNumberStatus4ByMyRegionValueMetric;
use App\Nova\Metrics\ProvincesNumberByMyRegionValueMetric;
use App\Nova\Metrics\SectorsNumberByMyRegionValueMetric;
use App\Nova\Metrics\TotalAreasCount;
use App\Nova\Metrics\TotalProvincesCount;
use App\Nova\Metrics\TotalRegionsCount;
use App\Nova\Metrics\TotalSectorsCount;
use Ericlagarda\NovaTextCard\TextCard;
use Giuga\LaravelNovaSidebar\NovaSidebar;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Mako\CustomTableCard\CustomTableCard;
use Mako\CustomTableCard\Table\Cell;
use Mako\CustomTableCard\Table\Row;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        $this->app->alias(
            \App\Http\Controllers\Nova\LoginController::class,
            \Laravel\Nova\Http\Controllers\LoginController::class
        );
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        $cards = [];
        switch (Auth::user()->getTerritorialRole()) {
            case 'national' :
                $cards = $this->_nationalCards();
                break;
            case 'regional' :
                $cards = $this->_regionalCards();
                break;
            default :
                $cards = [
                    (new TextCard())
                        ->forceFullWidth()
                        ->heading('Nessun Permesso territoriale')
                        ->text('Contatta catastorei@cai.it per informazioni')
                        ->textAsHtml(),
                ];
        }
        return $cards;
    }

    private function _nationalCards()
    {

        $values = DB::table('hiking_routes')
            ->select('osm2cai_status', DB::raw('count(*) as num'))
            ->groupBy('osm2cai_status')
            ->get();

        $numbers = [];
        $numbers[1] = 0;
        $numbers[2] = 0;
        $numbers[3] = 0;
        $numbers[4] = 0;

        if (count($values) > 0) {
            foreach ($values as $value) {
                $numbers[$value->osm2cai_status] = $value->num;
            }
        }

        $tot = array_sum($numbers);


        $sal = (
                HikingRoute::where('osm2cai_status', 1)->count() * 0.25 +
                HikingRoute::where('osm2cai_status', 2)->count() * 0.50 +
                HikingRoute::where('osm2cai_status', 3)->count() * 0.75 +
                HikingRoute::where('osm2cai_status', 4)->count()
            ) / Region::sum('num_expected');
        $sal_color = Osm2CaiHelper::getSalColor($sal);

        $cards = [
            (new TextCard())
                ->width('1/4')
                ->heading(Auth::user()->name)
                ->text('Username')
                ->center(false),
            (new TextCard())
                ->width('1/4')
                ->heading(Auth::user()->getPermissionString())
                ->text('Permessi')
                ->center(false),
            (new TextCard())
                ->width('1/4')
                ->heading('TBI')
                ->text('LastLogin')
                ->center(false),
            (new TextCard())
                ->width('1/4')
                ->heading('<div style="background-color: ' . $sal_color . '; color: white; font-size: xx-large">' . number_format($sal * 100, 2) . ' %</div>')
                ->headingAsHtml()
                ->text('SAL Nazionale'),

            (new TextCard())->width('1/4')
                ->text('<div>#sda 1 <a href="' . url('/resources/hiking-routes/lens/hiking-routes-status-1-lens') . '">[Esplora]</a></div>')
                ->textAsHtml()
                ->heading('<div style="background-color: '.Osm2CaiHelper::getSdaColor(1).'; color: white; font-size: xx-large">' . $numbers[1] . '</div>')
                ->headingAsHtml(),
            (new TextCard())->width('1/4')
                ->text('#sda 2')->heading('<div style="background-color: '.Osm2CaiHelper::getSdaColor(2).'; color: white; font-size: xx-large">' . $numbers[2] . '</div>')->headingAsHtml(),
            (new TextCard())->width('1/4')
                ->text('#sda 3')->heading('<div style="background-color: '.Osm2CaiHelper::getSdaColor(3).'; color: white; font-size: xx-large">' . $numbers[3] . '</div>')->headingAsHtml(),
            (new TextCard())->width('1/4')
                ->text('#sda 4')->heading('<div style="background-color: '.Osm2CaiHelper::getSdaColor(4).'; color: white; font-size: xx-large">' . $numbers[4] . '</div>')->headingAsHtml(),


        ];

        $cards = array_merge($cards, [$this->_getRegionsTableCard()]);

        return $cards;

    }

    private function _regionalCards()
    {
        $data = DB::table('regions_view')
            ->select(['tot', 'tot1', 'tot2', 'tot3', 'tot4'])
            ->where('id', Auth::user()->region->id)
            ->get();
        $numbers[1] = $data[0]->tot1;
        $numbers[2] = $data[0]->tot2;
        $numbers[3] = $data[0]->tot3;
        $numbers[4] = $data[0]->tot4;

        $num_areas = 0;
        $num_sectors = 0;
        foreach (Auth::user()->region->provinces as $province) {
            $num_areas += $province->areas->count();
            if ($province->areas->count() > 0) {
                foreach ($province->areas as $area) {
                    $num_sectors += $area->sectors->count();
                }
            }
        }

        $sal = Auth::user()->region->getSal();
        $sal_color = Osm2CaiHelper::getSalColor($sal);

        $cards = [
            (new TextCard())
                ->width('1/4')
                ->heading(Auth::user()->name)
                ->text('Username')
                ->center(false),
            (new TextCard())
                ->width('1/4')
                ->heading(Auth::user()->getPermissionString())
                ->text('Permessi')
                ->center(false),
            (new TextCard())
                ->width('1/4')
                ->heading('TBI')
                ->text('LastLogin')
                ->center(false),
            (new TextCard())
                ->width('1/4')
                ->heading('<div style="background-color: ' . $sal_color . '; color: white; font-size: xx-large">' . number_format($sal * 100, 2) . ' %</div>')
                ->headingAsHtml()
                ->text('SAL ' . Auth::user()->region->name),

//                <a href="' . route('api.hiking-routes-shapefile.region', ['id' => \auth()->user()->region->id]) . '" >Download shape Percorsi</a>
            (new TextCard())
                ->forceFullWidth()
                ->heading(\auth()->user()->region->name)
                ->text('<h4 class="font-light">
                <a href="' . route('api.geojson_complete.region', ['id' => \auth()->user()->region->id]) . '" >Download geojson Percorsi</a>
                <a href="' . route('api.shapefile.region', ['id' => \auth()->user()->region->id]) . '" >Download shape Settori</a>
                 <a href="' . route('api.csv.region', ['id' => \auth()->user()->region->id]) . '" >Download CSV Percorsi</a>
                 ')
                ->textAsHtml(),

            // General Info
            (new TextCard())
                ->width('1/4')
                ->heading(Auth::user()->region->provinces->count())
                ->text('#province'),
            (new TextCard())
                ->width('1/4')
                ->heading($num_areas)
                ->text('#aree'),
            (new TextCard())
                ->width('1/4')
                ->heading($num_sectors)
                ->text('#settori')
                ->width('1/4'),
            (new TextCard())
                ->width('1/4')
                ->heading(array_sum($numbers))
                ->text('#tot percorsi'),

            $this->_getSdaCard(1, $numbers[1]),
            $this->_getSdaCard(2, $numbers[2]),
            $this->_getSdaCard(3, $numbers[3]),
            $this->_getSdaCard(4, $numbers[4]),


        ];

        $cards = array_merge($cards, [$this->_getSectorsTableCard()]);

        return $cards;

    }

    private function _getSdaCard(int $sda, int $num): TextCard
    {

        $path = '/resources/hiking-routes/lens/hiking-routes-status-' . $sda . '-lens';
        return (new TextCard())->width('1/4')
            ->text('<div>#sda ' . $sda . ' <a href="' . url($path) . '">[Esplora]</a></div>')
            ->textAsHtml()
            ->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor($sda) . '; color: white; font-size: xx-large">' . $num . '</div>')
            ->headingAsHtml();
    }

    /**
     * @return CustomTableCard
     */
    private function _getRegionsTableCard(): CustomTableCard
    {

        $regionsCard = new CustomTableCard();
        $regionsCard->title(__('SDA e SAL Regioni'));

        // Headings
        $regionsCard->header([
            new Cell(__('Regione')),
            new Cell(__('#1')),
            new Cell(__('#2')),
            new Cell(__('#3')),
            new Cell(__('#4')),
            new Cell(__('#tot')),
            new Cell(__('#att')),
            new Cell(__('SAL')),
        ]);

        // Extract data from views
        // select name,code,tot1,tot2,tot3,tot4,num_expected from regions_view;
        $items = DB::table('regions_view')
            ->select('name', 'code', 'tot1', 'tot2', 'tot3', 'tot4', 'num_expected')
            ->get();

        $data = [];
        foreach ($items as $item) {

            $tot = $item->tot1 + $item->tot2 + $item->tot3 + $item->tot4;
            $sal = (($item->tot1 * 0.25) + ($item->tot2 * 0.50) + ($item->tot3 * 0.75) + ($item->tot4)) / $item->num_expected;
            $sal_color = Osm2CaiHelper::getSalColor($sal);

            $row = new Row(
                new Cell("{$item->name} ({$item->code})"),
                new Cell($item->tot1),
                new Cell($item->tot2),
                new Cell($item->tot3),
                new Cell($item->tot4),
                new Cell($tot),
                new Cell($item->num_expected),
                new Cell('<div style="background-color: ' . $sal_color . '; color: white; font-size: x-large">' . number_format($sal * 100, 2) . ' %</div>'),
            );
            $data[] = $row;
        }

        $regionsCard->data($data);

        return $regionsCard;
    }

    /**
     * @return CustomTableCard
     */
    private function _getSectorsTableCard(): CustomTableCard
    {

        $sectorsCard = new CustomTableCard();
        $sectorsCard->title(__('SDA e SAL Settori - ' . Auth::user()->region->name));

        // Headings
        $sectorsCard->header([
            new Cell(__('Settore')),
            new Cell(__('#1')),
            new Cell(__('#2')),
            new Cell(__('#3')),
            new Cell(__('#4')),
            new Cell(__('#tot')),
            new Cell(__('#att')),
            new Cell(__('SAL')),
        ]);

        // Get sectors_id
        $sectors_id = [];
        foreach (Auth::user()->region->provinces as $province) {
            if (Arr::accessible($province->areas)) {
                foreach ($province->areas as $area) {
                    if (Arr::accessible($area->sectors)) {
                        $sectors_id = array_merge($sectors_id, $area->sectors->pluck('id')->toArray());
                    }
                }
            }
        }

        // Extract data from views
        // select name,code,tot1,tot2,tot3,tot4,num_expected from regions_view;
        $items = DB::table('sectors_view')
            ->select('full_code', 'tot1', 'tot2', 'tot3', 'tot4', 'num_expected')
            ->whereIn('id', $sectors_id)
            ->get();

        $data = [];
        foreach ($items as $item) {

            $tot = $item->tot1 + $item->tot2 + $item->tot3 + $item->tot4;
            $sal = (($item->tot1 * 0.25) + ($item->tot2 * 0.50) + ($item->tot3 * 0.75) + ($item->tot4)) / $item->num_expected;
            $sal_color = Osm2CaiHelper::getSalColor($sal);

            $row = new Row(
                new Cell("{$item->full_code}"),
                new Cell($item->tot1),
                new Cell($item->tot2),
                new Cell($item->tot3),
                new Cell($item->tot4),
                new Cell($tot),
                new Cell($item->num_expected),
                new Cell('<div style="background-color: ' . $sal_color . '; color: white; font-size: x-large">' . number_format($sal * 100, 2) . ' %</div>'),
            );
            $data[] = $row;
        }

        $sectorsCard->data($data);

        return $sectorsCard;
    }

    private function _getUserSectorsListCard()
    {
        $sectorsCard = new CustomTableCard();
        $sectorsCard->title(__('I miei settori'));
        $sectorsCard->header([
            new Cell(__('Regione')),
            new Cell(__('Provincia')),
            new Cell(__('Settore')),
            new Cell(__('Tot Percorsi')),
            new Cell(__('0')),
            new Cell(__('1')),
            new Cell(__('2')),
            new Cell(__('3')),
            new Cell(__('4')),
        ]);
        $user = User::getEmulatedUser();
        $sectors = $user->getSectors();
        $data = [];
        foreach ($sectors as $sector) {
            $row = new Row(
                new Cell($sector->area->province->region->name),
                new Cell($sector->area->province->name),
                new Cell($sector->full_code),
                new Cell($sector->hikingRoutes()->count()),
                new Cell($sector->hikingRoutes()->where('osm2cai_status', '=', 0)->count()),
                new Cell($sector->hikingRoutes()->where('osm2cai_status', '=', 1)->count()),
                new Cell($sector->hikingRoutes()->where('osm2cai_status', '=', 2)->count()),
                new Cell($sector->hikingRoutes()->where('osm2cai_status', '=', 3)->count()),
                new Cell($sector->hikingRoutes()->where('osm2cai_status', '=', 4)->count()),
            );
            $row->viewLink('/resources/sectors/' . $sector->id);
            $data[] = $row;
        }
        $sectorsCard->data($data);

        return $sectorsCard;
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            (new ItalyDashboard()),
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            (new NovaSidebar())->hydrate([
                'Tools' => [
                    ['Mappa', 'http://osm2cai.j.webmapp.it']
                ],
            ])
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Nova::sortResourcesBy(function ($resource) {
            return $resource::$priority ?? 99999;
        });
    }
}
