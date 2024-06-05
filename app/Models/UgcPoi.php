<?php

namespace App\Models;

use app\Traits\GeojsonableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UgcPoi extends Model
{
    use HasFactory, GeojsonableTrait;

    protected $fillable = ['geohub_id', 'name', 'description', 'geometry', 'user_id', 'updated_at', 'raw_data', 'taxonomy_wheres', 'form_id', 'user_no_match', 'flow_range_volume', 'flow_range_fill_time'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $rawData = json_decode($model->raw_data, true);
            $model->form_id = $rawData['id'] ?? null;
            $model->save();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ugc_media(): BelongsToMany
    {
        return $this->belongsToMany(UgcMedia::class);
    }

    /**
     * Return the json version of the ec track, avoiding the geometry
     * 
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

        return $array;
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
     * Get the natural springs data for nova resource
     * 
     * @return array
     */
    public function getNaturalSpringsData(): array
    {
        $data = [];
        $rawData = json_decode($this->raw_data, true);

        $volume = $rawData['range_volume'] ?? '0';
        $volume = preg_replace('/[^0-9,]/', '', $volume);
        $volume = str_replace(',', '.', $volume);
        $volume = floatval($volume);

        $time = $rawData['range_time'] ?? '0';
        $time = preg_replace('/[^0-9,]/', '', $time);
        $time = str_replace(',', '.', $time);
        $time = floatval($time);

        $waterFlowRange = ($time > 0 && $volume > 0) ? round(($volume / ($time * 60)), 4) : 'N/A';
        $conductivity = $rawData['conductivity'] ?? 'N/A';
        $temperature = $rawData['temperature'] ?? 'N/A';

        if (strpos($temperature, '°') === false && $temperature !== 'N/A') {
            $temperature .= '°';
        }
        $photos = !empty($rawData['storedPhotoKeys']) ? true : false;
        $date = $rawData['date'] ?? 'N/A';

        if ($date !== 'N/A') {
            $date = Carbon::parse($date);
        }

        $data['waterFlowRange'] = $waterFlowRange;
        $data['photos'] = $photos;
        $data['date'] = $date;

        //populate the table if empty
        if (!$this->flow_range_volume) {
            $this->flow_range_volume = $waterFlowRange == 'N/A' ? $waterFlowRange : round($waterFlowRange / $volume, 4);
        }

        if (!$this->flow_range_fill_time) {
            $this->flow_range_fill_time = $waterFlowRange == 'N/A' ? $waterFlowRange : round($waterFlowRange / $time, 4);
        }

        if (!$this->conductivity) {
            $this->conductivity = $conductivity;
        }

        if (!$this->temperature) {
            $this->temperature = $temperature;
        }

        $this->save();

        return $data;
    }
}
