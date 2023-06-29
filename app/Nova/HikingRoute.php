<?php

namespace App\Nova;

use App\Models\User;
use DKulyk\Nova\Tabs;
use Laravel\Nova\Panel;
use Illuminate\Support\Arr;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
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
use Illuminate\Support\Facades\Storage;
use App\Nova\Filters\GeometrySyncFilter;
use AddRegionFavoriteToHikingRoutesTable;
use App\Nova\Lenses\HikingRoutesStatusLens;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Filters\HikingRoutesAreaFilter;
use App\Nova\Lenses\HikingRoutesStatus0Lens;
use App\Nova\Lenses\HikingRoutesStatus1Lens;
use App\Nova\Lenses\HikingRoutesStatus2Lens;
use App\Nova\Lenses\HikingRoutesStatus3Lens;
use App\Nova\Lenses\HikingRoutesStatus4Lens;
use App\Nova\Actions\DeleteHikingRouteAction;
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
    //public static string $title = 'id';
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
    public static $priority = 5;

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
        $loggedInUser = Auth::user();
        $role = $loggedInUser->getTerritorialRole();
        // if ( $role != "admin")
        // {
        //     $query->ownedBy($loggedInUser);
        // }

        return $query;
    }

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
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('Aree', function () {
                $val = "ND";
                if (Arr::accessible($this->areas)) {
                    if (count($this->areas) > 0) {
                        $val = implode(', ', $this->areas->pluck('name')->toArray());
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
                }
                return $val;
            })->onlyOnIndex(),
            Text::make('REF', 'ref')->onlyOnIndex()->sortable(),
            Text::make('COD_REI_OSM', 'ref_REI_osm')->onlyOnIndex()->sortable(),
            Text::make('COD_REI_COMP', 'ref_REI_comp')->onlyOnIndex()->sortable(),
            Text::make('Ultima ricognizione', 'survey_date')->onlyOnIndex(),
            Number::make('STATO', 'osm2cai_status')->sortable()->onlyOnIndex(),
            Number::make('OSMID', 'relation_id')->onlyOnIndex(),

            MapMultiLinestringNova::make('Mappa')->withMeta([
                'center' => [$this->getCentroid()[1], $this->getCentroid()[0]],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'defaultZoom' => 10,
                'geojson' => json_encode($this->getGeojsonForMapView())
            ])->hideFromIndex(),

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
            ])),
        ];

        $loggedInUser = auth()->user();
        $role = $loggedInUser->getTerritorialRole();
        if (in_array($role, ['admin', 'national', 'regional'])) {
            $fields[] = Boolean::make('Eliminato su osm', 'deleted_on_osm')->onlyOnIndex()->sortable();
        }
        $fields[] = Boolean::make('Correttezza geometria', 'geometry_check')
            ->hideWhenCreating()
            ->hideWhenUpdating()
            ->sortable();

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

        $fields[] = Boolean::make('Region Favorite', 'region_favorite');
        $fields[] = Date::make('Data publicazione LoScarpone', 'region_favorite_publication_date');

        return $fields;
    }

    private function getMetaFields($group): array
    {
        if (!in_array($group, ['main', 'general', 'tech', 'other'])) {
            return [];
        }
        $fields = [];
        foreach (\App\Models\HikingRoute::getInfoFields()[$group] as $field => $field_data) {
            $fields[] = Text::make($field_data['label'], function () use ($field, $field_data) {

                $field_osm = $field . '_osm';
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
        ];

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
                        '<p>Osmid: <a target="_blank" href="' . $osm . '">' . $hr->relation_id . '</a></p>' .
                            '<p>WMT: <a target="_blank" href="' . $wmt . '">' . $hr->relation_id . '</a></p>' .
                            '<p>Analyzer: <a target="_blank" href="' . $analyzer . '">' . $hr->relation_id . '</a></p>'
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
                ->confirmText('Inserire il GPX del percorso per confrontarlo con quello esistente.')
                ->confirmButtonText('Carica')
                ->cancelButtonText("Non caricare")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(function ($request, $user) {
                    return true;
                }),
            (new ValidateHikingRouteAction)
                ->confirmText('Sei sicuro di voler validare questo percorso?' . 'REF:' . $this->ref . ' (CODICE REI: ' . $this->ref_REI . ' / ' . $this->ref_REI_comp . ')')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Non validare")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(function ($request, $user) {
                    return true;
                }),
            (new OsmSyncHikingRouteAction)
                ->confirmText('Sei sicuro di voler sincronizzare i dati osm?')
                ->confirmButtonText('Aggiorna con dati osm')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(function ($request, $user) {
                    return true;
                }),
            (new RevertValidateHikingRouteAction)
                ->confirmText('Sei sicuro di voler revertare la validazione di questo percorso?' . 'REF:' . $this->ref . ' (CODICE REI: ' . $this->ref_REI . ' / ' . $this->ref_REI_comp . ')')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(function ($request, $user) {
                    return true;
                }),
            (new DeleteHikingRouteAction())
                ->confirmText('Sei sicuro di voler eliminare il percorso?' . 'REF:' . $this->ref . ' (CODICE REI: ' . $this->ref_REI . ' / ' . $this->ref_REI_comp . ')')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    return true;
                })
                ->canRun(function ($request, $user) {
                    return true;
                }),
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
            (new ToggleRegionFavoriteHikingRouteAction())
                ->onlyOnDetail('true')
                ->confirmText($this->region_favorite ? 'Sei sicuro di voler togliere il percorso dai favoriti della Regione?' : 'Sei sicuro di voler aggiungere il percorso ai favoriti della Regione?')
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
            (new AddFeatureImageToHikingRoute())
                ->onlyOnDetail('true')
                ->confirmText('Sei sicuro di voler caricare una nuova immagine in evidenza e sostituire quella esistente?')
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
            (new CreateIssue())
                ->confirmText('Sei sicuro di voler creare un issue per questo percorso?')
                ->confirmButtonText('Confermo')
                ->cancelButtonText("Annulla")
                ->canSee(function ($request) {
                    $u = auth()->user();
                    return $u->is_administrator || $u->is_national_referent;
                })
                ->canRun(
                    function ($request, $user) {
                        return true;
                    }
                ),

        ];
    }
}
