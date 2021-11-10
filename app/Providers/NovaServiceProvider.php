<?php

namespace App\Providers;

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
use Illuminate\Support\Facades\Auth;
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
        if (Auth::user()->getPermissionString() == 'Referente nazionale') {
            $num_expected = Region::sum('num_expected');
            $done = number_format((
                    HikingRoute::where('osm2cai_status', 1)->count() * 0.25 +
                    HikingRoute::where('osm2cai_status', 2)->count() * 0.50 +
                    HikingRoute::where('osm2cai_status', 3)->count() * 0.75 +
                    HikingRoute::where('osm2cai_status', 4)->count()
                ) / Region::sum('num_expected') * 100, 2);
            $info = (new TextCard())->width('1/4')->heading("$done %")->text('SAL nazionale')->center(false);
        } else {
            $info = (new TextCard())->width('1/4')->heading('TBI')->text('????')->center(false);
        }

        $main_cards = [
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
            $info,
            // $this->_getUserSectorsListCard()
        ];

        if (!is_null(Auth::user()->region_id)) {
            $cards = array_merge($main_cards, $this->_getRegionCards());
        } else {
            $cards = $main_cards;
        }

        return $cards;
    }

    private function _getRegionCards(): array
    {
        return [

            // Heading with region name
            (new TextCard())
                ->forceFullWidth()
                ->heading(\auth()->user()->region->name)
                ->text('<h4 class="font-light">
                         <a href="' . route('api.shapefile.region', ['id' => \auth()->user()->region->id]) . '" >Download shape Settori</a>
                         <a href="' . route('api.csv.region', ['id' => \auth()->user()->region->id]) . '" >Download CSV Percorsi</a>
                         ')
                ->textAsHtml(),

            // General Info
            (new ProvincesNumberByMyRegionValueMetric())
                ->width('1/4'),
            (new AreasNumberByMyRegionValueMetric())
                ->width('1/4'),
            (new SectorsNumberByMyRegionValueMetric())
                ->width('1/4'),
            (new HikingRoutesNumberByMyRegionValueMetric())
                ->width('1/4'),

            // Info on hiking routes
            (new HikingRoutesNumberStatus1ByMyRegionValueMetric())
                ->width('1/4'),
            (new HikingRoutesNumberStatus2ByMyRegionValueMetric())
                ->width('1/4'),
            (new HikingRoutesNumberStatus3ByMyRegionValueMetric())
                ->width('1/4'),
            (new HikingRoutesNumberStatus4ByMyRegionValueMetric())
                ->width('1/4'),
        ];
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
