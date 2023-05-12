<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class HikingRouteTDHResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
            $geometry = DB::select("SELECT ST_AsGeoJSON('$this->geometry') As g")[0]->g;

            $geojson = [
                "type" => "Feature",
                "properties" => [
                    "id" => $this->id,
                    "created_at" => $this->created_at,
                    "updated_at" => $this->updated_at,
                    "osm2cai_status" => $this->osm2cai_status,
                    "validation_date" => $this->validation_date,
                    "relation_id" => $this->relation_id,
                    "ref" => $this->ref,
                    "ref_REI" => $this->ref_REI,
                    "gpx_url" => $this->tdh['gpx_url'],
                    "cai_scale" => $this->cai_scale,
                    "cai_scale_string" => $this->tdh['cai_scale_string'],
                    "cai_scale_description" => $this->tdh['cai_scale_description'],
                    "survey_date" => $this->survey_date,
                    "from" => $this->tdh['from'],
                    "city_from" => $this->tdh['city_from'],
                    "city_from_istat" => $this->tdh['city_from_istat'],
                    "region_from" => $this->tdh['region_from'],
                    "region_from_istat" => $this->tdh['region_from_istat'],
                    "to" => $this->tdh['to'],
                    "city_to" => $this->tdh['city_to'],
                    "city_to_istat" => $this->tdh['city_to_istat'],
                    "region_to" => $this->tdh['region_to'],
                    "region_to_istat" => $this->tdh['region_to_istat'],
                    "name" => $this->getNameForTDH(),
                    "roundtrip" => $this->tdh['roundtrip'],
                    "abstract" => $this->tdh['abstract'],
                    "distance" => $this->tdh['distance'],
                    "ascent" => $this->tdh['ascent'],
                    "descent" => $this->tdh['descent'],
                    "duration_forward" => $this->tdh['duration_forward'],
                    "duration_backward" => $this->tdh['duration_backward'],
                    "ele_from" => $this->tdh['ele_from'],
                    "ele_to" => $this->tdh['ele_to'],
                    "ele_max" => $this->tdh['ele_max'],
                    "ele_min" => $this->tdh['ele_min'],
                ],
                "geometry" => json_decode($geometry,true)
            ];

            return $geojson;

    }
}
