<?php

namespace App\Nova;

use App\Models\User;
use DKulyk\Nova\Tabs;
use Laravel\Nova\Panel;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Imumz\LeafletMap\LeafletMap;
use Laravel\Nova\Fields\Boolean;
use App\Nova\Actions\CreateIssue;
use Laravel\Nova\Fields\Textarea;
use Illuminate\Support\Facades\Auth;
use Ericlagarda\NovaTextCard\TextCard;
use App\Nova\Actions\SectorRefactoring;
use App\Nova\Filters\DeleteOnOsmFilter;
use App\Nova\Filters\HikingRouteStatus;
use App\Nova\Filters\IssueStatusFilter;
use Illuminate\Support\Facades\Storage;
use App\Nova\Filters\GeometrySyncFilter;
use AddRegionFavoriteToHikingRoutesTable;
use App\Models\EcPoi;
use App\Nova\Lenses\HikingRoutesStatusLens;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Filters\HikingRoutesAreaFilter;
use App\Nova\Lenses\HikingRoutesStatus0Lens;
use App\Nova\Lenses\HikingRoutesStatus1Lens;
use App\Nova\Lenses\HikingRoutesStatus2Lens;
use App\Nova\Lenses\HikingRoutesStatus3Lens;
use App\Nova\Lenses\HikingRoutesStatus4Lens;
use App\Nova\Actions\DeleteHikingRouteAction;
use App\Nova\Filters\HrCorrectGeometryFilter;
use Laravel\Nova\Http\Requests\ActionRequest;
use App\Nova\Actions\OsmSyncHikingRouteAction;
use App\Nova\Filters\HikingRoutesRegionFilter;
use App\Nova\Filters\HikingRoutesSectorFilter;
use App\Nova\Actions\ValidateHikingRouteAction;
use App\Nova\Filters\HikingRoutesProvinceFilter;
use App\Nova\Actions\AddFeatureImageToHikingRoute;
use App\Nova\Actions\UploadValidationRawDataAction;
use App\Nova\Filters\HikingRoutesTerritorialFilter;
use App\Nova\Actions\RevertValidateHikingRouteAction;
use App\Nova\Filters\RegionFavoriteHikingRouteFilter;
use Wm\MapMultiLinestringNova\MapMultiLinestringNova;
use App\Nova\Actions\ToggleRegionFavoriteHikingRouteAction;
use App\Nova\Actions\AddRegionFavoritePublicationDateToHikingRouteAction;
use App\Nova\Actions\ImportPois;
use App\Nova\Actions\OverpassMap;
use App\Nova\Actions\PercorsoFavoritoAction;
use App\Nova\Filters\CaiHutsHRFilter;
use Suenerds\NovaSearchableBelongsToFilter\NovaSearchableBelongsToFilter;

