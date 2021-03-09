<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    public function region()
    {
        return $this->hasOne(Region::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
