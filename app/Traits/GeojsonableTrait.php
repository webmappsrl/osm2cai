<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Symm\Gisconverter\Gisconverter;
use Symm\Gisconverter\Exceptions\InvalidText;

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

        if (is_null($obj)) {
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

        if (is_null($obj)) {
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

        if (is_null($obj)) {
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



    public function textToGeojson($text = '')
    {
        $geometry = $contentType = null;
        if ($text) {
            if (strpos($text, '<?xml') !== false && strpos($text, '<?xml') < 10) {
                $geojson = '';
                if ('' === $geojson) {
                    try {
                        $geojson = Gisconverter::gpxToGeojson($text);
                        $content = json_decode($geojson);
                        $contentType = @$content->type;
                    } catch (InvalidText $ec) {
                    }
                }

                if ('' === $geojson) {
                    try {
                        $geojson = Gisconverter::kmlToGeojson($text);
                        $content = json_decode($geojson);
                        $contentType = @$content->type;
                    } catch (InvalidText $ec) {
                    }
                }
            } else {
                $content = json_decode($text);
                $isJson = json_last_error() === JSON_ERROR_NONE;
                if ($isJson) {
                    $contentType = $content->type;
                }
            }

            if ($contentType) {
                switch ($contentType) {
                    case "GeometryCollection":
                        foreach ($content->geometries as $item) {
                            if ($item->type == 'LineString') {
                                $contentGeometry = $item;
                            }
                        }
                        break;
                    case "FeatureCollection":
                        $contentGeometry = $content->features[0]->geometry;
                        break;
                    case "LineString":
                        $contentGeometry = $content;
                        break;
                    default:
                        $contentGeometry = $content->geometry;
                        break;
                }

                $geometry = json_encode($contentGeometry);
            }
        }

        return $geometry;
    }



    /**
     * @param string json encoded geometry.
     */
    public function fileToGeometry($fileContent = '')
    {
        $geometry = null;
        $geojson = $this->textToGeojson($fileContent);
        if ( $geojson )
            $geometry = DB::select( DB::raw("select (ST_Force3D(ST_GeomFromGeoJSON('" . $geojson . "'))) as g "))[0]->g;

        return $geometry;
    }
}