class HikingRoute extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\HikingRoute::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */

    public function title()
    {
        $supplementaryString = ' - ';

        if ($this->name) {
            $supplementaryString .= $this->name;
        }

        if ($this->ref)
            $supplementaryString .= 'ref: ' . $this->ref;

        if ($this->sectors->count()) {
            $supplementaryString .= " (" . $this->sectors->pluck('name')->implode(', ') . ")";
        }

        return $this->id . $supplementaryString;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static array $search = [
        'ref_REI', 'relation_id', 'ref', 'ref_REI_comp'
    ];

    public static string $group = 'Territorio';
    public static $priority = 6;

    public static function label()
    {
        $label = 'Percorsi escursionistici';

        if (Auth::user()->getTerritorialRole() == 'regional') {
            $label .= ' - ' . Auth::user()->region->name;
        }
        return $label . ' (SDA*)';
    }

    public static function indexQuery(NovaRequest $request, $query)
    {

        if (Auth::user()->getTerritorialRole() == 'regional') {
            return $query->whereHas('regions', function ($q) {
                $q->where('regions.id', Auth::user()->region->id);
            });
        }
    }

    /**
     * Apply any applicable query filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function relatableQuery(NovaRequest $request, $query)
    {
        return $query->take(100); // Limit the number of hiking routes

    }

    public static $perPageViaRelationship = 50;

    /**
     * Get the fields displayed by the resource.
     * SUGGESTION: use tabs https://novapackages.com/packages/dkulyk/nova-tabs
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {

        $fields = [
            Text::make('Regioni', function () {
                $val = "ND";
                if (Arr::accessible($this->regions)) {
                    if (count($this->regions) > 0) {
                        $val = implode(', ', $this->regions->pluck('name')->toArray());
                    }
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('Province', function () {
                $val = "ND";
                if (Arr::accessible($this->provinces)) {
                    if (count($this->provinces) > 0) {
                        $val = implode(', ', $this->provinces->pluck('name')->toArray());
                    }
                    if (count($this->provinces) >= 2) {
                        $val = implode(', ', $this->provinces->pluck('name')->take(1)->toArray()) . ' [...]';
                    }
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('Aree', function () {
                $val = "ND";
                if (Arr::accessible($this->areas)) {
                    if (count($this->areas) > 0) {
                        $val = implode(', ', $this->areas->pluck('name')->toArray());
                    }
                    if (count($this->areas) >= 2) {
                        $val = implode(', ', $this->areas->pluck('name')->take(1)->toArray()) . ' [...]';
                    }
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('Settori', function () {
                $val = "ND";
                if (Arr::accessible($this->sectors)) {
                    if (count($this->sectors) > 0) {
                        $val = implode(', ', $this->sectors->pluck('name')->toArray());
                    }
                    if (count($this->sectors) >= 2) {
                        $val = implode(', ', $this->areas->pluck('name')->take(1)->toArray()) . ' [...]';
                    }
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('REF', 'ref')->onlyOnIndex()->sortable(),
            Text::make('COD_REI_OSM', 'ref_REI_osm')->onlyOnIndex()->sortable(),
            Text::make('COD_REI_COMP', 'ref_REI_comp')->onlyOnIndex()->sortable(),
            Text::make('Percorribilità', 'issues_status')->sortable()
                ->hideWhenUpdating(),
            Text::make('Ultima ricognizione', 'survey_date')->onlyOnIndex()->sortable(),
            Number::make('STATO', 'osm2cai_status')->sortable()->onlyOnIndex(),
            // Number::make('OSMID', 'relation_id')->onlyOnIndex(),
            Text::make('Legenda', function () {
                return "<ul><li>Linea blu: percorso OSM2CAI/OSM</li><li>Linea rossa: percorso caricato dall'utente</li></ul>";
            })->asHtml()->onlyOnDetail(),
            (new Tabs('Metadata', [
                'Main' => $this->getMetaFields('main'),
                'General' => $this->getMetaFields('general'),
                'Tech' => $this->getMetaFields('tech'),
                'Other' => $this->getMetaFields('other'),
                'Content' => $this->getEditorialContent(),
                'Issues' => $this->getIssuesContent(),
                'POI' => $this->getPoiContent(),
                'Huts' => $this->getHutsContent(),
            ])),
        ];
        //handle the case when centroid is null (giving error to nova "[2023-07-13 15:05:05] local.ERROR: Trying to access array offset on value of type null {"userId":1,"exception":"[object] (ErrorException(code: 0): Trying to access array offset on value of type null at /Users/gennaromanzo/Webmapp/osm2cai/app/Nova/HikingRoute.php:174)")
        $centroids = $this->getCentroid();
        if (!is_null($centroids) && !empty($centroids)) {
            $fields[] = MapMultiLinestringNova::make('Mappa')->withMeta([
                'center' => [$this->getCentroid()[1], $this->getCentroid()[0]],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'defaultZoom' => 10,
                'geojson' => json_encode($this->getGeojsonForMapView())
            ])->hideFromIndex()
                ->hideWhenUpdating();
        }
        $loggedInUser = auth()->user();
        $role = $loggedInUser->getTerritorialRole();
        // if (in_array($role, ['admin', 'national', 'regional'])) {
        //     $fields[] = Boolean::make('Eliminato su osm', 'deleted_on_osm')->onlyOnIndex()->sortable();
        // }

        $fields[] = Boolean::make('Correttezza Geometria', 'geometry_check')
            ->onlyOnDetail();

        $fields[] = Boolean::make('Coerenza ref REI', function () {
            return $this->ref_REI == $this->ref_REI_comp;
        })->onlyOnDetail()
            ->trueValue('ref_REI uguale a ref_REI_comp')
            ->falseValue('ref_REI diverso da ref_REI_comp');

        $fields[] = Boolean::make('Geometry Sync', function () {
            return $this->geometry_sync;
        })->onlyOnDetail()
            ->trueValue('geometry uguale a geometry_osm')
            ->falseValue('geometry div erso a geometry_osm');

        return $fields;
    }

    private function getMetaFields($group): array
    {
        if (!in_array($group, ['main', 'general', 'tech', 'other'])) {
            return [];
        }
        $fields = [];
        $sections = $this->sections()->get();
        $sectionCaiCode = '';
        foreach ($sections as $section) {
            $sectionCaiCode .= "<a style='color:green; text-decoration:none;' href='/resources/sections/{$section->id}'>{$section->cai_code}</a>" . '<br>';
        }

        foreach (\App\Models\HikingRoute::getInfoFields()[$group] as $field => $field_data) {
            $fields[] = Text::make($field_data['label'], function () use ($field, $field_data, $sectionCaiCode) {
                $field_osm = $field . '_osm';
                if ($field_data['label'] == 'Codice Sezione CAI') {
                    return "<p>INFOMONT: {$this->$field}</p><p>OSM: {$this->$field_osm}</p><p>CODICE SEZIONE: {$sectionCaiCode}</p>";
                }

                if ($field_data['comp']) {
                    $field_comp = $field . '_comp';
                    return "<p>INFOMONT: {$this->$field}</p><p>OSM: {$this->$field_osm}</p><p>VALORE CALCOLATO: {$this->$field_comp}</p>";
                } else {
                    return "<p>INFOMONT: {$this->$field}</p><p>OSM: {$this->$field_osm}</p>";
                }
            })->onlyOnDetail()->asHtml();
        }
        return $fields;
    }

    /**
     * It returns the Editorial Content Fields (only in details)
     *
     * @return array
     */
    public function getEditorialContent(): array
    {
        $fields = [];

        // Automatic Name For TDH
        $fields[] = Text::make('Automatic Name (computed for TDH)', function () {
            return $this->getNameForTDH()['it'];
        })->onlyOnDetail();

        // Automatic Abstract For TDH
        $fields[] = Textarea::make('Automatic Abstract (computed for TDH)', function () {
            if (!empty($this->tdh) && !empty($this->tdh['abstract'])) {
                return $this->tdh['abstract']['it'];
            } else {
                return 'Abstract ancora non calcolato';
            }
        })->onlyOnDetail()->alwaysShow();

        // Feature Image
        $fields[] = Text::make('Feature Image', function () {
            if (empty($this->feature_image)) {
                return 'No Feature Image Uploaded';
            }
            return '<img src="' . Storage::url($this->feature_image) . '"/>';
        })->onlyOnDetail()->asHtml();

        //Description CAI IT
        $fields[] = Textarea::make('Description CAI IT', 'description_cai_it')
            ->onlyOnDetail()
            ->alwaysShow();

        //Region Favorite
        // $fields[] = Boolean::make('Region Favorite', 'region_favorite');

        //Data pubblicazione LoScarpone
        // $fields[] = Date::make('Data publicazione LoScarpone', 'region_favorite_publication_date');


        return $fields;
    }

    /**
     * Returns the issues fields for the resource (only in details)
     * 
     * @return array
     */
    public function getIssuesContent(): array
    {
        $fields = [
            Text::make('Issue Status', function () {
                return $this->issues_status;
            })->hideFromIndex(),
            Text::make('Issue Description', function () {
                return $this->issues_description;
            })->hideFromIndex(),
            Date::make('Issue Date', function () {
                return $this->issues_last_update;
            })->hideFromIndex(),
            Text::make('Issue Author', function () {
                $user = User::find($this->issues_user_id);
                if (empty($user)) {
                    return 'ND';
                }
                return $user->name;
            })->hideFromIndex(),
            Text::make('Cronologia Percorribilitá', function () {
                return "<a style='text-decoration: none; color: #2697bc; font-weight: bold; ' href='/hiking-route/{$this->id}/issues'>Visualizza</a>";
            })->asHtml()->onlyOnDetail()

        ];

        return $fields;
    }

    public function getPoiContent(): array
    {
        $routeGeometry = $this->geometry;
        $pois = \App\Models\EcPoi::whereRaw(
            "ST_DWithin(geometry, ST_GeomFromEWKB(?::geometry), 1000)",
            [$routeGeometry]
        )->get();
        //for test
        // $pois = EcPoi::where('osm_type', '!=', '')->get();
        $fields[] = Text::make('', function () use ($pois) {
            if (count($pois) < 1) {
                return '<h2>Nessun POI trovato in un buffer di 1km</h2>';
            }
            return '<h2>Punti di interesse (buffer 1km)</h2>';
        })->asHtml()->onlyOnDetail();

        if (count($pois) > 0) {
            $tableRows = [];
            foreach ($pois as $poi) {
                $tags = json_decode($poi->tags, true);
                $tagList = '';
                if ($tags) {
                    $tagList = '<ul>';
                    foreach ($tags as $key => $value) {
                        $tagList .= "<li>{$key}: {$value}</li>";
                    }
                    $tagList .= '</ul>';
                }

                $tableRows[] = "<tr style='border:1px solid grey;'>
            <td style='border: 1px solid grey;'><a style='text-decoration: none; color: #2697bc; font-weight: bold;' href='/resources/pois/{$poi->id}'>{$poi->name}</a></td>
            <td style='border: 1px solid grey;'>{$poi->osm_id}</td>
            <td style='border: 1px solid grey;'>{$tagList}</td>
            <td style='text-align:center;'>{$poi->osm_type}</td>
        </tr>";
            }

            $fields[] = Text::make('Risultati', function () use ($tableRows) {
                return "<table>
            <thead style='margin-bottom: 10px;'>
                <tr>
                    <th>Name</th>
                    <th>OSM ID</th>
                    <th>Tag OSM</th>
                    <th>Type OSM</th>
                </tr>
            </thead>
            <tbody>" . implode('', $tableRows) . "</tbody>
        </table>";
            })->asHtml()->onlyOnDetail();
        }
        return $fields;
    }
    public function getHutsContent()
    {
        $hikingRouteId = $this->model()->getKey();

        $hr = \App\Models\HikingRoute::find($hikingRouteId);

        if (!$hr) {
            return [];
        }
        $hutsId = $hr->cai_huts ? json_decode($hr->cai_huts) : [];

        if (empty($hutsId)) {
            $fields = [
                Text::make('', function () {
                    return '<h2>Nessun rifugio nelle vicinanze</h2>';
                })->asHtml()->onlyOnDetail()
            ];
        }

        $fields = [
            Text::make('', function () {
                return '<h2>Rifugi nelle vicinanze</h2>';
            })->asHtml()->onlyOnDetail()
        ];

        $tableRows = [];
        foreach ($hutsId as $hutId) {
            $hut = \App\Models\CaiHuts::find($hutId);
            if ($hut) {
                $tableRows[] = "<tr style='margin-top:10px;'>
            <td><a style='text-decoration: none; color: #2697bc; font-weight: bold;' href='/resources/cai-huts/{$hut->id}'>{$hut->name}</a></td>
        </tr>";
            }
        }

        $fields[] = Text::make('Risultati', function () use ($tableRows) {
            return "<table>
            <thead style='margin-bottom: 10px;'>
                <tr>
                    <th>Nome</th>
                </tr>
            </thead>
            <tbody>" . implode('', $tableRows) . "</tbody>
        </table>";
        })->asHtml()->onlyOnDetail();

        return $fields;
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        $links = $this->getLinks($request);
        $infomontLink = $links['infomontLink'];
        $osm2caiLink = $links['osm2caiLink'];

        $hr = \App\Models\HikingRoute::find($request->resourceId);

        if (!is_null($hr)) {

            $statoDiAccatastamento = 'Stato di accatastamento';

            if ($hr->validation_date)
                $statoDiAccatastamento .= "<h5 class=\"font-light\">Data di validazione: {$hr->validation_date->format('d/m/Y')}</h5>";

            if ($hr->validator)
                $statoDiAccatastamento .= "<h5 class=\"font-light\">Validatore: {$hr->validator->name} ({$hr->validator->email})</h5>";

            $osm = "https://www.openstreetmap.org/relation/" . $hr->relation_id;
            $wmt = "https://hiking.waymarkedtrails.org/#route?id= " . $hr->relation_id;
            $analyzer = "https://ra.osmsurround.org/analyzeRelation?relationId=" . $hr->relation_id . "&noCache=true&_noCache=on";
            return [
                (new TextCard())
                    ->center(false)
                    ->onlyOnDetail()
                    ->width('1/2')
                    ->heading('REF:' . $hr->ref . ' (CODICE REI: ' . $hr->ref_REI . ' / ' . $hr->ref_REI_comp . ')')
                    ->text('Settori: ' . $hr->getSectorsString()),

                (new TextCard())
                    ->center(false)
                    ->onlyOnDetail()
                    ->width('1/4')
                    ->text(
                        '<p>OpenStreetMap: <a target="_blank" href="' . $osm . '">' . $hr->relation_id . '</a></p>' .
                            '<p>Waymarkedtrails: <a target="_blank" href="' . $wmt . '">' . $hr->relation_id . '</a></p>' .
                            '<p>OSM Relation Analyzer: <a target="_blank" href="' . $analyzer . '">' . $hr->relation_id . '</a></p>' .
                            '<p>OSM2CAI: <a target="_blank" href="' . $osm2caiLink . '">' . $hr->id . '</a></p>' .
                            '<p>INFOMONT: <a target="_blank" href="' . $infomontLink . '">' . $hr->id . '</a></p>'
                    )

                    ->textAsHtml(),

                (new TextCard())
                    ->center(false)
                    ->onlyOnDetail()
                    ->width('1/4')
                    ->heading($hr->osm2cai_status)
                    ->text($statoDiAccatastamento)
                    ->textAsHtml(),
            ];
        }

        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        if (Auth::user()->getTerritorialRole() == 'regional') {
            return [
                (new HikingRoutesProvinceFilter()),
                (new HikingRoutesAreaFilter()),
                (new HikingRoutesSectorFilter()),
                (new GeometrySyncFilter()),
                (new DeleteOnOsmFilter()),
                (new RegionFavoriteHikingRouteFilter()),
            ];
        } else {
            return [
                (new HikingRoutesRegionFilter()),
                (new HikingRoutesProvinceFilter()),
                (new HikingRoutesAreaFilter()),
                (new HikingRoutesSectorFilter()),
                (new GeometrySyncFilter()),
                (new DeleteOnOsmFilter()),
                (new RegionFavoriteHikingRouteFilter()),
                (new IssueStatusFilter()),
                (new HrCorrectGeometryFilter()),
                (new CaiHutsHRFilter()),
            ];
        }
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [
            (new HikingRoutesStatus0Lens()),
            (new HikingRoutesStatus1Lens()),
            (new HikingRoutesStatus2Lens()),
            (new HikingRoutesStatus3Lens()),
            (new HikingRoutesStatus4Lens()),
        ];
    }

    public function authorizedToDelete(Request $request)
    {
        return $request instanceof ActionRequest;
    }

    public function authorizedToForceDelete(Request $request)
    {
        return $request instanceof ActionRequest;
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new UploadValidationRawDataAction)
                ->confirmButtonText('Carica')
                ->cancelButtonText("Non caricare")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                ),
            (new ValidateHikingRouteAction)
                ->confirmText('Sei sicuro di voler validare questo percorso?' . 'REF:' . $this->ref . ' (CODICE REI: ' . $this->ref_REI . ' / ' . $this->ref_REI_comp . ')')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Non validare")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                ),
            (new OsmSyncHikingRouteAction)
                ->confirmText('Sei sicuro di voler sincronizzare i dati osm?')
                ->confirmButtonText('Aggiorna con dati osm')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                ),
            (new RevertValidateHikingRouteAction)
                ->confirmText('Sei sicuro di voler revertare la validazione di questo percorso?' . 'REF:' . $this->ref . ' (CODICE REI: ' . $this->ref_REI . ' / ' . $this->ref_REI_comp . ')')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                ),
            (new DeleteHikingRouteAction())
                ->confirmText('Sei sicuro di voler eliminare il percorso?' . 'REF:' . $this->ref . ' (CODICE REI: ' . $this->ref_REI . ' / ' . $this->ref_REI_comp . ')')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                ),
            (new SectorRefactoring())
                ->onlyOnDetail('true')
                ->confirmText('Sei sicuro di voler rifattorizzare i settori per il percorso?' . 'REF:' . $this->ref . ' (CODICE REI: ' . $this->ref_REI . ' / ' . $this->ref_REI_comp . ')')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                ),
            // (new ToggleRegionFavoriteHikingRouteAction())
            //     ->onlyOnDetail('true')
            //     ->confirmText($this->region_favorite ? 'Sei sicuro di voler togliere il percorso dai favoriti della Regione?' : 'Sei sicuro di voler aggiungere il percorso ai favoriti della Regione?')
            //     ->confirmButtonText('Confermo')
            //     ->cancelButtonText("Annulla")
            //     ->canSee(function ($request) {
            //         return true;
            //     })
            //     ->canRun(
            //         function ($request, $user) {
            //             return true;
            //         }
            //     ),
            // (new AddFeatureImageToHikingRoute())
            //     ->onlyOnDetail('true')
            //     ->confirmText('Sei sicuro di voler caricare una nuova immagine in evidenza e sostituire quella esistente?')
            //     ->confirmButtonText('Confermo')
            //     ->cancelButtonText("Annulla")
            //     ->canSee(function ($request) {
            //         return true;
            //     })
            //     ->canRun(
            //         function ($request, $user) {
            //             return true;
            //         }
            //     ),
            (new PercorsoFavoritoAction())
                ->onlyOnDetail('true')
                ->confirmText('Sei sicuro di voler aggiornare il percorso?')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                ),
            (new AddRegionFavoritePublicationDateToHikingRouteAction())
                ->onlyOnDetail('true')
                ->confirmText('Imposta la data prevista per la publicazione sullo Scarpone Online')
                ->confirmButtonText('Confermo')
                ->cancelButtonText('Annulla')
                ->canSee(function ($request) {
                    $u = auth()->user();
                    return $u->is_administrator || $u->is_national_referent;
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                ),
            (new CreateIssue($this->model()))
                ->confirmText('Sei sicuro di voler creare un issue per questo percorso?')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    $u = auth()->user();
                    //can only see if the getTerritorialRole is not unknown
                    return $u->getTerritorialRole() != 'unknown';
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                )
                ->showOnTableRow(),
            (new OverpassMap($this->model()))
                ->onlyOnDetail('true')
                ->confirmText('Sei sicuro di voler creare una mappa Overpass per questo percorso?')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    $u = auth()->user();
                    //can only see if admin, itinerary manager or national referent
                    return $u->is_administrator || $u->is_national_referent || $u->is_itinerary_manager;
                }),
            (new ImportPois($this->model()))
                ->onlyOnDetail('true')
                ->confirmText('Sei sicuro di voler importare i POI per questo percorso?')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    $u = auth()->user();
                    //can only see if admin, itinerary manager or national referent
                    return $u->is_administrator || $u->is_national_referent || $u->is_itinerary_manager;
                }),



        ];
    }

    private function getLinks($request)
    {
        $infomontLink = 'https://15.app.geohub.webmapp.it/#/map';
        $osm2caiLink = 'https://26.app.geohub.webmapp.it/#/map';
        $endpoint = 'https://geohub.webmapp.it/api/osf/track/osm2cai/';
        $api = $endpoint . $request->resourceId;

        $headers = get_headers($api);
        $statusLine = $headers[0];

        if (strpos($statusLine, '200 OK') !== false) {
            // The API returned a success response
            $data = json_decode(file_get_contents($api), true);
            if (!empty($data)) {
                if ($data['properties']['id'] !== null) {
                    $infomontLink .= '?track=' . $data['properties']['id'];
                    $osm2caiLink .= '?track=' . $data['properties']['id'];
                }
            }
        }

        return [
            'infomontLink' => $infomontLink,
            'osm2caiLink' => $osm2caiLink,
        ];
    }
}
