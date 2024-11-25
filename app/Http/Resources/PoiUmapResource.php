<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class PoiUmapResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'type' => 'Feature',
            'geometry' => $this->geometry ? json_decode(DB::select("SELECT ST_AsGeoJSON('$this->geometry')")[0]->st_asgeojson, true) : null,
            'properties' => [
                'title' => $this->name ?? $this->raw_data['title'] ?? '',
                'description' => $this->raw_data['description'] ?? $this->description ?? '',
                'waypointtype' => $this->raw_data['waypointtype'] ?? '',
                'validation_status' => $this->validated ?? '',
                'osm2cai_link' => url('resources/ugc-pois/' . $this->osm_id),
                'images' => $this->ugc_media->map(function ($image) {
                    $url = $image->getUrl();
                    if (strpos($url, 'http') === false) {
                        $url = Storage::disk('public')->url($url);
                    }
                    return $url;
                }),
            ],
        ];
    }
}
