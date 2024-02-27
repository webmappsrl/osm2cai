<?php

namespace App\Models;

use App\Models\Region;
use App\Traits\GeojsonableTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EcPoi extends Model
{
    use HasFactory, GeojsonableTrait;

    protected $fillable = ['name', 'description', 'geometry', 'user_id', 'tags', 'type', 'osm_id', 'osm_type', 'region_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function toGeoJson()
    {

        $obj = $this->select(DB::raw("ST_AsGeoJSON(geometry) as geom"))->first();

        if (is_null($obj)) {
            return null;
        }
        $geometry = json_decode($obj->geom, true);


        return [
            'type' => 'Feature',
            'properties' => [
                'name' => $this->name,
                'description' => $this->description,
                'tags' => $this->tags,
                'type' => $this->type,
                'osm_id' => $this->osm_id,
                'osm_type' => $this->osm_type,
            ],
            'geometry' => $geometry,
        ];
    }
}
