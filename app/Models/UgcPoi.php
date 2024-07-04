<?php

namespace App\Models;

use Carbon\Carbon;
use app\Traits\GeojsonableTrait;
use Illuminate\Database\Eloquent\Model;
use App\Enums\UgcWaterFlowValidatedStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UgcPoi extends Model
{
    use HasFactory, GeojsonableTrait;

    protected $fillable = ['geohub_id', 'name', 'description', 'geometry', 'user_id', 'updated_at', 'raw_data', 'taxonomy_wheres', 'form_id', 'user_no_match', 'flow_rate_volume', 'flow_rate_fill_time', 'has_photo'];

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

    public function calculateFlowRate()
    {
        if ($this->water_flow_rate_validated === UgcWaterFlowValidatedStatus::Valid) {
            //extract values and replace comma with dot. if dot is found, do not replace. the fina result should be a float value with point
            if (strpos($this->flow_rate_volume, '.') !== false) {
                $volume = $this->flow_rate_volume;
            } else {
                $volume = preg_replace('/[^0-9,]/', '', $this->flow_rate_volume);
            }
            if (strpos($this->flow_rate_fill_time, '.') !== false) {
                $time = $this->flow_rate_fill_time;
            } else {
                $time = preg_replace('/[^0-9,]/', '', $this->flow_rate_fill_time);
            }
            $volume = str_replace(',', '.', $volume);
            $time = str_replace(',', '.', $time);

            if (is_numeric($volume) && is_numeric($time) && $time != 0) {
                $this->flow_rate = round($volume / $time, 3);
                $this->save();
            } else {
                $this->flow_rate = 'N/A';
                $this->save();
            }

            return $this->flow_rate;
        } else {
            $this->flow_rate = 'N/A';
            $this->save();
            return $this->flow_rate;
        }
    }
}