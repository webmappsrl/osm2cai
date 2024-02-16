<?php

namespace App\Models;

use App\Services\GeometryService;
use App\Traits\GeojsonableTrait;
use App\Traits\OwnableModelTrait;
use GeoJson\Geometry\Polygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Imumz\LeafletMap\LeafletMap;
use phpDocumentor\Reflection\Types\Boolean;
use Symm\Gisconverter\Exceptions\InvalidText;
use Symm\Gisconverter\Gisconverter;

/**
 * Class HikingRoute
 *
 * @package App\Models
 * @property int id
 * @property float distance_comp
 * @property geometry geometry
 * @property geometry geometry_osm
 */
class HikingRoute extends Model
{
    use HasFactory, GeojsonableTrait, OwnableModelTrait;

    public $is_syncing = false;


    protected $fillable = [
        'relation_id',
        'ref_osm', 'old_ref_osm', 'source_osm', 'source_ref_osm', 'survey_date_osm', 'name_osm', 'rwn_osm', 'rwn_name', 'ref_REI_osm',
        'tags_osm', 'geometry_osm',
        'cai_scale_osm', 'from_osm', 'to_osm', 'osmc_symbol_osm', 'network_osm', 'roundtrip_osm', 'symbol_osm', 'symbol_it_osm',
        'ascent_osm', 'descent_osm', 'distance_osm', 'duration_forward_osm', 'duration_backward_comp',
        'operator_osm', 'state_osm', 'description_osm', 'description_it_osm', 'website_osm', 'wikimedia_commons_osm', 'maintenance_osm', 'maintenance_it_osm', 'note_osm', 'note_it_osm', 'note_project_page_osm', 'geometry_raw_data', 'osm2cai_status', 'reg_ref_osm', 'reg_ref',
        'natural_springs', 'cai_huts', 'has_natural_springs', 'has_cai_huts'
    ];

    protected $casts = [
        'distance' => 'float',
        'distance_osm' => 'float',
        'distance_comp' => 'float',
        'validation_date' => 'datetime:Y-m-d H:i:s',
        'tdh' => 'array',
        'region_favorite_publication_date' => 'date:Y-m-d'
    ];

    public static array $info_fields = [
        'main' => [
            'source' => ['type' => 'string', 'comp' => false, 'label' => 'Source'],
            'survey_date' => ['type' => 'string', 'comp' => false, 'label' => 'Data ricognizione'],
            'source_ref' => ['type' => 'string', 'comp' => false, 'label' => 'Codice Sezione CAI'],
            'old_ref' => ['type' => 'string', 'comp' => false, 'label' => 'REF precedente'],
            'ref_REI' => ['type' => 'string', 'comp' => false, 'label' => 'REF rei'],
            'reg_ref' => ['type' => 'string', 'comp' => false, 'label' => 'REF regionale'],
        ],
        'general' => [
            'from' => ['type' => 'string', 'comp' => false, 'label' => 'Località di partenza'],
            'to' => ['type' => 'string', 'comp' => false, 'label' => 'Località di arrivo'],
            'name' => ['type' => 'string', 'comp' => false, 'label' => 'Nome del percorso'],
            'network' => ['type' => 'string', 'comp' => false, 'label' => 'Tipo di rete escursionistica'],
            'osmc_symbol' => ['type' => 'string', 'comp' => false, 'label' => 'Codice OSM segnalietica'],
            'symbol' => ['type' => 'string', 'comp' => false, 'label' => 'Segnaletica descr. (EN)'],
            'symbol_it' => ['type' => 'string', 'comp' => false, 'label' => 'Segnaletica descr. (IT)'],
            'roundtrip' => ['type' => 'string', 'comp' => false, 'label' => 'Percorso ad anello'],
            'rwn_name' => ['type' => 'string', 'comp' => false, 'label' => 'Nome rete escursionistica'],
        ],
        'tech' => [
            'distance' => ['type' => 'float', 'comp' => true, 'label' => 'Lunghezza in Km'],
            'cai_scale' => ['type' => 'string', 'comp' => false, 'label' => 'Diff. CAI'],
            'ascent' => ['type' => 'float', 'comp' => true, 'label' => 'Dislivello positivo in metri'],
            'descent' => ['type' => 'float', 'comp' => true, 'label' => 'Dislivello negativo in metri'],
            'duration_forward' => ['type' => 'string', 'comp' => true, 'label' => 'Durata (P->A)'],
            'duration_backward' => ['type' => 'string', 'comp' => true, 'label' => 'Durata (A->P)'],
            'ele_max' => ['type' => 'float', 'comp' => true, 'label' => 'Quota massima'],
            'ele_min' => ['type' => 'float', 'comp' => true, 'label' => 'Quota minima'],
            'ele_from' => ['type' => 'float', 'comp' => true, 'label' => 'Quota punto di partenza'],
            'ele_to' => ['type' => 'float', 'comp' => true, 'label' => 'Quota punto di arrivo'],
        ],
        'other' => [
            'description' => ['type' => 'string', 'comp' => false, 'label' => 'Descrizione (EN)'],
            'description_it' => ['type' => 'string', 'comp' => false, 'label' => 'Descrizione (IT)'],
            'maintenance' => ['type' => 'string', 'comp' => false, 'label' => 'Manutenzione (EN)'],
            'maintenance_it' => ['type' => 'string', 'comp' => false, 'label' => 'Manutenzione (IT)'],
            'note' => ['type' => 'string', 'comp' => false, 'label' => 'Note (IT)'],
            'note_it' => ['type' => 'string', 'comp' => false, 'label' => 'Note (EN)'],
            'note_project_page' => ['type' => 'string', 'comp' => false, 'label' => 'Note di progetto'],
            'operator' => ['type' => 'string', 'comp' => false, 'label' => 'Operator'],
            'state' => ['type' => 'string', 'comp' => false, 'label' => 'Stato del percorso'],
            'website' => ['type' => 'string', 'comp' => false, 'label' => 'Indirizzo web'],
            'wikimedia_commons' => ['type' => 'string', 'comp' => false, 'label' => 'Immagine su wikimedia'],
        ],
    ];

    protected static function booted()
    {
        static::saved(function ($hikingRoute) {
            if ($hikingRoute->is_syncing) {
                $hikingRoute->is_syncing = false;
                return;
            }
            Artisan::call('osm2cai:add_cai_huts_to_hiking_routes HikingRoute ' . $hikingRoute->id);
            Artisan::call('osm2cai:add_natural_springs_to_hiking_routes HikingRoute ' . $hikingRoute->id);
        });

        static::created(function ($hikingRoute) {
            if ($hikingRoute->is_syncing) {
                $hikingRoute->is_syncing = false;
                return;
            }
            Artisan::call('osm2cai:add_cai_huts_to_hiking_routes HikingRoute ' . $hikingRoute->id);
            Artisan::call('osm2cai:add_natural_springs_to_hiking_routes HikingRoute ' . $hikingRoute->id);
        });
    }

