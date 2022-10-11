<?php

namespace App\Models;

use App\Traits\SallableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends TerritorialUnit
{
    use HasFactory, SallableTrait;

    protected $fillable = [
        'num_expected',
    ];


    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function areasIds(): array
    {
        return $this->areas->pluck('id')->toArray();
    }

    /**
     * Alias
     */
    public function children(){
        return $this->areas();
    }
    /**
     * Alias
     */
    public function childrenIds() {
        return $this->areasIds();
    }
    /**
     * Alias
     */
    public function parent(){
        return $this->region();
    }


    public function sectorsIds(): array
    {
        $result = [];
        foreach ($this->areas as $area) {
            $result = array_unique(array_values(array_merge($result, $area->sectorsIds())));
        }

        return $result;
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function hikingRoutes()
    {
        return $this->belongsToMany(HikingRoute::class);
    }

}
