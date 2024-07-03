<?php

namespace App\Models;

use App\Models\Region;
use App\Traits\GeoIntersectTrait;
use App\Traits\GeojsonableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MountainGroups extends Model
{
    use HasFactory,  GeojsonableTrait, GeoIntersectTrait;

    protected $fillable = ['name', 'description', 'updated_at', 'geometry', 'hiking_routes_intersecting', 'huts_intersecting', 'sections_intersecting', 'ec_pois_intersecting', 'cached_mitur_api_data'];

    public function regions()
    {
        return $this->belongsToMany(Region::class, 'mountain_groups_region', 'mountain_group_id', 'region_id');
    }
}
