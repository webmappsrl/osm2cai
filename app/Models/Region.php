<?php

namespace App\Models;

use App\Traits\SallableTrait;
use App\Traits\CsvableModelTrait;
use App\Traits\OwnableModelTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends TerritorialUnit
{
    use HasFactory, SallableTrait, OwnableModelTrait, CsvableModelTrait;

    protected $fillable = [
        'num_expected',
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
    public function children(){
        return $this->provinces();
    }
    /**
     * Alias
     */
    public function childrenIds() {
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

    public function getGeojsonComplete(): string {
        $g = [];
        $g['type']='FeatureCollection';
        $features=[];
        if (count($this->hikingRoutes->whereIn('osm2cai_status', [1, 2, 3, 4]))) {
            foreach ($this->hikingRoutes->whereIn('osm2cai_status', [1, 2, 3, 4]) as $hr) {
                $f=[];
                // Properties
                $p=[];
                $p['id'] = $hr->id;
                $p['created_at'] = $hr->created_at;
                $p['updated_at'] = $hr->updated_at;
                $p['relation_id'] = $hr->relation_id;
                $p['osm2cai_status'] = $hr->osm2cai_status;
                $p['ref'] = $hr->ref;
                $p['cai_scale'] = $hr->cai_scale;
                $p['distance'] = $hr->distance_comp;
                $p['ref_REI'] = $hr->ref_REI;
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
                $geom=json_decode($geom_s,TRUE);

                // Build item
                $f['type']='Feature';
                $f['properties']=$p;
                $f['geometry']=$geom;
                $features[]=$f;
            }
        }
        $g['features']=$features;
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
        $userModelIds = $user->region->pluck('id');
        return $query->whereIn('id', $userModelIds);
    }

}
