<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    use HasFactory;

    public function hikingRoutes()
    {
        return $this->belongsToMany(HikingRoute::class);
    }
}
