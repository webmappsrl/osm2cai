<?php

namespace App\Http\Controllers\V2;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class MiturAbruzzoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/mitur_abruzzo/region_list",
     *     tags={"Api V2 - MITUR Abruzzo"},
     *     summary="List all regions",
     *     description="Returns a list of all regions with their ID and last updated timestamp.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     description="The region ID"
     *                 ),
     *                 @OA\Property(
     *                     property="updated_at",
     *                     type="string",
     *                     format="date-time",
     *                     description="Last update timestamp of the region"
     *                 ),
     *                 example={12:"2022-12-03 12:34:25",6:"2022-07-31 18:23:34",2:"2022-09-12 23:12:11"},

     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     ),
     * )
     */
    public function miturAbruzzoRegionList()
    {
        $regions = Region::all();

        $formattedRegions = $regions->mapWithKeys(function ($region) {
            return [$region->id => $region->updated_at->format('Y-m-d H:i:s')];
        });

        return response()->json($formattedRegions);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/mitur_abruzzo/region/{id}",
     *     operationId="getRegionById",
     *     tags={"Api V2 - MITUR Abruzzo"},
     *     summary="Get Region by ID",
     *     description="Returns a single region, including mountain groups and region geometry, by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the region to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 example="Feature"
     *             ),
     *             @OA\Property(
     *                 property="properties",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Region Name"
     *                 ),
     *               @OA\Property(
     *                     property="mountain_groups",
     *                     type="object",
     *                     example={"1": "2022-12-03 12:34:25", "2": "2023-01-15 09:30:00", "3": "2023-02-20 14:45:10"}
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="geometry",
     *                 description="GeoJSON geometry of the region",
     *                 type="object",
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     example="MultiPolygon"
     *                 ),
     *                 @OA\Property(
     *                     property="coordinates",
     *                     type="array",
     *                     @OA\Items(
     *                         type="array",
     *                         @OA\Items(
     *                             type="number",
     *                             format="float",
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Region not found"
     *     )
     * )
     */
    public function miturAbruzzoRegionById($id)
    {

        $region = Region::find($id);

        //get the mountain groups for the region
        $mountainGroups = $region->mountainGroups;

        //get the region geometry
        $geom_s = $region
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first()
            ->geom;
        $geom = json_decode($geom_s, TRUE);

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';
        $geojson['properties'] = [];
        $geojson['geometry'] = $geom;

        $properties = [];
        $properties['id'] = $region->id;
        $properties['name'] = $region->name;
        $properties['mountain_groups'] = $mountainGroups->pluck('updated_at', 'id')->toArray();

        $geojson['properties'] = $properties;

        return response()->json($geojson);
    }
}
