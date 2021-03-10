<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    public function provinces()
    {
        return $this->hasMany(Province::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
