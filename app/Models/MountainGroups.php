<?php

namespace App\Models;

use App\Models\Region;
use App\Traits\GeojsonableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MountainGroups extends Model
{
    use HasFactory,  GeojsonableTrait;

    public function regions()
    {
        return $this->belongsToMany(Region::class);
    }
}
