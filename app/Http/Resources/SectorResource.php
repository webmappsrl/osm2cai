<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class SectorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $result = parent::toArray($request);

        if ($this->resource->geometry) {
            $geom = DB::select(
                DB::raw('SELECT ST_AsGeoJSON(geometry) As geom FROM sectors WHERE id = :id'),
                ['id' => $this->resource->id]
            )[0]->geom;

            $result['geometry'] = json_decode($geom, true);
        }

        return $result;
    }
}