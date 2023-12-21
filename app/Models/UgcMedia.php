<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UgcMedia extends Model
{
    use HasFactory;

    protected $fillable = ['geohub_id', 'name', 'description', 'geometry', 'user_id', 'updated_at', 'raw_data', 'taxonomy_wheres', 'relative_url'];

    public function ugc_pois(): BelongsToMany
    {
        return $this->belongsToMany(UgcPoi::class);
    }

    public function ugc_tracks(): BelongsToMany
    {
        return $this->belongsToMany(UgcTrack::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
