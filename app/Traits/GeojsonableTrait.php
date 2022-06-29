<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait GeojsonableTrait
{
    /**
     * Calculate the geojson of a model with only the geometry
     *
     * @return array
     */
    public function getEmptyGeojson(): ?array
    {
        $model = get_class($this);
        $obj = $model::where('id', '=', $this->id)
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first();

        if(is_null($obj)) {
            return null;
        }
        $geom = $obj->geom;

        if (isset($geom)) {
            return [
                "type" => "Feature",
                "properties" => [],
                "geometry" => json_decode($geom, true)
            ];
        } else
            return null;
    }

    /**
     * Calculate the geojson of a model with only the geometry
     *
     * @return array
     */
    public function getGeojsonForMapView(): ?array
    {
        $model = get_class($this);
        $obj = $model::where('id', '=', $this->id)
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first();

        if(is_null($obj)) {
            return null;
        }
        $geom = $obj->geom;

        $obj_raw_data = $model::where('id', '=', $this->id)
            ->select(
                DB::raw("ST_AsGeoJSON(geometry_raw_data) as geom_raw")
            )
            ->first();
        $geom_raw = $obj_raw_data->geom_raw;

        if (isset($geom_raw) && isset($geom)) {
            return [
                "type" => "FeatureCollection",
                "features" => [
                    0 => [
                        "type" => "Feature",
                        "properties" => [],
                        "geometry" => json_decode($geom, true),
                    ],
                    1 => [
                        "type" => "Feature",
                        "properties" => [],
                        "geometry" => json_decode($geom_raw, true),
                    ],
                ]
            ];
        }

        if (isset($geom)) {
            return [
                "type" => "Feature",
                "properties" => [],
                "geometry" => json_decode($geom, true)
            ];
        } else
            return null;
    }

    /**
     * SELECT ST_Asgeojson(ST_Centroid(geometry)) as geojson from XX where id=1;
     * geojson
     * {"type":"Point","coordinates":[11.165142884,43.974709689]}
     *
     * @return array|null
     */
    public function getCentroidGeojson(): ?array
    {
        $model = get_class($this);
        $obj = $model::where('id', '=', $this->id)
            ->select(
                DB::raw("ST_Asgeojson(ST_Centroid(geometry)) as geom")
            )
            ->first();

            if(is_null($obj)) {
                return null;
            }

        $geom = $obj->geom;
    
        if (isset($geom)) {
            return [
                "type" => "Feature",
                "properties" => [],
                "geometry" => json_decode($geom, true)
            ];
        } else
            return null;
    }

    /**
     * @return array|null
     */
    public function getCentroid(): ?array
    {
        $geojson = $this->getCentroidGeojson();
        if (!is_null($geojson)) {
            return $geojson['geometry']['coordinates'];
        }
        return null;
    }

}
