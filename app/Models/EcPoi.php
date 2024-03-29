<?php

namespace App\Models;

use App\Models\Region;
use App\Traits\GeojsonableTrait;
use App\Traits\TagsMappingTrait;
use App\Traits\GeoIntersectTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Artisan;

class EcPoi extends Model
{
    use HasFactory, GeojsonableTrait, GeoIntersectTrait, TagsMappingTrait;

    protected $fillable = ['name', 'description', 'geometry', 'user_id', 'tags', 'type', 'osm_id', 'osm_type', 'region_id', 'score'];

    protected static function booted()
    {
        parent::booted();

        static::created(function ($ecPoi) {
            Artisan::call('osm2cai:update-ec-pois-score', ['id' => $ecPoi->id]);
        });
    }

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

    /**
     * Get the score of the POI and render it as stars svg
     * 
     * @return string
     */
    public function getScoreStars(): string
    {
        $score = $this->score;
        $stars = '';

        for ($i = 0; $i < $score; $i++) {
            $stars .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 12.794l-5.225 3.388 1.26-6.978-4.465-4.35 6.21-.906L10 1.106l2.22 4.844 6.21.906-4.465 4.35 1.26 6.978z" clip-rule="evenodd" />
            </svg>';
        }

        return $stars;
    }
}
