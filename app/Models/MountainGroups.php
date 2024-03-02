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

    public function regions()
    {
        return $this->belongsToMany(Region::class, 'mountain_groups_region', 'mountain_group_id', 'region_id');
    }
}
