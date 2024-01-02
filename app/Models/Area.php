<?php

namespace App\Models;

use App\Traits\CsvableModelTrait;
use App\Traits\SallableTrait;
use App\Traits\OwnableModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends TerritorialUnit
{
    use HasFactory, SallableTrait, OwnableModelTrait, CsvableModelTrait;

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function sectors()
    {
        return $this->hasMany(Sector::class);
    }

    public function sectorsIds(): array
    {
        return $this->sectors->pluck('id')->toArray();
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function hikingRoutes()
    {
        return $this->belongsToMany(HikingRoute::class);
    }

    /**
     * Alias
     */
    public function children()
    {
        return $this->sectors();
    }
    /**
     * Alias
     */
    public function childrenIds()
    {
        return $this->sectorsIds();
    }
    /**
     * Alias
     */
    public function parent()
    {
        return $this->province();
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
            $query->whereHas('province.region', function ($eloquentBuilder) use ($user) {
                $eloquentBuilder->where('id', $user->region->id);
            });
            $hasUserPermission = true;
        }

        if ($user->provinces->count()) {

            if ($hasUserPermission) {
                $query->orWhereHas('provinces', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->provinces->pluck('id'));
                });
            } else {
                $query->whereHas('provinces', function ($eloquentBuilder) use ($user) {
                    $eloquentBuilder->whereIn('id', $user->provinces->pluck('id'));
                });
            }

            $hasUserPermission = true;
        }

        if ($user->areas->count()) {
            if ($hasUserPermission) {
                $query->orWhereIn('id', $user->areas->pluck('id'));
            } else {
                $query->whereIn('id', $user->areas->pluck('id'));
            }
            $hasUserPermission = true;
        }

        return $query;
    }
}
