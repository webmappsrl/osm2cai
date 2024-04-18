<?php

namespace App\Http\Resources;

use App\Models\HikingRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class HikingRouteResource extends JsonResource
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
                DB::raw('SELECT ST_AsGeoJSON(geometry) As geom FROM hiking_routes WHERE id = :id'),
                ['id' => $this->resource->id]
            )[0]->geom;

            $result['geometry'] = json_decode($geom, true);
        }

        if ($this->resource->geometry_osm) {
            $geom = DB::select(
                DB::raw('SELECT ST_AsGeoJSON(geometry_osm) As geom FROM hiking_routes WHERE id = :id'),
                ['id' => $this->resource->id]
            )[0]->geom;

            $result['geometry_osm'] = json_decode($geom, true);
        }
        if ($this->resource->geometry_raw_data) {
            $geom = DB::select(
                DB::raw('SELECT ST_AsGeoJSON(geometry_raw_data) As geom FROM hiking_routes WHERE id = :id'),
                ['id' => $this->resource->id]
            )[0]->geom;

            $result['geometry_raw_data'] = json_decode($geom, true);
        }

        $result['natural_springs'] = json_decode($this->resource->natural_springs, true);
        return $result;
    }
}