<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class SignUmapResource extends JsonResource
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
                'artifact_type' => $this->raw_data['artifact_type'] ?? '',
                'location' => $this->raw_data['location'] ?? '',
                'conservation_status' => $this->raw_data['conservation_status'] ?? '',
                'notes' => $this->raw_data['notes'] ?? '',
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
