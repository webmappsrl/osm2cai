<?php

namespace App\Models;

use App\Models\Region;
use App\Traits\GeoIntersectTrait;
use Illuminate\Support\Facades\Log;
use App\Contracts\OsmfeaturesEnricher;
use App\Traits\EnrichmentFromOsmfeaturesTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CaiHuts extends Model
{
    use HasFactory, GeoIntersectTrait, EnrichmentFromOsmfeaturesTrait;

    protected $fillable = [
        'unico_id', 'created_at', 'updated_at',
        'name', 'second_name', 'description', 'elevation', 'owner', '
    geometry', 'region_id', 'addr_city', 'addr_street', 'addr_housenumber',
        'addr_postcode', 'website', 'phone', 'email', 'fax', 'ref_vatin', 'email_pec',
        'facebook_contact', 'municipality_geo', 'province_geo', 'site_geo', 'type',
        'type_custodial', 'company_management_property', 'cached_mitur_api_data', 'osmfeatures_id', 'osmfeatures_data'
    ];

    protected static function booted()
    {
        // static::saved(function ($caiHut) {
        //     Artisan::call('osm2cai:add_cai_huts_to_hiking_routes CaiHuts ' . $caiHut->id);
        // });

        static::created(function ($caiHut) {
            Artisan::call('osm2cai:add_cai_huts_to_hiking_routes', ['model' => 'CaiHuts', 'id' => $caiHut->id]);
        });
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}