    public static function getInfoFields(): array
    {
        return self::$info_fields;
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function regions()
    {
        return $this->belongsToMany(Region::class);
    }

    public function provinces()
    {
        return $this->belongsToMany(Province::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class);
    }

    public function sectors()
    {
        return $this->belongsToMany(Sector::class)->withPivot(['percentage']);
    }

    public function issueUser()
    {
        return $this->belongsTo(User::class, 'id', 'issues_user_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'hiking_route_section');
    }

    public function itineraries()
    {
        return $this->belongsToMany(Itinerary::class);
    }

    public function mainSector()
    {
        $q = "SELECT sector_id from hiking_route_sector where hiking_route_id={$this->id} order by percentage desc limit 1;";
        $res = DB::select(DB::raw($q));
        if (count($res) > 0) {
            foreach ($res as $item) {
                $sector_id = $item->sector_id;
            }
            return Sector::find($sector_id);
        }
        return null;
    }

    /**
     * It returns a string with all hiking routes sectors full codes separated by ';'
     *
     * @return string
     */
    public function getSectorsString(): string
    {
        $s = 'ND';
        if (count($this->sectors) > 0) {
            $sectors = [];
            foreach ($this->sectors as $sector) {
                $sectors[] = $sector->full_code . '(' . number_format($sector->pivot->percentage * 100, 2) . '%)';
            }
            $s = implode('; ', $sectors);
        }
        return $s;
    }

    public function validated(): bool
    {
        if (!empty($this->validation_date)) {
            return true;
        }

        return false;
    }

    /*
     * 0: cai_scale null, source null
     * 1: cai_scale not null, source null
     * 2: cai_scale null, source contains "survey:CAI"
     * 3: cai_scale not null, source contains "survey:CAI"
     * 4: validation_date not_null
     */
    public function setOsm2CaiStatus(): void
    {
        $status = 0;
        if ($this->validated()) {
            $status = 4;
        } else if (!is_null($this->cai_scale_osm) && !preg_match('/survey:CAI/', $this->source_osm)) {
            $status = 1;
        } else if (is_null($this->cai_scale_osm) && preg_match('/survey:CAI/', $this->source_osm)) {
            $status = 2;
        } else if (!is_null($this->cai_scale_osm) && preg_match('/survey:CAI/', $this->source_osm)) {
            $status = 3;
        }
        $this->osm2cai_status = $status;
    }

    // TODO: riscrivere con sezioni varie
    public function copyFromOsm2Cai()
    {

        if ($this->osm2cai_status != 4) {
            // ID fields
            $this->ref = $this->ref_osm;
            $this->ref_REI = $this->ref_REI_osm;

            // Geometry

            $this->geometry = $this->geometry_osm;

            // Meta
            foreach (self::$info_fields as $group => $fields) {
                foreach ($fields as $field => $field_data) {
                    $field_osm = $field . '_osm';
                    if ($field_data['type'] != 'float') {
                        $this->$field = $this->$field_osm;
                    } else {
                        $this->$field = (float)preg_replace('/,/', '.', $this->$field_osm);
                    }
                }
            }
        }
    }

    /**
     * This method compute and set tech info (distance_comp, ascent_comp, descent_comp, duration_forward_comp,
     * duration_backward_comp) from geometry: geometry_cai if geometry_osm is not present, geometry_osm if it is
     * present. If HikingRoute ha no geometry nothing is done.
     */
    public function computeAndSetTechInfo(): void
    {
        if (is_null($this->geometry_osm) && is_null($this->geometry)) {
            return;
        } else {
            if (!is_null($this->geometry)) {
                // Compute from CAI geometry
                // Distance
                if ($this->id == 22289) {
                    $stop = 'here';
                }
                $this->distance_comp = round(DB::table('hiking_routes')
                    ->selectRaw('ST_length(geometry,true) as length')
                    ->find($this->id)->length / 1000.0, 2);
            } else {
                // Compute from OSM geometry
                // Distance
                $this->distance_comp = round(DB::table('hiking_routes')
                    ->selectRaw('ST_length(geometry_osm,true) as length')
                    ->find($this->id)->length / 1000.0, 2);
            }
        }
    }

    /**
     * Check if Hiking Route has geometry
     *
     * @return bool
     */
    public function hasGeometry(): bool
    {
        if (is_null($this->geometry) && is_null($this->geometry_osm)) {
            return false;
        }

        return true;
    }

    /**
     * Check if Hiking route has geometry, if not returns false, if true returns the name of the
     * "actual" geometry, that is geometry if present, geometry_osm if geometry is not still there.
     *
     * @return mixed
     */
    public function getActualGeometryField(): string
    {
        if (!$this->hasGeometry()) {
            return '';
        } elseif (!is_null($this->geometry)) {
            return 'geometry';
        }

        return 'geometry_osm';
    }

    /**
     * Compute and Associate Sectors to Hiking Route
     */
    public function computeAndSetSectors(): void
    {
        // If object is not persistent save it
        if (!$this->exists) {
            $this->save();
        }
        if (!$this->hasGeometry()) {
            return;
        }
        $query = 'SELECT s.id FROM sectors AS s,hiking_routes AS r WHERE ST_intersects(s.geometry,r.' . $this->getActualGeometryField() . ') AND r.id=' . $this->id;
        $sectors = DB::select(DB::raw($query));
        if (count($sectors) > 0) {
            $this->sectors()->sync(array_map(function ($item) {
                return $item->id;
            }, $sectors));
        }
        $this->save();
        // UPDATE %
        $q = <<<EOF
        UPDATE hiking_route_sector as hr set percentage=
        (SELECT ST_length(ST_Intersection((select geometry from hiking_routes where id=hr.hiking_route_id),(select geometry from sectors where id=hr.sector_id)),true)/(SELECT ST_length(geometry,true) as length from hiking_routes where id=hr.hiking_route_id))
        WHERE hr.hiking_route_id={$this->id}
        ;
EOF;
        DB::update(DB::raw($q));
    }

    /**
     * Compute and Associate Areas to Hiking Route
     */
    public function computeAndSetAreas(): void
    {
        // If object is not persistent save it
        if (!$this->exists) {
            $this->save();
        }
        if (!$this->hasGeometry()) {
            return;
        }
        $query = 'SELECT a.id FROM areas AS a,hiking_routes AS r WHERE ST_intersects(a.geometry,r.' . $this->getActualGeometryField() . ') AND r.id=' . $this->id;
        $areas = DB::select(DB::raw($query));
        if (count($areas) > 0) {
            $this->areas()->sync(array_map(function ($item) {
                return $item->id;
            }, $areas));
        }
    }

    /**
     * Compute and Associate Provinces to Hiking Route
     */
    public function computeAndSetProvinces(): void
    {
        // If object is not persistent save it
        if (!$this->exists) {
            $this->save();
        }
        if (!$this->hasGeometry()) {
            return;
        }
        $query = 'SELECT p.id FROM provinces AS p,hiking_routes AS r WHERE ST_intersects(p.geometry,r.' . $this->getActualGeometryField() . ') AND r.id=' . $this->id;
        $provinces = DB::select(DB::raw($query));
        if (count($provinces) > 0) {
            $this->provinces()->sync(array_map(function ($item) {
                return $item->id;
            }, $provinces));
        }
    }

    /**
     * Compute and Associate Provinces to Hiking Route
     */
    public function computeAndSetRegions(): void
    {
        // If object is not persistent save it
        if (!$this->exists) {
            $this->save();
        }
        if (!$this->hasGeometry()) {
            return;
        }
        $query = 'SELECT re.id FROM regions AS re,hiking_routes AS r WHERE ST_intersects(re.geometry,r.' . $this->getActualGeometryField() . ') AND r.id=' . $this->id;
        $regions = DB::select(DB::raw($query));
        if (count($regions) > 0) {
            $this->regions()->sync(array_map(function ($item) {
                return $item->id;
            }, $regions));
        }
    }

    /**
     *
     */
    public function setRefREIComp(): void
    {
        if (!is_null($this->mainSector())) {
            if (strlen($this->ref) == 3) {
                $this->ref_REI_comp = $this->mainSector()->full_code . substr($this->ref, 1) . '0';
            } else if (strlen($this->ref) == 4) {
                $this->ref_REI_comp = $this->mainSector()->full_code . substr($this->ref, 1);
            } else {
                $this->ref_REI_comp = $this->mainSector()->full_code . '????';
            }
        }
    }

    /**
     * Compute and Associate all Territorial Units
     */
    public function computeAndSetTerritorialUnits(): void
    {
        $this->computeAndSetSectors();
        $this->setRefREIComp();
        $this->computeAndSetAreas();
        $this->computeAndSetProvinces();
        $this->computeAndSetRegions();
    }

    /**
     * Get the hiking routes ids intersecting a bounding box in a specific status
     *
     * @param int $osm2cai_status the status
     * @param float $lo0 the minimum longitude
     * @param float $la0 the minimum latitude
     * @param float $lo1 the maximum longitude
     * @param float $la1 the maximum latitude
     *
     * @return array
     */
    public static function idsByBoundingBox(int $osm2cai_status, float $lo0, float $la0, float $lo1, float $la1): array
    {
        $geometry_field = 'geometry_osm';

        if (!in_array($osm2cai_status, [0, 1, 2, 3, 4])) {
            return [];
        }
        if ($osm2cai_status == 4) {
            $geometry_field = 'geometry';
        }
        $ids = [];
        // Build Polygon BB geometry
        $coords = [[[$lo0, $la0], [$lo0, $la1], [$lo1, $la1], [$lo1, $la0], [$lo0, $la0]]];
        $poly = new Polygon($coords);
        $res = DB::select(DB::raw('SELECT ST_GeomFromGeoJSON(\'' . json_encode($poly->jsonSerialize()) . '\') as geom'));
        $geom = $res[0]->geom;

        $query = "
                SELECT id
                FROM hiking_routes
                WHERE ST_intersects(ST_SetSRID(" . $geometry_field . ", 4326), ST_GeomFromGeoJSON('" . json_encode($poly->jsonSerialize()) . "')) AND
                osm2cai_status=" . $osm2cai_status;
        $res = DB::select(DB::raw($query));
        if (count($res) > 0) {
            foreach ($res as $obj) {
                $ids[] = $obj->id;
            }
        }

        return $ids;
    }

    /**
     * Returns geojson Feature Collection
     *
     * @param $osm2cai_status
     * @param $lo0
     * @param $la0
     * @param $lo1
     * @param $la1
     *
     * @return string
     */
    public static function geojsonByBoundingBox($osm2cai_status, $lo0, $la0, $lo1, $la1): string
    {
        // TODO: remove idsByBoundingBox call and implement query ST_intersects directly
        // TODO: unitTest (inspired by Feature test HikingRouteBoundingBox)
        $ids = self::idsByBoundingBox($osm2cai_status, $lo0, $la0, $lo1, $la1);
        if (count($ids) == 0)
            return json_encode(["type" => "FeatureCollection", "features" => []]);
        // Build Query
        $geometry_field = 'geometry_osm';
        if ($osm2cai_status == 4) {
            $geometry_field = 'geometry';
        }
        $where = implode(',', $ids);
        $query = <<<EOF
SELECT json_build_object(
    'type', 'FeatureCollection',
    'features', json_agg(ST_AsGeoJSON(t.*)::json)
    )
FROM
(SELECT id,created_at,updated_at,osm2cai_status,validation_date,relation_id,
        ref, old_ref, source, source_ref, survey_date, name, rwn_name,
        ref_osm, old_ref_osm, source_osm, source_ref_osm, survey_date_osm, name_osm, rwn_name_osm,
        "ref_REI_osm","ref_REI","ref_REI_comp",
        cai_scale, "from", "to", osmc_symbol, network, roundtrip, symbol, symbol_it,
        cai_scale_osm, "from_osm", "to_osm", osmc_symbol_osm, network_osm, roundtrip_osm, symbol_osm, symbol_it_osm,
        "ascent", "descent", "distance", "duration_forward", "duration_backward",
        "ascent_osm", "descent_osm", "distance_osm", "duration_forward_osm", "duration_backward_osm",
        "ascent_comp", "descent_comp", "distance_comp", "duration_forward_comp", "duration_backward_comp",
        "operator", "state", "description", "description_it", "website", "wikimedia_commons",
        "maintenance", "maintenance_it", "note", "note_it", "note_project_page",
        "operator_osm", "state_osm", "description_osm", "description_it_osm", "website_osm", "wikimedia_commons_osm",
        "maintenance_osm", "maintenance_it_osm", "note_osm", "note_it_osm", "note_project_page_osm",
        $geometry_field
        FROM hiking_routes WHERE id IN ($where)) AS

      t(id,created_at,updated_at,osm2cai_status,validation_date,relation_id,
        ref, old_ref, source, source_ref, survey_date, name, rwn_name,
        ref_osm, old_ref_osm, source_osm, source_ref_osm, survey_date_osm, name_osm, rwn_name_osm,
        "ref_REI_osm","ref_REI","ref_REI_comp",
        cai_scale, "from", "to", osmc_symbol, network, roundtrip, symbol, symbol_it,
        cai_scale_osm, "from_osm", "to_osm", osmc_symbol_osm, network_osm, roundtrip_osm, symbol_osm, symbol_it_osm,
        "ascent", "descent", "distance", "duration_forward", "duration_backward",
        "ascent_osm", "descent_osm", "distance_osm", "duration_forward_osm", "duration_backward_osm",
        "ascent_comp", "descent_comp", "distance_comp", "duration_forward_comp", "duration_backward_comp",
        "operator", "state", "description", "description_it", "website", "wikimedia_commons",
        "maintenance", "maintenance_it", "note", "note_it", "note_project_page",
        "operator_osm", "state_osm", "description_osm", "description_it_osm", "website_osm", "wikimedia_commons_osm",
        "maintenance_osm", "maintenance_it_osm", "note_osm", "note_it_osm", "note_project_page_osm",
        geom);
EOF;
        $res = DB::select(DB::raw($query));

        return $res[0]->json_build_object;
    }

    public function validateSDA($user_id, $date)
    {
        $this->validation_date = $date;
        $this->user_id = $user_id;
        $this->osm2cai_status = 4;
        $this->save();
    }



    public function addLayerToMap($geometry, $getCentroid)
    {
        return [
            LeafletMap::make('Mappa')
                ->type('GeoJson')
                ->geoJson(json_encode($geometry))
                ->center($getCentroid[1], $getCentroid[0])
                ->zoom(12)
        ];
    }


    /**
     * Scope a query to only include models owned by a certain user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Model\User  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOwnedBy($query, User $user)
    {

        //collect all possible hiking route partents model related to user
        $userModels = collect([
            [$user->region], //force array
            $user->provinces->all(), //array
            $user->areas->all(), //array
            $user->sectors->all() //array
        ])->filter()->collapse();

        $userHikingRoutes = $userModels->filter()->map(function ($model) {
            //iterate over them to get children up to hikingRoutes
            return $model->getHikingRoutes();
        })->collapse()->unique();

        $userHikingRoutesIds = $userHikingRoutes->pluck('id');
        return $query->whereIn('id', $userHikingRoutesIds);
    }



    public function hasCorrectGeometry()
    {
        $geojson = $this->query()->where('id', $this->id)->selectRaw('ST_AsGeoJSON(geometry) as geom')->get()->pluck('geom')->first();
        $geom = json_decode($geojson, TRUE);
        $type = $geom['type'];
        $nseg = count($geom['coordinates']);
        if ($nseg > 1 && $this->osm2cai_status == 4)
            return false;

        return true;
    }

    public function getPublicPage()
    {
        return url('/') . '/hiking-route/id/' . $this->id;
    }

    public function revertValidation()
    {
        if ($this->osm2cai_status == 4)
            $this->osm2cai_status = 3;
        $this->validation_date = null;
        $this->user_id = null;
        $this->save();
    }

    public function setGeometrySync()
    {
        if ($this->geometry == $this->geometry_osm)
            $this->geometry_sync = true;
        else
            $this->geometry_sync = false;
        $this->save();
    }


    /**
     * It returns a valid name for TDH export, even if the field name ha no value
     * The name is not translated (it,en,es,de,fr,pt)
     *
     * @return array
     */
    public function getNameForTDH(): array
    {
        $v = [];
        if (!empty($this->name)) {
            $v = [
                'it' => $this->name,
                'en' => $this->name,
                'es' => $this->name,
                'de' => $this->name,
                'fr' => $this->name,
                'pt' => $this->name,
            ];
        } else if (!empty($this->ref)) {
            $v = [
                'it' => 'Sentiero ' . $this->ref,
                'en' => 'Path ' . $this->ref,
                'es' => 'Camino ' . $this->ref,
                'de' => 'Weg ' . $this->ref,
                'fr' => 'Chemin ' . $this->ref,
                'pt' => 'Caminho ' . $this->ref,
            ];
        } else {
            $info = $this->getFromInfo();
            $v = [
                'it' => 'Sentiero del Comune di ' . $info['city_from'],
                'en' => 'Path in the municipality of ' . $info['city_from'],
                'es' => 'Camino en el municipio de ' . $info['city_from'],
                'de' => 'Weg in der Gemeinde ' . $info['city_from'],
                'fr' => 'Chemin dans la municipalité de ' . $info['city_from'],
                'pt' => 'Caminho no município de ' . $info['city_from'],
            ];
        }
        return $v;
    }

    /**
     * Restituisce l'etichetta breve della scala di difficoltà in multilingue
     *
     * @return array
     */
    public function getCaiScaleString(): array
    {
        switch ($this->cai_scale) {
            case 'T':
                $v = [
                    'it' => 'Turistico',
                    'en' => 'Easy Hiking Trail',
                    'es' => 'Turístico',
                    'de' => 'Touristische Route',
                    'fr' => 'Sentier touristique',
                    'pt' => 'Turístico'
                ];
                break;

            case 'E':
                $v = [
                    'it' => 'Escursionistico',
                    'en' => 'Hiking Trail',
                    'es' => 'Excursionista',
                    'de' => 'Wanderweg',
                    'fr' => 'Sentier de randonnée',
                    'pt' => 'Caminhadas'
                ];
                break;

            case 'EE':
                $v = [
                    'it' => 'Escursionisti Esperti',
                    'en' => 'Experienced Hikers',
                    'es' => 'Excursionistas expertos',
                    'de' => 'Erfahrene Wanderer',
                    'fr' => 'Randonneurs chevronnés',
                    'pt' => 'Caminhantes Experientes'
                ];
                break;

            default:
                $v = [
                    'it' => 'Difficoltà sconosciuta',
                    'en' => 'Unknown difficulty',
                    'de' => 'Unbekannte Schwierigkeit',
                    'fr' => 'Difficulté inconnue',
                ];
                break;
        }

        return $v;
    }

    /**
     * Restituisce l'etichetta estesa della scala di difficoltà in multilingue
     *
     * @return array
     */
    public function getCaiScaleDescription(): array
    {
        switch ($this->cai_scale) {
            case 'T':
                $v = [
                    'it' => trim(preg_replace('/\s\s+/', ' ', "CARATTERISTICHE

                    Percorsi su carrarecce, mulattiere o evidenti sentieri che non pongono incertezze o problemi di orientamento, con modeste pendenze e dislivelli contenuti.
                    
                    ABILITA’ E COMPETENZE
                    
                    Richiedono conoscenze escursionistiche di base e preparazione fisica alla camminata.
                    
                    ATTREZZATURE
                    
                    Sono comunque richiesti adeguato abbigliamento e calzature adatte.")),
                    'en' => trim(preg_replace('/\s\s+/', ' ', "FEATURES

                    Routes on bridle paths, mule tracks or obvious trails that do not present any uncertainties or orientation problems, with moderate gradients.
                    
                    SKILLS AND COMPETENCES
                    
                    Basic hiking knowledge and physical fitness for walking are required.
                    
                    EQUIPMENT
                    
                    Appropriate clothing and footwear are still required.")),
                    'es' => trim(preg_replace('/\s\s+/', ' ', "CARACTERÍSTICAS

                    Rutas por pistas, caminos o senderos obvios que no plantean incertidumbres o problemas de orientación, con pequeñas pendientes y desniveles contenidos.
                    
                    HABILIDADES Y COMPETENCIAS
                    
                    Requieren conocimientos básicos de excursionismo y preparación física para caminar.
                    
                    EQUIPO
                    
                    Se requiere igualmente ropa y calzado adecuados.")),
                    'de' => trim(preg_replace('/\s\s+/', ' ', "MERKMALE

                    Routen auf Feldwegen, Saumpfaden oder offensichtlichen Wegen, die keine Unsicherheiten oder Orientierungsprobleme aufwerfen, mit bescheidenen Steigungen und geringen Höhenunterschieden.
                    
                    FÄHIGKEITEN und KOMPETENZEN
                    
                    Grundlegende Wanderkenntnisse und körperliche Vorbereitung auf das Gehen erforderlich.
                    
                    AUSRÜSTUNG
                    
                    Geeignete Kleidung und Schuhe sind in jedem Fall erforderlich.")),
                    'fr' => trim(preg_replace('/\s\s+/', ' ', "Des parcours sur rails, des chemins muletiers ou des sentiers évidents qui ne posent pas d’incertitudes ou de problèmes d’orientation, avec des pentes modestes et des dénivelés limités.

                    APTITUDES ET COMPÉTENCES
                    
                    Connaissances de base en randonnée et en préparation physique à la marche.
                    
                    ÉQUIPEMENT
                    
                    Cependant, des vêtements et des chaussures appropriés sont nécessaires.")),
                    'pt' => trim(preg_replace('/\s\s+/', ' ', "CARACTERÍSTICAS

                    Percursos em carreiros, trilhas ou caminhos óbvios, que não apresentam dúvidas ou problemas de orientação, com declives modestos e desníveis moderados.
                    
                    HABILIDADES e COMPETÊNCIAS
                    
                    Exigem conhecimentos básicos de caminhada e preparação física para caminhada
                    
                    EQUIPAMENTO 
                    
                    Vestuário e calçado adequados são, no entanto, necessários.")),
                ];
                break;

            case 'E':
                $v = [
                    'it' => trim(preg_replace('/\s\s+/', ' ', "CARATTERISTICHE

                    Percorsi che rappresentano la maggior parte degli itinerari escursionistici, quindi tra i più vari per ambienti naturali. Si svolgono per mulattiere, sentieri e talvolta tracce; su terreno diverso per contesto geomorfologico e vegetazionale (es. pascoli, sottobosco, detriti, pietraie). Sono generalmente segnalati e possono presentare tratti ripidi. Si possono incontrare facili passaggi su roccia, non esposti, che necessitano l’utilizzo delle mani per l’equilibrio. Eventuali punti esposti sono in genere protetti. Possono attraversare zone pianeggianti o poco inclinate su neve residua.
                    
                    ABILITA’ E COMPETENZE
                    
                    Richiedono senso di orientamento ed esperienza escursionistica e adeguato allenamento
                    
                    ATTREZZATURE
                    
                    È richiesto idoneo equipaggiamento con particolare riguardo alle calzature.")),
                    'en' => trim(preg_replace('/\s\s+/', ' ', "FEATURES

                    Routes that represent most of the hiking itineraries, and therefore among the most varied in terms of natural environments. They run along mule tracks, paths and sometimes tracks; on terrain that varies in geomorphological and vegetational context (e.g. pasture, undergrowth, scree, scree slopes). They are generally signposted and may have steep sections. Easy, unexposed rock sections may be encountered that require the use of hands for balance. Any exposed points are generally protected. They can pass across flat or gently sloping areas on residual snow.
                    
                    SKILLS AND COMPETENCES
                    
                    They require a sense of direction, hiking experience and adequate training
                    
                    EQUIPMENT
                    
                    Suitable equipment is required, particularly with regard to footwear.")),
                    'es' => trim(preg_replace('/\s\s+/', ' ', "CARACTERÍSTICAS

                    Rutas que representan la mayoría de los itinerarios de excursionismo, por lo que se encuentran en los más variados entornos naturales. Recorren caminos, senderos y, a veces, pistas; en diferentes terrenos en cuanto a entornos geomorfológicos y de vegetación (p. ej., pastizales, bosque bajo, roquedales, pedregales). Por lo general, están marcadas y pueden presentar tramos escarpados. Se pueden encontrar pasos fáciles en la roca, no expuestos, que requieren el uso de las manos para mantener el equilibrio. Por lo general, los puntos expuestos están protegidos. Pueden atravesar zonas llanas o poco inclinadas sobre nieve residual.
                    
                    HABILIDADES Y COMPETENCIAS
                    
                    Requieren sentido de la orientación, experiencia como excursionista y entrenamiento adecuado
                    
                    EQUIPO
                    
                    Se requiere un equipo adecuado, con especial atención al calzado.")),
                    'de' => trim(preg_replace('/\s\s+/', ' ', "MERKMALE

                    Routen, die den größten Teil der Wanderwege ausmachen und aus diesem Grund zu den abwechslungsreichsten für natürliche Umgebungen gehören. Sie finden auf Saumpfaden, Wegen und manchmal Spuren statt; auf einem Gelände, das sich aufgrund des geomorphologischen und vegetativen Kontextes (z. B. Weiden, Waldboden, Trümmer, Steine) unterscheidet. Sie sind in der Regel gekennzeichnet und können steile Abschnitte aufweisen. Sie können auf einfache, unbelichtete Felspassagen stoßen, bei denen die Verwendung der Hände für das Gleichgewicht erforderlich ist. Alle exponierten Stellen sind in der Regel geschützt. Sie können flache oder wenig geneigte Bereiche auf Restschnee durchqueren.
                    
                    FÄHIGKEITEN und KOMPETENZEN
                    
                    Sie erfordern Orientierungssinn und Wandererfahrung sowie angemessenes Training
                    
                    AUSRÜSTUNG
                    
                    Eine geeignete Ausrüstung mit besonderem Augenmerk auf die Schuhe ist erforderlich.")),
                    'fr' => trim(preg_replace('/\s\s+/', ' ', "CARACTÉRISTIQUES

                    Des parcours qui représentent la plupart des itinéraires de randonnée, donc parmi les plus variés pour les environnements naturels. Ils se déroulent sur des chemins muletiers, des sentiers et parfois des pistes ; sur des terrains différents en raison de leur cadre géomorphologique et végétatif (par exemple, des pâturages, sous-bois, débris, pierres). Ils sont généralement signalés et peuvent présenter des tronçons raides. Vous pouvez rencontrer des passages faciles sur roche, non exposés, qui nécessitent l’utilisation des mains pour l’équilibre. Tous les points exposés sont généralement protégés. Ils peuvent traverser des zones plates ou peu inclinées sur la neige résiduelle.
                    
                    APTITUDES ET COMPÉTENCES
                    
                    Ils exigent un sens de l’orientation, de l’expérience de la randonnée et une formation adéquate.
                    
                    ÉQUIPEMENT
                    
                    Un équipement approprié est requis, en particulier en ce qui concerne les chaussures.")),
                    'pt' => trim(preg_replace('/\s\s+/', ' ', "CARACTERÍSTICAS

                    Percursos que fazem parte da maioria das rotas de caminhada e que são, portanto, muito variadas em termos de ambiente natural. Desenvolvem-se em trilhas, caminhos e por vezes em traçados; em diferentes terrenos em termos de contexto geomorfológico e vegetacional (por ex. pastagens, mato, detritos, pedranceiras). Estão geralmente assinalados e podem apresentar trechos íngremes. Podem-se encontrar passagens fáceis na rocha, não expostas, que exigem o uso das mãos para o equilíbrio. Os eventuais pontos expostos estão geralmente protegidos. Podem atravessar áreas planas ou ligeiramente inclinadas na neve residual.
                    
                    HABILIDADES e COMPETÊNCIAS
                    
                    Exigem um sentido de orientação e experiência de caminhada e treino adequados
                    
                    EQUIPAMENTO
                    
                    É necessário equipamento adequado, especialmente no que diz respeito ao calçado.")),
                ];
                break;

            case 'EE':
                $v = [
                    'it' => trim(preg_replace('/\s\s+/', ' ', "CARATTERISTICHE

                    Percorsi quasi sempre segnalati che richiedono capacità di muoversi lungo sentieri e tracce su terreno impervio e/o infido (pendii ripidi e/o scivolosi di erba, roccette o detriti sassosi), spesso instabile e sconnesso. Possono presentare tratti esposti, traversi, cenge o tratti rocciosi con lievi difficoltà tecniche e/o attrezzati, mentre sono escluse le ferrate propriamente dette. Si sviluppano su pendenze medio‐alte. Può essere necessario l’attraversamento di tratti su neve, mentre sono esclusi tutti i percorsi su ghiacciaio.
                    
                    ABILITA’ E COMPETENZE
                    
                    Necessitano di ottima esperienza escursionistica, capacità di orientamento, conoscenza delle caratteristiche dell’ambiente montano, passo sicuro e assenza di vertigini, capacità valutative e decisionali nonché di preparazione fisica adeguata.
                    
                    ATTREZZATURE
                    
                    Richiedono equipaggiamento e attrezzatura adeguati all’itinerario programmato. ")),
                    'en' => trim(preg_replace('/\s\s+/', ' ', "FEATURES

                    Routes that are almost always signposted and require the ability to move along paths and tracks over inaccessible and/or treacherous terrain (steep and/or slippery slopes of grass, rocks or stony debris), often unstable and uneven. They may have exposed sections, traverses, ledges or rocky sections with slight technical difficulties and/or structural aids, although there are no actual via ferratas. They extend over medium-steep slopes. Crossing sections on snow may be necessary, although no glacier routes are included.
                    
                    SKILLS AND COMPETENCES
                    
                    They require excellent hiking experience, orienteering skills, knowledge of the features of the mountain environment, sure-footedness, absence of vertigo and assessment and decision-making skills as well as adequate physical fitness.
                    
                    EQUIPMENT
                    
                    They require suitable equipment and gear for the planned route. ")),
                    'es' => trim(preg_replace('/\s\s+/', ' ', "CARACTERÍSTICAS

                    Rutas casi siempre señalizadas que requieren la capacidad de moverse a lo largo de senderos y pistas en terrenos difíciles o traicioneros (pendientes empinadas o resbaladizas de hierba, rocas o piedras), a menudo inestables y desiguales. Pueden presentar tramos expuestos, traviesas, cornisas o tramos rocosos con leves dificultades técnicas o que requieren el uso de equipos, mientras que se excluyen las vías ferratas propiamente dichas. Se desarrollan en pendientes medias-altas. Puede ser necesario atravesar tramos sobre nieve, mientras que se excluyen todas las rutas sobre hielo.
                    
                    HABILIDADES Y COMPETENCIAS
                    
                    Necesitan una excelente experiencia de excursionismo, capacidad de orientación, conocimiento de las características del entorno de montaña, tener un paso seguro y ausencia de vértigo, habilidades de evaluación y toma de decisiones, así como una preparación física adecuada.
                    
                    EQUIPO
                    
                    Requieren equipamiento y equipo adecuado para el itinerario programado. ")),
                    'de' => trim(preg_replace('/\s\s+/', ' ', "MERKMALE 

                    Fast immer gekennzeichnete Wege, die die Fähigkeit erfordern, sich auf Wegen und Spuren auf unebenem und/oder tückischem Gelände (steilen und/oder rutschigen Hängen von Gras, Felsen oder steinigen Trümmern) fortzubewegen, oft instabil und uneben. Sie können freiliegende Abschnitte, Traversen, Böschungen oder felsige Abschnitte mit leichten technischen Schwierigkeiten aufweisen und/oder ausgerüstet sein, während die eigentlichen Klettersteige ausgeschlossen sind. Sie entwickeln sich auf mittleren bis hohen Steigungen. Es kann erforderlich sein, Abschnitte auf Schnee zu überqueren, während alle Gletscherrouten ausgeschlossen sind.
                    
                    FÄHIGKEITEN und KOMPETENZEN
                    
                    Es sind ausgezeichnete Wandererfahrung, Orientierungsfähigkeit, Kenntnis der Merkmale der Bergumgebung, sicheres Tempo erforderlich, man muss schwindelfrei sein und über Bewertungs- und Entscheidungsfähigkeit sowie eine angemessene körperliche Vorbereitung verfügen.
                    
                    AUSRÜSTUNG
                    
                    Sie benötigen Ausstattung und Ausrüstung, die an die geplante Route angepasst sind. ")),
                    'fr' => trim(preg_replace('/\s\s+/', ' ', "CARACTÉRISTIQUES

                    Des parcours presque toujours signalés qui nécessitent la capacité à se déplacer le long des sentiers et des pistes sur un terrain accidenté et/ou traître (pentes raides et/ou glissantes d’herbe, de rochers ou de débris de pierre), souvent instable et inégal. Ils peuvent comporter des tronçons exposés, des traverses, des cendres ou des tronçons rocheux présentant de légères difficultés techniques et/ou équipés, tandis que les ferrate proprement dites sont exclues. Ils se développent sur des pentes moyennes à élevées. Il peut être nécessaire de traverser des tronçons sur la neige, tandis que tous les parcours sur le glacier sont exclus.
                    
                    APTITUDES ET COMPÉTENCES
                    
                    Cela nécessite une excellente expérience de la randonnée, une capacité d’orientation, une connaissance des caractéristiques de l’environnement de montagne, un pas sûr et l’absence de vertiges, des capacités d’évaluation et de prise de décision, ainsi qu’une préparation physique adéquate.
                    
                    ÉQUIPEMENT
                    
                    Cela nécessite un équipement adapté à l’itinéraire prévu. ")),
                    'pt' => trim(preg_replace('/\s\s+/', ' ', "CARACTERÍSTICAS

                    Percursos quase sempre assinalados que exigem a capacidade para se mover ao longo de caminhos e trilhas de terreno acidentado e/ou traiçoeiro (encostas íngremes e/ou escorregadias, de erva, rochas ou detritos pedregosos), muitas vezes instáveis e desarticulados. Podem apresentar trechos expostos, travessias, saliências ou partes rochosas com ligeiras dificuldades técnicas e/ou equipadas, excluindo-se as vias ferratas propriamente ditas. Desenvolvem-se em declives médio-altos. Pode ser necessário atravessar trechos na neve, estando excluídos todos os percursos no gelo.
                    
                    HABILIDADES e COMPETÊNCIAS
                    
                    É necessário excelente experiência de caminhada, capacidade de orientação, conhecimento das características do ambiente de montanha, passo seguro e ausência de vertigens, capacidades de avaliação e de tomada de decisão, bem como preparação física adequada.
                    
                    EQUIPAMENTO
                    
                    Requer equipamento e dispositivos adequados ao itinerário planeado. ")),
                ];
                break;

            default:
                $v = [
                    'it' => 'Difficoltà sconosciuta',
                    'en' => 'Unknown difficulty',
                    'de' => 'Unbekannte Schwierigkeit',
                    'fr' => 'Difficulté inconnue',
                ];
                break;
        }

        return $v;
    }

    /**
     * Restituisce un array associativo con le informazioni del punto di partenza ricavate
     * dal DB Istat dei comuni (tabella municipality_boundaries)
     * 
     * Per ricavare l'intersezione si usa la seguente query:
     * SELECT m.cod_reg as cod_reg, m.comune as comune, m.pro_com_t as istat
     * FROM municipality_boundaries as m, hiking_routes as hr 
     * WHERE st_intersects(m.geom,ST_transform(ST_startpoint(hr.geometry),4326)) 
     *   AND hr.id=19222;
     *
     * 
     * @return array
     */
    public function getFromInfo(): array
    {

        $from = $this->from;
        $info = [
            'from' => $from,
            'city_from' => 'Sconosciuto',
            'city_from_istat' => 'Sconosciuto',
            'region_from' => 'Sconosciuto',
            'region_from_istat' => 'Sconosciuto',
        ];

        // Get data from ISTAT
        $query = "SELECT m.cod_reg as cod_reg, m.comune as comune, m.pro_com_t as istat FROM municipality_boundaries as m, hiking_routes as hr WHERE st_intersects(m.geom,ST_transform(ST_startpoint(hr.geometry),4326)) AND hr.id=$this->id;";
        try {
            //code...
            $res = DB::select($query);
            if (count($res) > 0) {
                $info['city_from'] = $res[0]->comune;
                $info['city_from_istat'] = $res[0]->istat;
                $info['region_from'] = config('osm2cai.region_istat_name.' . $res[0]->cod_reg);
                $info['region_from_istat'] = $res[0]->cod_reg;

                if (empty($info['from'])) {
                    $info['from'] = $info['city_from'];
                }
            }
        } catch (\Throwable $th) {
            echo "ERROR on query: $query (ID:$this->id)\n";
        }

        return $info;
    }
    /**
     * Restituisce un array associativo con le informazioni del punto di partenza ricavate
     * dal DB Istat dei comuni (tabella municipality_boundaries)
     * 
     * Per ricavare l'intersezione si usa la seguente query:
     * SELECT m.cod_reg as cod_reg, m.comune as comune, m.pro_com_t as istat
     * FROM municipality_boundaries as m, hiking_routes as hr 
     * WHERE st_intersects(m.geom,ST_transform(ST_startpoint(hr.geometry),4326)) 
     *   AND hr.id=19222;
     *
     * 
     * @return array
     */
    public function getToInfo(): array
    {

        $to = $this->to;
        $info = [
            'to' => $to,
            'city_to' => 'Sconosciuto',
            'city_to_istat' => 'Sconosciuto',
            'region_to' => 'Sconosciuto',
            'region_to_istat' => 'Sconosciuto',
        ];

        // Get data from ISTAT
        $query = "SELECT m.cod_reg as cod_reg, m.comune as comune, m.pro_com_t as istat FROM municipality_boundaries as m, hiking_routes as hr WHERE st_intersects(m.geom,ST_transform(ST_endpoint(ST_linemerge(hr.geometry)),4326)) AND hr.id=$this->id;";

        try {
            //code...
            $res = DB::select($query);
            if (count($res) > 0) {
                $info['city_to'] = $res[0]->comune;
                $info['city_to_istat'] = $res[0]->istat;
                $info['region_to'] = config('osm2cai.region_istat_name.' . $res[0]->cod_reg);
                $info['region_to_istat'] = $res[0]->cod_reg;

                if (empty($info['to'])) {
                    $info['to'] = $info['city_to'];
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            echo "ERROR on query: $query (ID:$this->id)\n";
        }


        return $info;
    }

    /**
     * Return true if geometry is compatible with roundtrip hiking routes:
     * The distance between first and last point must be lesser than OSM2CAI_ROUNDTRIP_THRASHOLD
     *
     * @return boolean
     */
    public function checkRoundTripFromGeometry(): bool
    {
        // TODO: implement real check
        $roundtrip = false;
        if ($this->roundtrip == 'yes') {
            $roundtrip = true;
        }
        return $roundtrip;
    }


    /**
     * It gets INFO data from geohub and it returns it in hash.
     *
     * @return array
     */
    public function getTechInfoFromGeohub(): array
    {

        $info = [
            'gpx_url' => 'Unknown',
            'distance' => 'Unknown',
            'ascent' => 'Unknown',
            'descent' => 'Unknown',
            'duration_forward' => 'Unknown',
            'duration_backward' => 'Unknown',
            'ele_from' => 'Unknown',
            'ele_to' => 'Unknown',
            'ele_max' => 'Unknown',
            'ele_min' => 'Unknown',
        ];

        $geohub_url = 'https://geohub.webmapp.it/api/osf/track/osm2cai/' . $this->id;
        try {
            $geohub = json_decode(file_get_contents($geohub_url), true);
            $properties = $geohub['properties'];
            $info = [
                'gpx_url' => $properties['gpx_url'],
                'distance' => $properties['distance'],
                'ascent' => $properties['ascent'],
                'descent' => $properties['descent'],
                'duration_forward' => $properties['duration_forward'],
                'duration_backward' => $properties['duration_backward'],
                'ele_from' => $properties['ele_from'],
                'ele_to' => $properties['ele_to'],
                'ele_max' => $properties['ele_max'],
                'ele_min' => $properties['ele_min'],
            ];
        } catch (\Throwable $th) {
            echo "ERROR ON getting data from geohub $geohub_url";
        }

        // Update with OSM data ascent,discent,duration_forward,duration_backward
        if (isset($this->ascent_osm) && !empty($this->ascent_osm)) {
            $info['ascent'] = $this->ascent_osm;
        }
        if (isset($this->descent_osm) && !empty($this->descent_osm)) {
            $info['descent'] = $this->descent_osm;
        }
        if (isset($this->duration_forward_osm) && !empty($this->duration_forward_osm)) {
            $info['duration_forward'] = $this->h2m($this->duration_forward_osm);
        }
        if (isset($this->duration_backward_osm) && !empty($this->duration_backward_osm)) {
            $info['duration_backward'] = $this->h2m($this->duration_backward_osm);
        }

        return $info;
    }

    public function h2m($strHourMinute)
    {
        $strHourMinute = preg_replace('/[^0-9:]/', '', $strHourMinute);
        $from = date('Y-m-d 00:00:00');
        $to = date('Y-m-d ' . $strHourMinute . ':00');
        $diff = strtotime($to) - strtotime($from);
        $minutes = $diff / 60;
        return (int) $minutes;
    }



    /**
     * Restituisce un abstract del percorso automatico, costruito a partire dai metadati del percorso stesso.
     *
     * @param array $from dati da getFromInfo
     * @param array $to dati da getToInfo
     * @param array $tech dati da getAbstract
     * @return array
     */
    public function getAbstract(array $from, array $to, array $tech): array
    {
        $abstract = [];
        $cai_scale_string = $this->getCaiScaleString();
        if (!$this->checkRoundTripFromGeometry()) {
            // Percorso AB
            $abstract = [
                'it' => trim(preg_replace('/\s\s+/', ' ', "Il percorso escursionistico $this->ref parte da {$from['from']}, nel Comune di {$from['city_from']} e termina a {$to['to']}, nel comune di {$to['city_to']}. Secondo lo standard CAI, è classificato come {$cai_scale_string['it']} e copre una distanza totale di {$tech['distance']} chilometri.
                L'altitudine del punto di partenza è {$tech['ele_from']} metri e l'altitudine massima raggiunta è di {$tech['ele_max']} metri, mentre l'altitudine minima è di {$tech['ele_min']} metri.
                Il percorso escursionistico è adatto chi vuole immergersi nella natura e godersi un'esperienza rilassante e rigenerante.
                Si consiglia di essere ben equipaggiati e preparati ad affrontare le diverse condizioni climatiche e i possibili ostacoli che potrebbero presentarsi lungo il percorso.")),
                'en' => trim(preg_replace('/\s\s+/', ' ', "The hiking trail $this->ref starts from {$from['from']}, in the Municipality of {$from['city_from']} and ends at {$to['to']}, in the Municipality of {$to['city_to']}. According to the CAI standard, it is classified as {$cai_scale_string['it']} and covers a total distance of {$tech['distance']} kilometres.
                The altitude of the starting point is {$tech['ele_from']} metres and the maximum altitude reached is {$tech['ele_max']} metres, while the minimum altitude is {$tech['ele_min']} metres.
                The hiking trail is suitable for those who want to immerse themselves in nature and enjoy a relaxing and regenerating experience.
                It is advisable to be well equipped and prepared to deal with the different weather conditions and possible obstacles that may arise along the way.")),
                'es' => trim(preg_replace('/\s\s+/', ' ', "La ruta de excursionismo $this->ref parte de {$from['from']}, en el municipio de {$from['city_from']}, y termina en {$to['to']}, en el municipio de {$to['city_to']}. Según el estándar CAI, está clasificada como {$cai_scale_string['it']} y cubre una distancia total de {$tech['distance']} kilómetros.
                La altitud del punto de partida es de {$tech['ele_from']} metros y la altitud máxima alcanzada es de {$tech['ele_max']} metros, mientras que la altitud mínima es de {$tech['ele_min']} metros.
                La ruta de excursionismo es adecuada para quienes quieren sumergirse en la naturaleza y disfrutar de una experiencia relajante y regeneradora.
                Se recomienda equiparse y prepararse adecuadamente para hacer frente a las diferentes condiciones climáticas y a los posibles obstáculos que puedan surgir en el recorrido.")),
                'de' => trim(preg_replace('/\s\s+/', ' ', "Der Wanderweg $this->ref beginnt in {$from['from']} in der Gemeinde {$from['city_from']} und endet in {$to['to']} in der Gemeinde {$to['city_to']}. Gemäß dem Standard des CAI ist es als {$cai_scale_string['it']} klassifiziert und deckt eine Gesamtdistanz von {$tech['distance']} Kilometern ab.
                Die Höhe des Startpunkts beträgt {$tech['ele_from']} Meter und die maximal erreichte Höhe beträgt {$tech['ele_max']} Meter, während die Mindesthöhe {$tech['ele_min']} Meter beträgt.
                Der Wanderweg eignet sich für diejenigen, die in die Natur eintauchen und ein entspannendes und regenerierendes Erlebnis genießen möchten.
                Es wird empfohlen, sich gut auszustatten, um den unterschiedlichen klimatischen Bedingungen und möglichen Hindernissen zu begegnen, die auf dem Weg auftreten können.")),
                'fr' => trim(preg_replace('/\s\s+/', ' ', "Le parcours de randonnée $this->ref part de {$from['from']}, dans la commune de {$from['city_from']} et se termine à {$to['to']}, dans la commune de {$to['city_to']}. Selon la norme CAI, il est classé comme {$cai_scale_string['it']} et couvre une distance totale de {$tech['distance']} kilomètres.
                L'altitude du point de départ est de {$tech['ele_from']} mètres et l'altitude maximale atteinte est de {$tech['ele_max']} mètres, tandis que l'altitude minimale est de {$tech['ele_min']} mètres.
                Le parcours de randonnée est adapté à ceux qui veulent s'immerger dans la nature et profiter d'une expérience relaxante et régénérante.
                Il est recommandé d'être bien équipé et préparé pour faire face aux différentes conditions climatiques et aux obstacles potentiels qui pourraient se présenter le long du chemin.")),
                'pt' => trim(preg_replace('/\s\s+/', ' ', "O percurso excursionista $this->ref parte de {$from['from']}, no município de {$from['city_from']} e termina em [to], no município de {$to['city_to']}. De acordo com o padrão CAI, é classificado como {$cai_scale_string['it']} e cobre uma distância total de {$tech['distance']} quilómetros.
                A altitude do ponto de partida é de {$tech['ele_from']} metros e a altitude máxima atingida é de {$tech['ele_max']} metros, enquanto a altitude mínima é de {$tech['ele_min']} metros.
                O percurso excursionista é adequado quem quer mergulhar na natureza e desfrutar de uma experiência relaxante e regeneradora.
                É aconselhável estar bem equipado e preparado para lidar com as diferentes condições climatéricas e possíveis obstáculos que possam surgir ao longo do caminho.")),
            ];
        } else {
            // Percorso ad anello
            $abstract = [
                'it' => trim(preg_replace('/\s\s+/', ' ', "Il percorso escursionistico ad anello $this->ref ha il suo punto di partenza e arrivo in {$from['from']}, nel Comune di {$from['city_from']}. Secondo lo standard CAI è classificato come {$cai_scale_string['it']} e copre una distanza totale di {$tech['distance']} chilometri.
                La lunghezza totale del percorso è di {$tech['distance']} chilometri, con un dislivello significativo.
                L'altitudine del punto di partenza è {$tech['ele_from']} metri, e l'altitudine massima raggiunta è di {$tech['ele_max']} metri, mentre l'altitudine minima è di {$tech['ele_min']} metri.
                Il percorso escursionistico ad anello è una bella opzione per chi vuole godersi una giornata in mezzo alla natura, senza dover fare ritorno al punto di partenza.
                Si consiglia di essere ben equipaggiati e preparati ad affrontare le diverse condizioni climatiche e i possibili ostacoli che potrebbero presentarsi lungo il percorso.")),
                'en' => trim(preg_replace('/\s\s+/', ' ', "The loop hiking trail $this->ref has its starting point and arrival in {$from['from']}, in the Municipality of {$from['city_from']}. According to the CAI standard, it is classified as {$cai_scale_string['it']} and covers a total distance of {$tech['distance']} kilometres.
                The total length of the trail is {$tech['distance']} kilometres, with significant unevenness.
                The altitude of the starting point is {$tech['ele_from']} metres and the maximum altitude reached is {$tech['ele_max']} metres, while the minimum altitude is {$tech['ele_min']} metres.
                The loop hiking trail is a good option for those who want to enjoy a day in the middle of nature, without having to return to the starting point.
                It is advisable to be well equipped and prepared to deal with the different weather conditions and possible obstacles that may arise along the way.")),
                'es' => trim(preg_replace('/\s\s+/', ' ', "La ruta de excursionismo circular $this->ref tiene su punto de partida y llegada en {$from['from']}, en el municipio de {$from['city_from']}. Según el estándar CAI, está clasificada como {$cai_scale_string['it']} y cubre una distancia total de {$tech['distance']} kilómetros.
                La longitud total del recorrido es de {$tech['distance']} kilómetros, con un desnivel significativo.
                La altitud del punto de partida es de {$tech['ele_from']} metros y la altitud máxima alcanzada es de {$tech['ele_max']} metros, mientras que la altitud mínima es de {$tech['ele_min']} metros.
                La ruta de excursionismo circular es una buena opción para quienes quieren disfrutar de un día en la naturaleza sin tener que añadir el regreso al punto de partida.
                Se recomienda equiparse y prepararse adecuadamente para hacer frente a las diferentes condiciones climáticas y a los posibles obstáculos que puedan surgir en el recorrido.")),
                'de' => trim(preg_replace('/\s\s+/', ' ', "Der Ringwanderweg $this->ref beginnt und endet in {$from['from']} in der Gemeinde {$from['city_from']}. Gemäß dem Standard des CAI ist er als {$cai_scale_string['it']} klassifiziert und deckt eine Gesamtdistanz von {$tech['distance']} Kilometern ab.
                Die Gesamtlänge der Strecke beträgt {$tech['distance']} Kilometer mit einem signifikanten Höhenunterschied.
                Die Höhe des Startpunkts beträgt {$tech['ele_from']} Meter und die maximal erreichte Höhe beträgt {$tech['ele_max']} Meter, während die Mindesthöhe {$tech['ele_min']} Meter beträgt.
                Der Rundwanderweg ist eine schöne Option für diejenigen, die einen Tag in der Natur genießen möchten, ohne zum Ausgangspunkt zurückkehren zu müssen.
                Es wird empfohlen, sich gut auszustatten, um den unterschiedlichen klimatischen Bedingungen und möglichen Hindernissen zu begegnen, die auf dem Weg auftreten können.")),
                'fr' => trim(preg_replace('/\s\s+/', ' ', "Le parcours de randonnée circulaire $this->ref a son point de départ et d'arrivée à {$from['from']}, dans la commune de {$from['city_from']}. Selon la norme CAI, il est classé comme {$cai_scale_string['it']} et couvre une distance totale de {$tech['distance']} kilomètres.
                La longueur totale du parcours est de {$tech['distance']} kilomètres, avec un dénivelé significatif.
                L'altitude du point de départ est de {$tech['ele_from']} mètres et l'altitude maximale atteinte est de {$tech['ele_max']} mètres, tandis que l'altitude minimale est de {$tech['ele_min']} mètres.
                Le parcours de randonnée circulaire est une option parfaite pour ceux qui veulent profiter d'une journée en pleine nature, sans avoir à prévoir un moyen de retour au point de départ.
                Il est recommandé d'être bien équipé et préparé pour faire face aux différentes conditions climatiques et aux obstacles potentiels qui pourraient se présenter le long du chemin.")),
                'pt' => trim(preg_replace('/\s\s+/', ' ', "O percursu excursionista em anel $this->ref tem o ponto de partida e chegada em {$from['from']}, no município de {$from['city_from']}. De acordo com o padrão CAI, é classificado como {$cai_scale_string['it']} e cobre uma distância total de {$tech['distance']} quilómetros.
                O comprimento total do percurso é de [distância] quilómetros, com um desnível significativo.
                A altitude do ponto de partida é de {$tech['ele_from']} metros e a altitude máxima atingida é de {$tech['ele_max']} metros, enquanto a altitude mínima é de {$tech['ele_min']} metros.
                O percurso excursionista em anel é uma boa opção para quem quer desfrutar de um dia no meio da natureza, sem ter de fazer o regresso ao ponto de partida.
                É aconselhável estar bem equipado e preparado para lidar com as diferentes condições climatéricas e possíveis obstáculos que possam surgir ao longo do caminho.")),
            ];
        }
        return $abstract;
    }

    /**
     * Ritorna i campi mancanti per le API del TDH
     *
     * @return array
     */
    public function computeTdh(): array
    {

        $fromInfo = $this->getFromInfo();
        $toInfo = $this->getToInfo();
        $techInfo = $this->getTechInfoFromGeohub();

        $tdh = [
            'gpx_url' => $techInfo['gpx_url'],
            'cai_scale_string' => $this->getCaiScaleString(),
            'cai_scale_description' => $this->getCaiScaleDescription(),
            'from' => $fromInfo['from'],
            'city_from' => $fromInfo['city_from'],
            'city_from_istat' => $fromInfo['city_from_istat'],
            'region_from' => $fromInfo['region_from'],
            'region_from_istat' => $fromInfo['region_from_istat'],
            'to' => $toInfo['to'],
            'city_to' => $toInfo['city_to'],
            'city_to_istat' => $toInfo['city_to_istat'],
            'region_to' => $toInfo['region_to'],
            'region_to_istat' => $toInfo['region_to_istat'],
            'roundtrip' => $this->checkRoundTripFromGeometry(),
            'abstract' => $this->getAbstract($fromInfo, $toInfo, $techInfo),
            'distance' => $techInfo['distance'],
            'ascent' => $techInfo['ascent'],
            'descent' => $techInfo['descent'],
            'duration_forward' => $techInfo['duration_forward'],
            'duration_backward' => $techInfo['duration_backward'],
            'ele_from' => $techInfo['ele_from'],
            'ele_to' => $techInfo['ele_to'],
            'ele_max' => $techInfo['ele_max'],
            'ele_min' => $techInfo['ele_min'],
        ];

        return $tdh;
    }

    /**
     * Ritorna il link alla edit della risorsa nova corrispondente
     * 
     * @return string
     */
    public function getNovaEditLink(): string
    {
        $link = '';
        if ($this->id > 0) {
            $link = url('/nova/resources/hiking-routes/' . $this->id . '/edit');
        }
        return $link;
    }
}
