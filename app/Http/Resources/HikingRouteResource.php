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

        $obj = $this->resource
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first();

        if (!is_null($obj)) {
            $geom = $obj->geom;
            $result['geometry'] = json_decode($geom, true);
        }

        $osmObj = $this->resource
            ->select(
                DB::raw("ST_AsGeoJSON(geometry_osm) as geom")
            )
            ->first();

        if (!is_null($osmObj)) {
            $osmGeom = $osmObj->geom;
            $result['geometry_osm'] = json_decode($osmGeom, true);
        }


        return $result;
    }
}