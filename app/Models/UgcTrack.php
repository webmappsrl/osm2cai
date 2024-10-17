<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use App\Traits\GeojsonableTrait;
use App\Traits\WmNovaFieldsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UgcTrack extends Model
{
    use HasFactory,
        GeojsonableTrait,
        WmNovaFieldsTrait;

    protected $fillable = ['geohub_id', 'name', 'description', 'geometry', 'user_id', 'updated_at', 'raw_data', 'taxonomy_wheres', 'metadata', 'app_id'];

    protected $casts = [
        'raw_data' => 'array',
        'validation_date' => 'datetime',
        'raw_data->date' => 'datetime:Y-m-d H:i:s'
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->user_id = auth()->id() ?? $model->user_id;
            $model->app_id = $model->app_id ?? 'osm2cai';
            $model->save();
        });
    }

    public function getRegisteredAtAttribute()
    {
        return isset($this->raw_data['date'])
            ? Carbon::parse($this->raw_data['date'])
            : $this->created_at;
    }

    public function setGeometryAttribute($value)
    {
        if (!is_string($value)) {
            $this->attributes['geometry'] = json_encode($value);
        } else {
            $this->attributes['geometry'] = $value;
        }
    }

    public function ugc_media(): BelongsToMany
    {
        return $this->belongsToMany(UgcMedia::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a geojson from the ec track
     *
     * @return array
     */
    public function getGeojson(): ?array
    {
        $feature = $this->getEmptyGeojson();
        if (isset($feature["properties"])) {
            $feature["properties"] = $this->getJson();
            return $feature;
        } else return null;
    }

    /**
     * Return the json version of the ec track, avoiding the geometry
     *
     * @return array
     */
    public function getJson(): array
    {
        $array = $this->toArray();

        $propertiesToClear = ['geometry'];
        foreach ($array as $property => $value) {
            if (is_null($value) || in_array($property, $propertiesToClear))
                unset($array[$property]);
        }

        if (isset($array['raw_data'])) {
            $array['raw_data']  = json_encode($array['raw_data']);
        }

        return $array;
    }
}
