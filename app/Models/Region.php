<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends TerritorialUnit
{
    use HasFactory;

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }

    public function provincesIds(): array
    {
        return $this->provinces->pluck('id')->toArray();
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
}
