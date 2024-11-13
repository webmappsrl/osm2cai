<?php

namespace App\Models;

use App\Models\HikingRoute;
use App\Traits\SallableTrait;
use App\Traits\GeojsonableTrait;
use App\Traits\CsvableModelTrait;
use App\Traits\OwnableModelTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sector extends TerritorialUnit
{
    use HasFactory, SallableTrait, GeojsonableTrait, OwnableModelTrait, CsvableModelTrait;

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function sectorsIds(): array
    {
        return [$this->id];
    }

    public function hikingRoutes()
    {
        return $this->belongsToMany(HikingRoute::class);
    }

    /**
     * Alias
     */
    public function parent()
    {
        return $this->area();
    }

    /**
     * Alias
     */
    public function children()
    {
        return $this->hikingRoutes();
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
        $hasUserPermission = false;

        if ($user->region) {
            $query->whereHas('area.province.region', function ($eloquentBuilder) use ($user) {
                $eloquentBuilder->where('id', $user->region->id);
            });
            $hasUserPermission = true;
        }

        if ($user->provinces->count()) {
            if ($hasUserPermission) {
                $query->orWhereHas('area.province', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->provinces->pluck('id'));
                });
            } else {
                $query->whereHas('area.province', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->provinces->pluck('id'));
                });
            }

            $hasUserPermission = true;
        }

        if ($user->areas->count()) {

            if ($hasUserPermission) {
                $query->orWhereHas('area', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->areas->pluck('id'));
                });
            } else {
                $query->whereHas('area', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->areas->pluck('id'));
                });
            }

            $hasUserPermission = true;
        }

        if ($user->sectors->count()) {

            if ($hasUserPermission) {
                $query->whereIn('id', $user->sectors->pluck('id'));
            } else {
                $query->orWhereIn('id', $user->sectors->pluck('id'));
            }

            $hasUserPermission = true;
        }

        return $query;
    }

    public function calculateFullCode()
    {
        $area = \App\Models\Area::where('id', $this->area_id)->first();
        $this->name = $this->full_code = $area->name . $this->code;
        $this->saveQuietly();
    }

    public function getGeojsonComplete(): string
    {
        $g = [];
        $g['type'] = 'FeatureCollection';
        $features = [];
        $hikingRoutes = $this->hikingRoutes->whereIn('osm2cai_status', [1, 2, 3, 4]);
        if (count($hikingRoutes)) {
            foreach ($hikingRoutes as $hr) {
                $f = [];
                // Properties
                $p = [];
                $p['id'] = $hr->id;
                $p['created_at'] = $hr->created_at;
                $p['updated_at'] = $hr->updated_at;
                $p['osm2cai_status'] = $hr->osm2cai_status;
                $p['ref'] = $hr->ref;
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
}
