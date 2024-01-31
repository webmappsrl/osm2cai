<?php

namespace App\Http\Controllers;

use App\Models\EcPoi;
use App\Models\HikingRoute;
use Illuminate\Http\Request;

class EcPoiController extends Controller
{


    public function ecPoisBBox($bounding_box, $type)
    {
        // $bounding_box should be in 'minLng,minLat,maxLng,maxLat' format
        [$minLng, $minLat, $maxLng, $maxLat] = explode(',', $bounding_box);
        $type = strtoupper($type);


        $pois = EcPoi::whereRaw(
            "
        ST_Within(geometry::geometry, ST_MakeEnvelope(?, ?, ?, ?, 4326)) 
        AND type = ?",
            [$minLng, $minLat, $maxLng, $maxLat, $type]
        )->where('osm_type', $type)->get();

        $pois = $pois->mapWithKeys(function ($item) {
            return [$item['id'] => $item['updated_at']];
        });

        return response()->json($pois);
    }

    public function ecPoisByOsm2CaiId($hr_osm2cai_id, $type)
    {
        $route = HikingRoute::where('id', $hr_osm2cai_id)->firstOrFail();

        $pois = \App\Models\EcPoi::whereRaw(
            "ST_DWithin(geometry, ST_GeomFromEWKB(?::geometry), 1000)",
            [$route->geometry]
        )->get();

        $pois = collect($pois)->mapWithKeys(function ($item) {
            return [$item['id'] => $item['updated_at']];
        });

        return response()->json($pois);
    }

    public function ecPoisByOsmId($hr_osm_id, $type)
    {
        $route = HikingRoute::where('relation_id', $hr_osm_id)->firstOrFail();

        $pois = \App\Models\EcPoi::whereRaw(
            "ST_DWithin(geometry, ST_GeomFromEWKB(?::geometry), 1000)",
            [$route->geometry]
        )->get();
        $pois = collect($pois)->mapWithKeys(function ($item) {
            return [$item['id'] => $item['updated_at']];
        });

        return response()->json($pois);
    }
}
