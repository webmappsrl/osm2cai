<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    public function province()
    {
        return $this->hasOne(Province::class);
    }

    public function sectors()
    {
        return $this->belongsToMany(Sector::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
