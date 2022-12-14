<?php

namespace App\Models;

use App\Services\GeometryService;
use App\Traits\GeojsonableTrait;
use App\Traits\OwnableModelTrait;
use GeoJson\Geometry\Polygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    protected $fillable = [
        'relation_id',
        'ref_osm', 'old_ref_osm', 'source_osm', 'source_ref_osm', 'survey_date_osm', 'name_osm', 'rwn_osm', 'rwn_name', 'ref_REI_osm',
        'tags_osm', 'geometry_osm',
        'cai_scale_osm', 'from_osm', 'to_osm', 'osmc_symbol_osm', 'network_osm', 'roundtrip_osm', 'symbol_osm', 'symbol_it_osm',
        'ascent_osm', 'descent_osm', 'distance_osm', 'duration_forward_osm', 'duration_backward_comp',
        'operator_osm', 'state_osm', 'description_osm', 'description_it_osm', 'website_osm', 'wikimedia_commons_osm', 'maintenance_osm', 'maintenance_it_osm', 'note_osm', 'note_it_osm', 'note_project_page_osm', 'geometry_raw_data', 'osm2cai_status'
    ];

    protected $casts = [
        'distance' => 'float',
        'distance_osm' => 'float',
        'distance_comp' => 'float',
        'validation_date' => 'datetime:Y-m-d H:i:s',
    ];

    public static array $info_fields = [
        'main' => [
            'cai_scale' => ['type' => 'string', 'comp' => false, 'label' => 'Diff. CAI'],
            'source' => ['type' => 'string', 'comp' => false, 'label' => 'Source'],
            'survey_date' => ['type' => 'string', 'comp' => false, 'label' => 'Data ricognizione'],
            'source_ref' => ['type' => 'string', 'comp' => false, 'label' => 'Codice Sezione CAI'],
            'old_ref' => ['type' => 'string', 'comp' => false, 'label' => 'REF precedente'],
            'ref_REI' => [ 'type' => 'string' , 'comp' => false, 'label' => 'REF rei']
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
            'ascent' => ['type' => 'float', 'comp' => true, 'label' => 'Dislivello positivo in metri'],
            'descent' => ['type' => 'float', 'comp' => true, 'label' => 'Dislivello negativo in metri'],
            'duration_forward' => ['type' => 'string', 'comp' => true, 'label' => 'Durata (P->A)'],
            'duration_backward' => ['type' => 'string', 'comp' => true, 'label' => 'Durata (A->P)'],
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
                if ( $this->id == 22289 )
                {
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

        $query = 'SELECT id
            FROM hiking_routes
            WHERE ST_intersects(' . $geometry_field . ',ST_GeomFromGeoJSON(\'' . json_encode($poly->jsonSerialize()) . '\')) AND
            osm2cai_status=' . $osm2cai_status;
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

    public function getPublicPage(){
        return url('/').'/hiking-route/id/'.$this->id;
    }

    public function revertValidation(){
        if($this->osm2cai_status == 4)
            $this->osm2cai_status = 3;
        $this->validation_date = null;
        $this->geometry_raw_data = null;
        $this->user_id = null;
        $this->save();
    }
}
