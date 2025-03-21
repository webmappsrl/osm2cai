<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\User;
use App\Models\Sector;
use Laravel\Nova\Nova;
use App\Models\Province;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Nova\Dashboards\SAL;
use App\Helpers\Osm2CaiHelper;
use App\Services\CacheService;
use App\Services\CardsService;
use App\Nova\Dashboards\Utenti;
use App\Observers\SectorObserver;
use Spatie\SchemaOrg\MenuSection;
use Illuminate\Support\Facades\DB;
use Mako\CustomTableCard\Table\Row;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Mako\CustomTableCard\Table\Cell;
use App\Nova\Dashboards\AcquaSorgente;
use Ericlagarda\NovaTextCard\TextCard;
use App\Nova\Dashboards\ItalyDashboard;
use App\Nova\Dashboards\Percorribilità;
use App\Nova\Dashboards\EcPoisDashboard;
use App\Nova\Dashboards\PercorsiFavoriti;
use App\Nova\Dashboards\SectorsDashboard;
use Giuga\LaravelNovaSidebar\NovaSidebar;
use Mako\CustomTableCard\CustomTableCard;
use Laravel\Nova\NovaApplicationServiceProvider;

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
        Nova::style('admin', public_path('css/admin.css'));
        Nova::script('nova-custom', asset('js/nova-custom.js')); //script to hide "create and add another" button in ugc-poi resource
        Sector::observe(SectorObserver::class);
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
                'team@webmapp.it',
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
        /**
         * @var \App\Models\User
         */
        $user = Auth::user();
        switch ($user->getTerritorialRole()) {
            case 'admin':
                $cards = $this->_nationalCards();
                break;
            case 'national':
                $cards = $this->_nationalCards();
                break;
                //define local cards
                //"smallest" model related to user win
            case 'local':
                if ($user->sectors->count())
                    $cards = $this->_localCardsByModelClassName(Sector::class);
                elseif ($user->areas->count())
                    $cards = $this->_localCardsByModelClassName(Area::class);
                else
                    $cards = $this->_localCardsByModelClassName(Province::class);
                break;
            case 'regional':
                $cards = $this->_regionalCards();
                break;
            default:
                $cards = [
                    (new TextCard())
                        ->forceFullWidth()
                        ->heading('Modalità visualizzazione - Nessun permesso territoriale')
                        ->text('Contatta il tuo Referente SOSEC Regionale o catastorei@cai.it per informazioni.<br><br>
                        La piattaforma può essere consultata attraverso le voci nel menu disponibile sulla sinistra di questa schermata')
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


        $cardsService = new CardsService;

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
            $cardsService->getNationalSalCard(),
            (new TextCard())->width('1/4')
                ->text('<div>#sda 1 <a href="' . url('/resources/hiking-routes/lens/hiking-routes-status-1-lens') . '">[Esplora]</a></div>')
                ->textAsHtml()
                ->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor(1) . '; color: white; font-size: xx-large">' . $numbers[1] . '</div>')
                ->headingAsHtml(),
            (new TextCard())->width('1/4')
                ->text('#sda 2')->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor(2) . '; color: white; font-size: xx-large">' . $numbers[2] . '</div>')->headingAsHtml(),
            (new TextCard())->width('1/4')
                ->text('#sda 3')->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor(3) . '; color: white; font-size: xx-large">' . $numbers[3] . '</div>')->headingAsHtml(),
            (new TextCard())->width('1/4')
                ->text('#sda 4')->heading('<div style="background-color: ' . Osm2CaiHelper::getSdaColor(4) . '; color: white; font-size: xx-large">' . $numbers[4] . '</div>')->headingAsHtml(),


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
        $area_codes = [];
        $num_sectors = 0;
        foreach (Auth::user()->region->provinces as $province) {
            array_push($area_codes, implode(',', $province->areas->pluck('code')->toArray()));
            if ($province->areas->count() > 0) {
                foreach ($province->areas as $area) {
                    $num_sectors += $area->sectors->count();
                }
            }
        }
        $area_codes = implode(',', $area_codes);
        $area_codes = implode(',', array_unique(explode(',', $area_codes)));
        $num_areas = count(explode(',', $area_codes));

        $sal = Auth::user()->region->getSal();
        $sal_color = Osm2CaiHelper::getSalColor($sal);

        $syncDate = app()->make(CacheService::class)->getLastOsmSyncDate();
        $SALIssueStatus = $this->getSalIssueStatus();

        $cards = [
            (new TextCard)
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
                ->heading($SALIssueStatus)
                ->text('SAL Stato percorribilitá')
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
    <p>&nbsp;</p>
<a href="' . route('loading-download', ['type' => 'geojson-complete', 'model' => 'region', 'id' => \auth()->user()->region->id]) . '" target="_blank">Download geojson Percorsi</a>

<a href="' . route('loading-download', ['type' => 'shapefile', 'model' => 'region', 'id' => \auth()->user()->region->id]) . '" target="_blank">Download shape Settori</a>

<a href="' . route('loading-download', ['type' => 'csv', 'model' => 'region', 'id' => \auth()->user()->region->id]) . '" target="_blank">Download CSV Percorsi</a>    <p>&nbsp;</p>
    <p>Ultima sincronizzazione da osm: ' . $syncDate . '</p>
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

        $user = Auth::user();
        $provinceCards = [];
        foreach ($user->region->provinces as $province) {
            $provinceCards[] = $this->_getChildrenTableCardByModel($province); //areas
        }

        //$cardsService = new CardsService;
        $cards = array_merge(
            $cards,
            [$this->_getChildrenTableCardByModel($user->region)], //provinces
            $provinceCards, //areas
            //[$cardsService->getSectorsTableCard()]//sectors
        );


        return $cards;
    }

    /**
     * Get dashboard cards for authenticated user with "local" territorial code
     * @see \App\Model\User->getTerritorialCode()
     *
     * @return void
     */
    private function _localCardsByModelClassName($modelClassName)
    {
        $abstractModel = (new $modelClassName);
        $table = $abstractModel->getTable();
        $view = $abstractModel->getView();
        $user = Auth::user();




        $data = DB::table($view)
            ->select(['tot', 'tot1', 'tot2', 'tot3', 'tot4'])
            ->whereIn('id', $user->$table->pluck('id')->all())
            ->get();
        $numbers[1] = $data->sum('tot1');
        $numbers[2] = $data->sum('tot2');
        $numbers[3] = $data->sum('tot3');
        $numbers[4] = $data->sum('tot4');

        $num_provinces = 0;
        $num_areas = 0;
        $num_sectors = 0;
        $salHtml = '';


        if ($table == 'provinces') {
            $num_provinces = $user->provinces->count();
            foreach ($user->provinces as $province) {

                $sal = $province->getSal();
                $sal_color = Osm2CaiHelper::getSalColor($sal);
                $salHtml .= $province->name . '<div style="background-color: ' . $sal_color . '; color: white; font-size: xx-large">' .
                    number_format($sal * 100, 2) . ' %</div>';

                $num_areas += $province->areas->count();
                if ($province->areas->count() > 0) {
                    foreach ($province->areas as $area) {
                        $num_sectors += $area->sectors->count();
                    }
                }
            }
        } elseif ($table == 'areas') {

            if ($user->areas->count() > 0) {
                $num_areas = $user->areas->count();
                foreach ($user->areas as $area) {
                    $sal = $area->getSal();
                    $sal_color = Osm2CaiHelper::getSalColor($sal);
                    $salHtml .= $area->name . '<div style="background-color: ' . $sal_color . '; color: white; font-size: xx-large">' .
                        number_format($sal * 100, 2) . ' %</div>';
                    $num_sectors += $area->sectors->count();
                }
            }
        } elseif ($table == 'sectors') {
            $num_sectors = $user->sectors->count();
            foreach ($user->sectors as $sector) {
                $sal = $sector->getSal();
                $sal_color = Osm2CaiHelper::getSalColor($sal);
                $salHtml .= $sector->name . '<div style="background-color: ' . $sal_color . '; color: white; font-size: xx-large">' .
                    number_format($sal * 100, 2) . ' %</div>';
            }
        }




        $tableSingular = Str::singular($table);
        ob_start();
        foreach ($user->$table as $relatedModel) {
            $id = $relatedModel->id;

            //local referent should download hiking routes geojson for the sector and not the geojson of the sector
            if ($tableSingular == 'sector') {
                $type = 'geojson-complete';
            } else {
                $type = 'geojson';
            }
?>
            <h5><?= $relatedModel->name ?>: </h5>
            <a href="<?= route("loading-download", ['type' => $type, 'model' => $tableSingular, 'id' => $id]) ?>" target="_blank">Download
                geojson
                Percorsi</a>
            <a href="<?= route("loading-download", ['type' => 'shapefile', 'model' => $tableSingular, 'id' => $id]) ?>" target="_blank">Download
                shape
                geometria territoriale</a>
            <a href="<?= route("loading-download", ['type' => 'csv', 'model' => $tableSingular, 'id' => $id]) ?>" target="_blank">Download
                csv
                Percorsi</a>
<?php
        }
        $downloadLiks = ob_get_clean();
        $syncDate = app()->make(CacheService::class)->getLastOsmSyncDate();


        $cards = [
            (new TextCard())
                ->width('1/4')
                ->heading($user->name)
                ->text('Username')
                ->center(false),
            (new TextCard())
                ->width('1/4')
                ->heading($user->getPermissionString())
                ->text('Permessi')
                ->center(false),
            (new TextCard())
                ->width('1/4')
                ->heading('TBI')
                ->text('LastLogin')
                ->center(false),
            (new TextCard())
                ->width('1/4')
                ->heading($salHtml)
                ->headingAsHtml(),
            //->text('SAL ' . $user->region->name),

            //                <a href="' . route('api.hiking-routes-shapefile.region', ['id' => \auth()->user()->region->id]) . '" >Download shape Percorsi</a>
            (new TextCard())
                ->forceFullWidth()
                ->text('<div class="font-light">
                <p>&nbsp;</p>' .
                    $downloadLiks .
                    '<p>&nbsp;</p>
                 <p>Ultima sincronizzazione da osm: ' . $syncDate . '</p>
                 </div>')
                ->textAsHtml(),

            // General Info
            (new TextCard())
                ->width('1/4')
                ->heading((string) $num_provinces)
                ->text('#province'),
            (new TextCard())
                ->width('1/4')
                ->heading((string) $num_areas)
                ->text('#aree'),
            (new TextCard())
                ->width('1/4')
                ->heading((string) $num_sectors)
                ->text('#settori')
                ->width('1/4'),
            (new TextCard())
                ->width('1/4')
                ->heading((string) (array_sum($numbers)))
                ->text('#tot percorsi'),

            $this->_getSdaCard(1, $numbers[1]),
            $this->_getSdaCard(2, $numbers[2]),
            $this->_getSdaCard(3, $numbers[3]),
            $this->_getSdaCard(4, $numbers[4]),


        ];

        $cards = array_merge($cards, [$this->_getSectorsTableCardByModelClassName($modelClassName)]);

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
     * A copy of $this->_getSectorsTableCard() dynamicized with modelClassName parameter
     * to load cards for: provinces, areas and sectors models
     *
     * @param string $name - the class path to load the model with its properties
     * @return CustomTableCard
     */
    private function _getSectorsTableCardByModelClassName($modelClassName): CustomTableCard
    {

        $sectorsCard = new CustomTableCard();

        $user = Auth::user();
        $table = (new $modelClassName)->getTable();
        $modelNamesString = $user->$table->pluck('name')->implode(', ');

        $sectorsCard->title(__('SDA e SAL Settori - ' . $modelNamesString));

        // Headings
        $sectorsCard->header([
            new Cell(__('Settore')),
            new Cell(__('Nome')),
            new Cell(__('#1')),
            new Cell(__('#2')),
            new Cell(__('#3')),
            new Cell(__('#4')),
            new Cell(__('#tot')),
            new Cell(__('#att')),
            new Cell(__('SAL')),
            new Cell(__('Actions')),
        ]);

        // Get sectors_id
        $sectors_id = [];

        if ($table == 'provinces') {
            foreach ($user->provinces as $province) {
                if (Arr::accessible($province->areas)) {
                    foreach ($province->areas as $area) {
                        if (Arr::accessible($area->sectors)) {
                            $sectors_id = array_merge($sectors_id, $area->sectors->pluck('id')->toArray());
                        }
                    }
                }
            }
        } elseif ($table == 'areas') {
            foreach ($user->areas as $area) {
                if (Arr::accessible($area->sectors)) {
                    $sectors_id = array_merge($sectors_id, $area->sectors->pluck('id')->toArray());
                }
            }
        } elseif ($table == 'sectors') {
            $sectors_id = $user->sectors->pluck('id')->toArray();
        }


        // Extract data from views
        // select name,code,tot1,tot2,tot3,tot4,num_expected from regions_view;
        $items = DB::table('sectors_view')
            ->select('id', 'full_code', 'tot1', 'tot2', 'tot3', 'tot4', 'num_expected')
            ->whereIn('id', $sectors_id)
            ->get();

        $data = [];
        foreach ($items as $item) {

            $tot = $item->tot1 + $item->tot2 + $item->tot3 + $item->tot4;
            $sal = (($item->tot1 * 0.25) + ($item->tot2 * 0.50) + ($item->tot3 * 0.75) + ($item->tot4)) / $item->num_expected;
            $sal_color = Osm2CaiHelper::getSalColor($sal);
            $sector = Sector::find($item->id);

            $row = new Row(
                new Cell("{$item->full_code}"),
                new Cell($sector->human_name),
                new Cell($item->tot1),
                new Cell($item->tot2),
                new Cell($item->tot3),
                new Cell($item->tot4),
                new Cell($tot),
                new Cell($item->num_expected),
                new Cell('<div style="background-color: ' . $sal_color . '; color: white; font-size: x-large">' . number_format($sal * 100, 2) . ' %</div>'),
                new Cell('<a href="/resources/sectors/' . $item->id . '">[VIEW]</a>'),
            );
            $data[] = $row;
        }

        $sectorsCard->data($data);

        return $sectorsCard;
    }




    private function _getChildrenTableCardByModel($model): CustomTableCard
    {

        $sectorsCard = new CustomTableCard();




        $modelName = $model->name;
        $childrenAbstractModel = $model->children()->getRelated();
        $childrenIds = $model->childrenIds();




        $childrenTable = $childrenAbstractModel->getTable();



        $sectorsCard->title("SDA e SAL $childrenTable - $modelName");

        // Headings
        $sectorsCard->header([
            new Cell($childrenTable),
            new Cell(__('Nome')),
            new Cell(__('#1')),
            new Cell(__('#2')),
            new Cell(__('#3')),
            new Cell(__('#4')),
            new Cell(__('#tot')),
            new Cell(__('#att')),
            new Cell(__('SAL')),
            new Cell(__('Actions')),
        ]);


        // Extract data from views
        // select name,code,tot1,tot2,tot3,tot4,num_expected from regions_view;
        $items = DB::table($childrenAbstractModel->getView())
            ->select('id', 'full_code', 'tot1', 'tot2', 'tot3', 'tot4', 'num_expected')
            ->whereIn('id', $childrenIds)
            ->get();

        $data = [];
        foreach ($items as $item) {

            $tot = $item->tot1 + $item->tot2 + $item->tot3 + $item->tot4;
            $sal = (($item->tot1 * 0.25) + ($item->tot2 * 0.50) + ($item->tot3 * 0.75) + ($item->tot4)) / $item->num_expected;
            $sal_color = Osm2CaiHelper::getSalColor($sal);
            $sector = $childrenAbstractModel::find($item->id);

            $row = new Row(
                new Cell("{$item->full_code}"),
                new Cell($sector->name),
                new Cell($item->tot1),
                new Cell($item->tot2),
                new Cell($item->tot3),
                new Cell($item->tot4),
                new Cell($tot),
                new Cell($item->num_expected),
                new Cell('<div style="background-color: ' . $sal_color . '; color: white; font-size: x-large">' . number_format($sal * 100, 2) . ' %</div>'),
                new Cell('<a href="/resources/' . ($childrenTable == 'regions' ? 'region' : $childrenTable)  . '/' . $item->id . '">[VIEW]</a>'),
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

    private function getSalIssueStatus(): string
    {
        $percorribile = 0;
        $nonPercorribile = 0;
        $percorribileParzialmente = 0;
        $hikingRoutes = auth()->user()->region->hikingRoutes()->get();

        foreach ($hikingRoutes as $hr) {
            switch ($hr->issues_status) {
                case 'percorribile':
                    $percorribile++;
                    break;
                case 'non percorribile':
                    $nonPercorribile++;
                case 'percorribile parzialmente':
                    $percorribileParzialmente++;
                    break;
            }
        }

        $result = (($percorribile + $percorribileParzialmente + $nonPercorribile) / count($hikingRoutes)) * 100;
        $result = round($result, 2);

        return strval($result) . '%';
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        $dashboards = [
            new ItalyDashboard,
            new PercorsiFavoriti,
            new EcPoisDashboard
        ];

        /**
         * @var \App\Models\User
         */
        $loggedInUser = Auth::user();
        if ($loggedInUser->getTerritorialRole() == 'admin') {
            $dashboards[] = new Utenti;
            $dashboards[] = new Percorribilità();
            $dashboards[] = new SAL();
            $dashboards[] = new AcquaSorgente();
        }
        if ($loggedInUser->getTerritorialRole() == 'national') {
            $dashboards[] = new Percorribilità();
            $dashboards[] = new SAL();
            $dashboards[] = new AcquaSorgente();
        }
        if ($loggedInUser->getTerritorialRole() == 'regional') {
            $dashboards[] = new SectorsDashboard;
            $dashboards[] = new Percorribilità($loggedInUser);
        }
        if ($loggedInUser->getTerritorialRole() == 'local') {
            $dashboards[] = new Percorribilità($loggedInUser);
        }

        return $dashboards;
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        $tools = [
            ['Esporta Rilievi', route('export.form')],
            ['Mappa Settori', 'http://osm2cai.j.webmapp.it/#/main/map'],
            ['Mappa Percorsi', 'https://26.app.geohub.webmapp.it/#/map'],
            ['INFOMONT', 'https://15.app.geohub.webmapp.it/#/map'],
            ['LoScarpone-Export', route('loscarpone-export')],
            ['API', '/api/documentation'],
            ['Documentazione OSM2CAI', 'https://catastorei.gitbook.io/documentazione-osm2cai/'],
        ];
        $isAdmin = Auth::user()->is_administrator;
        if ($isAdmin) {
            $tools[] = ['Sync UGC', route('import-ugc')];
            $tools[] = ['Sync EcPois,Mountain groups and Huts to regions', route('sync-to-regions')];
            $tools[] = ['Logs', '/logs'];
        }
        return [
            (new NovaSidebar())->hydrate([
                'Tools' => $tools,
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
