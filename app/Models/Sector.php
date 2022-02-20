<?php

namespace App\Models;

use App\Traits\GeojsonableTrait;
use App\Traits\SallableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sector extends TerritorialUnit
{
    use HasFactory, SallableTrait, GeojsonableTrait;

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

}
