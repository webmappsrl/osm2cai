<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Class HikingRoute
 * @package App\Models
 * @property int id
 * @property float distance_comp
 * @property geometry geometry
 * @property geometry geometry_osm
 */
class HikingRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'relation_id',
        'ref_osm', 'old_ref_osm', 'source_osm', 'source_ref_osm', 'survey_date_osm', 'name_osm', 'rwn_osm', 'rwn_name', 'ref_REI_osm',
        'tags_osm', 'geometry_osm',
        'cai_scale_osm', 'from_osm', 'to_osm', 'osmc_symbol_osm', 'network_osm', 'roundtrip_osm', 'symbol_osm', 'symbol_it_osm',
        'ascent_osm', 'descent_osm', 'distance_osm', 'duration_forward_osm', 'duration_backward_comp',
        'operator_osm', 'state_osm', 'description_osm', 'description_it_osm', 'website_osm', 'wikimedia_commons_osm', 'maintenance_osm', 'maintenance_it_osm', 'note_osm', 'note_it_osm', 'note_project_page_osm'
    ];

    public function validator()
    {
        return $this->belongsTo(User::class);
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
        return $this->belongsToMany(Sector::class);
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
     * 2: cai_scale null, source=survey:cai not null
     * 3: cai_scale not null, source=survey:cai not null
     * 4: validation_date not_null
     */
    public function setOsm2CaiStatus(): void
    {
        if ($this->validated()) {
            $status = 4;
        } else if (is_null($this->cai_scale_osm) && $this->source_osm != 'survey:CAI') {
            $status = 0;
        } else if (!is_null($this->cai_scale_osm) && $this->source_osm != 'survey:CAI') {
            $status = 1;
        } else if (is_null($this->cai_scale_osm) && $this->source_osm == 'survey:CAI') {
            $status = 2;
        } else if (!is_null($this->cai_scale_osm) && $this->source_osm == 'survey:CAI') {
            $status = 3;
        }
        $this->osm2cai_status = $status;
    }

    /**
     * This method compute and set tech info (distance_comp, ascent_comp, descent_comp, duration_forward_comp, duration_backward_comp)
     * from geometry: geometry_cai if geometry_osm is not present, geometry_osm if it is present.
     * If HikingRoute ha no geometry nothing is done.
     */
    public function computeAndSetTechInfo(): void
    {
        if (is_null($this->geometry_osm) && is_null($this->geometry)) {
            return;
        } else {
            if (!is_null($this->geometry)) {
                // Compute from CAI geometry
                // Distance
                $this->distance_comp = DB::table('hiking_routes')
                    ->selectRaw('ST_length(geometry,true) as length')
                    ->find($this->id)->length;
            } else {
                // Compute from OSM geometry
                // Distance
                $this->distance_comp = DB::table('hiking_routes')
                    ->selectRaw('ST_length(geometry_osm,true) as length')
                    ->find($this->id)->length;
            }
        }
    }

    /**
     * Check if Hiking Route has geometry
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
     * Compute and Associate all Territorial Units
     */
    public function computeAndSetTerritorialUnits(): void
    {

        $this->computeAndSetSectors();
        $this->computeAndSetAreas();
        $this->computeAndSetProvinces();
        $this->computeAndSetRegions();
    }

}
