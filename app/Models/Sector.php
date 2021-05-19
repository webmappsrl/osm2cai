<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sector extends TerritorialUnit
{
    use HasFactory;

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
        return $this->belongsToMany(HikingRoutes::class);
    }

}
