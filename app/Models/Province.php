<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends TerritorialUnit {
    use HasFactory;

    public function region() {
        return $this->belongsTo(Region::class);
    }

    public function areas() {
        return $this->hasMany(Area::class);
    }

    public function areasIds(): array {
        return $this->areas->pluck('id')->toArray();
    }

    public function sectorsIds(): array {
        $result = [];
        foreach ($this->areas as $area) {
            $result = array_unique(array_values(array_merge($result, $area->sectorsIds())));
        }

        return $result;
    }

    public function users() {
        return $this->belongsToMany(User::class);
    }
}
