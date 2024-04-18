<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
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

        $obj = $this->resource->select(DB::raw('ST_AsGeoJSON(geometry) As geom'))->first();

        if (!is_null($obj)) {
            $geom = $obj->geom;
            $result['geometry'] = json_decode($geom, true);
        }

        return $result;
    }
}