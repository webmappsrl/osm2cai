<?php

namespace App\Models;

use App\Traits\GeojsonableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcPoi extends Model
{
    use HasFactory, GeojsonableTrait;

    protected $fillable = ['name', 'description', 'geometry', 'user_id', 'tags', 'type', 'osm_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
