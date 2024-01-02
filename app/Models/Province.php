<?php

namespace App\Models;

use App\Traits\CsvableModelTrait;
use App\Traits\SallableTrait;
use App\Traits\OwnableModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends TerritorialUnit
{
    use HasFactory, SallableTrait, OwnableModelTrait, CsvableModelTrait;

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
    public function children()
    {
        return $this->areas();
    }
    /**
     * Alias
     */
    public function childrenIds()
    {
        return $this->areasIds();
    }
    /**
     * Alias
     */
    public function parent()
    {
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
            $query->whereHas('region', function ($eloquentBuilder) use ($user) {
                $eloquentBuilder->where('id', $user->region->id);
            });
            $hasUserPermission = true;
        }

        if ($user->provinces->count()) {

            if ($hasUserPermission) {
                $query->orWhereIn('id', $user->provinces->pluck('id'));
            } else {
                $query->whereIn('id', $user->provinces->pluck('id'));
            }
            $hasUserPermission = true;
        }


        return $query;
    }
}
