<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'region_id' => 'integer',
        'name' => 'string',
        'cai_code' => 'string',
    ];

    protected $fillable = [
        'id',
        'region_id',
        'name',
        'cai_code',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function hikingRoutes()
    {
        return $this->belongsToMany(HikingRoute::class, 'hiking_route_section');
    }
}
