<?php

namespace App\Models;

use App\Traits\CsvableModelTrait;
use App\Traits\SallableTrait;
use App\Traits\GeojsonableTrait;
use App\Traits\OwnableModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sector extends TerritorialUnit
{
    use HasFactory, SallableTrait, GeojsonableTrait, OwnableModelTrait, CsvableModelTrait;

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

    /**
     * Alias
     */
    public function parent()
    {
        return $this->area();
    }

    /**
     * Alias
     */
    public function children()
    {
        return $this->hikingRoutes();
    }

    /**
     * Scope a query to only include models owned by a certain user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Model\User  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOwnedBy($query, User $user)
    {
        $hasUserPermission = false;

        if ($user->region) {
            $query->whereHas('area.province.region', function ($eloquentBuilder) use ($user) {
                $eloquentBuilder->where('id', $user->region->id);
            });
            $hasUserPermission = true;
        }

        if ($user->provinces->count()) {
            if ($hasUserPermission) {
                $query->orWhereHas('area.province', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->provinces->pluck('id'));
                });
            } else {
                $query->whereHas('area.province', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->provinces->pluck('id'));
                });
            }

            $hasUserPermission = true;
        }

        if ($user->areas->count()) {

            if ($hasUserPermission) {
                $query->orWhereHas('area', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->areas->pluck('id'));
                });
            } else {
                $query->whereHas('area', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->areas->pluck('id'));
                });
            }

            $hasUserPermission = true;
        }

        if ($user->sectors->count()) {

            if ($hasUserPermission) {
                $query->whereIn('id', $user->sectors->pluck('id'));
            } else {
                $query->orWhereIn('id', $user->sectors->pluck('id'));
            }

            $hasUserPermission = true;
        }

        return $query;
    }

    public function calculateFullCode(){
        $area = \App\Models\Area::where('id',$this->area_id)->first();
        $this->full_code = $this->code.$area->name;
        $this->saveQuietly();
    }




}
