<?php

namespace App\Models;

use App\Models\User;
use App\Traits\CsvableModelTrait;
use App\Traits\GeoIntersectTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory, CsvableModelTrait, GeoIntersectTrait;

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
        'geometry',
        'addr:city',
        'addr:street',
        'addr:housenumber',
        'addr:postcode',
        'website',
        'phone',
        'email',
        'opening_hours',
        'wheelchair',
        'fax',
        'cached_mitur_api_data',
        'section_manager_id',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function hikingRoutes()
    {
        return $this->belongsToMany(HikingRoute::class, 'hiking_route_section');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sectionManager()
    {
        return $this->hasOne(User::class, 'manager_section_id');
    }
}
