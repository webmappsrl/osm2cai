<?php

namespace App\Models;

use App\Contracts\OsmfeaturesEnricher;
use App\Models\EcPoi;
use App\Models\CaiHuts;
use App\Traits\SallableTrait;
use App\Models\MountainGroups;
use App\Traits\CsvableModelTrait;
use App\Traits\EnrichmentFromOsmfeaturesTrait;
use App\Traits\OwnableModelTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends TerritorialUnit
{
    use HasFactory, SallableTrait, OwnableModelTrait, CsvableModelTrait, EnrichmentFromOsmfeaturesTrait;

    protected $fillable = [
        'num_expected', 'name', 'code', 'geometry', 'updated_at', 'created_at', 'aggregated_data', 'osmfeatures_id', 'osmfeatures_data', 'cached_mitur_api_data'
    ];

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }

    public function provincesIds(): array
    {
        return $this->provinces->pluck('id')->toArray();
    }

    /**
     * Alias
     */
    public function children()
    {
        return $this->provinces();
    }
    /**
     * Alias
     */
    public function childrenIds()
    {
        return $this->provincesIds();
    }

    public function areasIds(): array
    {
        $result = [];
        foreach ($this->provinces as $province) {
            $result = array_unique(array_values(array_merge($result, $province->areasIds())));
        }

        return $result;
    }

    public function sectorsIds(): array
    {
        $result = [];
        foreach ($this->provinces as $province) {
            $result = array_unique(array_values(array_merge($result, $province->sectorsIds())));
        }

        return $result;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function hikingRoutes()
    {
        return $this->belongsToMany(HikingRoute::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function ecPois()
    {
        return $this->hasMany(EcPoi::class);
    }

    public function mountainGroups()
    {
        return $this->belongsToMany(MountainGroups::class, 'mountain_groups_region', 'region_id', 'mountain_group_id');
    }

    public function caiHuts()
    {
        return $this->hasMany(CaiHuts::class);
    }

    public function getGeojsonComplete(): string
    {
        $g = [];
        $g['type'] = 'FeatureCollection';
        $features = [];
        $hikingRoutes = $this->hikingRoutes->whereIn('osm2cai_status', [1, 2, 3, 4]);
        if (count($hikingRoutes)) {
            foreach ($hikingRoutes as $hr) {
                $sectors = $hr->sectors;
                $f = [];
                // Properties
                $p = [];
                $p['id'] = $hr->id;
                $p['created_at'] = $hr->created_at;
                $p['updated_at'] = $hr->updated_at;
                $p['osm2cai_status'] = $hr->osm2cai_status;
                $p['ref'] = $hr->ref;
                $p['sectors'] = $sectors->pluck('name')->toArray();
                $p['source_ref'] = $hr->source_ref;
                $p['cai_scale'] = $hr->cai_scale;
                $p['distance'] = $hr->distance_comp;
                $p['ref_REI'] = $hr->ref_REI;
                $p['ref_REI_computed'] = $hr->ref_REI_comp;
                $p['accessibility'] = $hr->issues_status;
                $p['osm_id'] = $hr->relation_id;
                $p['osm2cai'] = $hr->getNovaEditLink();
                $p['survey_date'] = $hr->survey_date;
                $p['old_ref'] = $hr->old_ref;
                $p['from'] = $hr->from;
                $p['to'] = $hr->to;
                $p['name'] = $hr->name;
                $p['roundtrip'] = $hr->rounftrip;
                $p['duration_forward'] = $hr->duration_forward;
                $p['duration_backword'] = $hr->duration_backword;
                $p['ascent'] = $hr->ascent;
                $p['descent'] = $hr->descent;


                // Geometry
                $geom_s = HikingRoute::where('id', '=', $hr->id)
                    ->select(
                        DB::raw("ST_AsGeoJSON(geometry) as geom")
                    )
                    ->first()
                    ->geom;
                $geom = json_decode($geom_s, TRUE);

                // Build item
                $f['type'] = 'Feature';
                $f['properties'] = $p;
                $f['geometry'] = $geom;
                $features[] = $f;
            }
        }
        $g['features'] = $features;
        return json_encode($g);
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
        $userModelId = $user->region ? $user->region->id : 0;
        return $query->where('id', $userModelId);
    }
}