<?php

namespace App\Nova;

use App\Nova\Filters\HikingRoutesAreaFilter;
use App\Nova\Filters\HikingRoutesProvinceFilter;
use App\Nova\Filters\HikingRoutesRegionFilter;
use App\Nova\Filters\HikingRoutesSectorFilter;
use App\Nova\Filters\HikingRouteStatus;
use App\Nova\Filters\HikingRoutesTerritorialFilter;
use App\Nova\Lenses\HikingRoutesStatus0Lens;
use App\Nova\Lenses\HikingRoutesStatus1Lens;
use App\Nova\Lenses\HikingRoutesStatus2Lens;
use App\Nova\Lenses\HikingRoutesStatus3Lens;
use App\Nova\Lenses\HikingRoutesStatus4Lens;
use App\Nova\Lenses\HikingRoutesStatusLens;
use DKulyk\Nova\Tabs;
use App\Nova\Actions\ValidateHikingRouteAction;
use Ericlagarda\NovaTextCard\TextCard;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Suenerds\NovaSearchableBelongsToFilter\NovaSearchableBelongsToFilter;
use Imumz\LeafletMap\LeafletMap;


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
    public static string $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static array $search = [
        'ref_REI', 'relation_id', 'ref'
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
        if (Auth::user()->getTerritorialRole() == 'regional') {
            $value = Auth::user()->region->id;
            return $query->whereHas('regions', function ($query) use ($value) {
                $query->where('region_id', $value);
            });
        }
        return parent::indexQuery($request, $query);
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

        return [
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
            Text::make('REF', 'ref')->onlyOnIndex(),
            Text::make('Cod. REI', 'ref_REI')->onlyOnIndex(),
            Text::make('Ultima ricognizione', 'survey_date')->onlyOnIndex(),
            Number::make('STATO', 'osm2cai_status')->sortable()->onlyOnIndex(),
            Number::make('OSMID', 'relation_id')->onlyOnIndex(),

            LeafletMap::make('Mappa')
                ->type('GeoJson')
                ->geoJson(json_encode($this->getEmptyGeojson()))
                ->center($this->getCentroid()[1], $this->getCentroid()[0])
                ->zoom(12)
                ->hideFromIndex(),

            (new Tabs('Metadata', [
                'Main' => $this->getMetaFields('main'),
                'General' => $this->getMetaFields('general'),
                'Tech' => $this->getMetaFields('tech'),
                'Other' => $this->getMetaFields('other'),
            ]))->withToolbar(),
        ];
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
                    return sprintf('%s (%s / %s)', $this->$field, $this->$field_osm, $this->$field_comp);
                } else {
                    return sprintf('%s (%s)', $this->$field, $this->$field_osm);
                }
            })->onlyOnDetail();
        }
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
            $osm = "https://www.openstreetmap.org/relation/" . $hr->relation_id;
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
                    ->heading('<a target="_blank" href="' . $osm . '">' . $hr->relation_id . '</a>')
                    ->text('OSMID')
                    ->headingAsHtml(),

                (new TextCard())
                    ->center(false)
                    ->onlyOnDetail()
                    ->width('1/4')
                    ->heading($hr->osm2cai_status)
                    ->text('Stato di accatastamento'),
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
            ];

        } else {
            return [
                (new HikingRoutesRegionFilter()),
                (new HikingRoutesProvinceFilter()),
                (new HikingRoutesAreaFilter()),
                (new HikingRoutesSectorFilter()),
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

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        if ( $this->osm2cai_status == 3) {
            return [
                (new ValidateHikingRouteAction())
                    ->confirmText('Inserire il GPX del percorso per confrontarlo con quello esistente.')
                    ->confirmButtonText('Validare')
                    ->cancelButtonText("Non validare"),
            ];
        } else {
            return [];
        }
    }
}
