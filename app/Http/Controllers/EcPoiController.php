<?php

namespace App\Http\Controllers;

use App\Models\EcPoi;
use App\Models\HikingRoute;
use Illuminate\Http\Request;

class EcPoiController extends Controller
{

    /**
     * @OA\Get(
     *     path="/ecpois/bb/{bounding_box}/{type}",
     *     tags={"Api V2"},
     *     summary="Get Ec POIs by Bounding Box and Type",
     *     description="Returns a list of Eco POIs within the specified bounding box and of the specified type",
     *     @OA\Parameter(
     *         name="bounding_box",
     *         in="path",
     *         required=true,
     *         description="Bounding box in 'minLng,minLat,maxLng,maxLat' format",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Type of the POIs to retrieve: R for relation, W for way, N for node",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\AdditionalProperties(
     *                 type="string",
     *                 format="date-time"
     *             )
     *      
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/ecpois/{hr_osm2cai_id}/{type}",
     *     tags={"Api V2"},
     *     summary="Get EcPOIs in a 1km buffer from the HikingRoutes defined by ID",
     *     description="Returns a list of Ec POIs around 1km from a specific OSM2CAI hiking route ID and of a specified type",
     *     @OA\Parameter(
     *         name="hr_osm2cai_id",
     *         in="path",
     *         required=true,
     *         description="OSM2CAI ID of the hiking route",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Type of the POIs to retrieve",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\AdditionalProperties(
     *                 type="string",
     *                 format="date-time"
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/ecpois/{hr_osm_id}/{type}",
     *     tags={"Api V2"},
     *     summary="Get EcPOIs in a 1km buffer from the HikingRoutes defined by OSM ID",
     *     description="Returns a list of Ec POIs associated with a specific OpenStreetMap hiking route ID and of a specified type",
     *     @OA\Parameter(
     *         name="hr_osm_id",
     *         in="path",
     *         required=true,
     *         description="OSM ID of the hiking route",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         description="Type of the POIs to retrieve",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\AdditionalProperties(
     *                 type="string",
     *                 format="date-time"
     *             )
     *         )
     *     )
     * )
     */
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
