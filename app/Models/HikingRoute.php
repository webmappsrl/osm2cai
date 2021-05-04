<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class HikingRoute
 * @package App\Models
 * @property int id
 * @property float distance_comp
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
        return $this->hasMany(Region::class);
    }

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function sectors()
    {
        return $this->hasMany(Sector::class);
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
        } else if (is_null($this->cai_scale_osm) && is_null($this->source_osm)) {
            $status = 0;
        } else if (!is_null($this->cai_scale_osm) && is_null($this->source_osm)) {
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
}
