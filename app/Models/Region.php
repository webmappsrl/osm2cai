<?php

namespace App\Models;

use App\Traits\SallableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Region extends TerritorialUnit
{
    use HasFactory, SallableTrait;

    protected $fillable = [
        'num_expected',
    ];

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

    /**
     * osm2cai.0.1.01.13 - Come Lorenzo Monelli, voglio che nella dashboard ci sia la possibilità di scaricare uno file .csv contenente le lista dei percorsi della mia regione con i seguenti
     * ref:REI
     * osm id
     * timestamp (?)
     * user (?)
     * survey:date
     * from
     * to
     * cai_scale
     * osmc:symbol
     * ref
     * name
     * network
     * source
     * @return string
     */
    public function getCsv(): string
    {
        $line = 'sda,settore,ref,from,to,difficoltà,codice rei,osm,osm2cai' . PHP_EOL;
        if (count($this->hikingRoutes->whereIn('osm2cai_status', [1, 2, 3, 4]))) {
            foreach ($this->hikingRoutes->whereIn('osm2cai_status', [1, 2, 3, 4]) as $hr) {
                $line .= $hr->osm2cai_status . ',';
                $line .= $hr->mainSector()->full_code . ',';
                $line .= $hr->ref . ',';
                $line .= $hr->from . ',';
                $line .= $hr->to . ',';
                $line .= $hr->cai_scale . ',';
                $line .= $hr->ref_REI_comp . ',';
                $line .= $hr->relation_id . ',';
                $line .= url('/resources/hiking-routes/' . $hr->id);
                $line .= PHP_EOL;
            }
        }
        return $line;
    }
}
