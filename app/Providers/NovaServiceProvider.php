<?php

namespace App\Providers;

use App\Models\User;
use App\Nova\Dashboards\UserSectors;
use App\Nova\Metrics\TotalAreasCount;
use App\Nova\Metrics\TotalProvincesCount;
use App\Nova\Metrics\TotalRegionsCount;
use App\Nova\Metrics\TotalSectorsCount;
use Giuga\LaravelNovaSidebar\NovaSidebar;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Mako\CustomTableCard\CustomTableCard;
use Mako\CustomTableCard\Table\Cell;
use Mako\CustomTableCard\Table\Row;

class NovaServiceProvider extends NovaApplicationServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        parent::boot();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes() {
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
    protected function gate() {
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
    protected function cards() {
        return [
            (new TotalRegionsCount())->width('1/4'),
            (new TotalProvincesCount())->width('1/4'),
            (new TotalAreasCount())->width('1/4'),
            (new TotalSectorsCount())->width('1/4'),
            $this->_getUserSectorsListCard()
        ];
    }

    private function _getUserSectorsListCard() {
        $sectorsCard = new CustomTableCard();
        $sectorsCard->title(__('My Sectors'));
        $sectorsCard->header([
            new Cell(__('Name')),
            new Cell(__('Full Code')),
            new Cell(__('Area')),
            new Cell(__('Province')),
            new Cell(__('Region')),
        ]);
        $user = User::getEmulatedUser();
        $sectors = $user->getSectors();
        $data = [];
        foreach ($sectors as $sector) {
            $row = new Row(
                new Cell($sector->name),
                new Cell($sector->full_code),
                new Cell($sector->area->name),
                new Cell($sector->area->province->name),
                new Cell($sector->area->province->region->name),
            );
            $row->viewLink('/resources/sectors?sectors_page=1&sectors_search=' . $sector->full_code);
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
    protected function dashboards() {
        return [
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools() {
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
    public function register() {
        Nova::sortResourcesBy(function ($resource) {
            return $resource::$priority ?? 99999;
        });
    }
}
