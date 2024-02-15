<?php

namespace App\Models;

use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CaiHuts extends Model
{
    use HasFactory;

    protected $fillable = ['unico_id', 'created_at', 'updated_at', 'name', 'second_name', 'description', 'elevation', 'owner', 'geometry', 'region_id'];

    protected static function booted()
    {
        // static::saved(function ($caiHut) {
        //     Artisan::call('osm2cai:add_cai_huts_to_hiking_routes CaiHuts ' . $caiHut->id);
        // });

        static::created(function ($caiHut) {
            Artisan::call('osm2cai:add_cai_huts_to_hiking_routes CaiHuts ' . $caiHut->id);
        });
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}