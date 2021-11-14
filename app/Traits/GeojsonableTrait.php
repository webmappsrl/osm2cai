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
        $geom = $model::where('id', '=', $this->id)
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first()
            ->geom;

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
        $geom = $model::where('id', '=', $this->id)
            ->select(
                DB::raw("ST_Asgeojson(ST_Centroid(geometry)) as geom")
            )
            ->first()
            ->geom;

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
