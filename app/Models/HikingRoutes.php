<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HikingRoutes extends Model
{
    use HasFactory;

    public function validator() {
        return $this->belongsTo(User::class);
    }
    public function regions() {
        return $this->hasMany(Region::class);
    }
    public function provinces() {
        return $this->hasMany(Province::class);
    }
    public function areas() {
        return $this->hasMany(Area::class);
    }
    public function sectors() {
        return $this->hasMany(Sector::class);
    }

}
