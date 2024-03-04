<?php

namespace App\Http\Controllers\V2;

use App\Models\EcPoi;
use App\Models\Region;
use App\Models\CaiHuts;
use App\Models\HikingRoute;
use Illuminate\Http\Request;
use App\Models\MountainGroups;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Section;

class MiturAbruzzoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/mitur_abruzzo/region_list",
     *     tags={"Api V2 - MITUR Abruzzo"},
     *     summary="List all regions",
     *     description="Returns a list of all regions with their ID and last updated timestamp. The update timestamp of an item in the database is formatted as 'YYYY-MM-DD-Thh:mm:ss+HH:M', adhering to the ISO8601 standard. This notation represents the date and time with an offset of hours (HH) and minutes (MM) from Coordinated Universal Time (UTC), ensuring a consistent formatting and interpretation across the globe.",
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

            $formattedDate = $region->updated_at->toIso8601String();

            return [$region->id => $formattedDate];
        });

        return response()->json($formattedRegions);
    }


    /**
     * @OA\Get(
     *     path="/api/v2/mitur_abruzzo/region/{id}",
     *     operationId="getRegionById",
     *     tags={"Api V2 - MITUR Abruzzo"},
     *     summary="Get Region by ID",
     *     description="Returns a single region, including mountain groups and region geometry, by ID. The update timestamp of an item in the database is formatted as 'YYYY-MM-DD-Thh:mm:ss+HH:M', adhering to the ISO8601 standard. This notation represents the date and time with an offset of hours (HH) and minutes (MM) from Coordinated Universal Time (UTC), ensuring a consistent formatting and interpretation across the globe.",
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
     *               @OA\Property(property="geometry", type="object",
     *                      @OA\Property( property="type", type="string",  description="Postgis geometry type: MultiPolygon, etc."),
     *                      @OA\Property( property="coordinates", type="object",  description="region coordinates (WGS84)")
     *                 ),
     *               example={"type":"Feature","properties":{"id":1,"name":"Abruzzo","mountain_groups":{"1":"2022-12-03 12:34:25","2":"2023-01-15 09:30:00","3":"2023-02-20 14:45:10"}},"geometry":{"type":"MultiPolygon","coordinates":{{{10.4495294,43.7615252},{10.4495998,43.7615566}}}}}
     *        )
     *   ),
     *  @OA\Response(
     *    response=404,
     *  description="Region not found"
     * )
     * )
     */
    public function miturAbruzzoRegionById($id)
    {

        $region = Region::findOrfail($id);

        //get the mountain groups for the region
        $mountainGroups = $region->mountainGroups;
        //format the date
        $mountainGroups = $mountainGroups->mapWithKeys(function ($mountainGroup) {
            $formattedDate = $mountainGroup->updated_at ? $mountainGroup->updated_at->toIso8601String() : null;

            return [$mountainGroup->id => $formattedDate];
        });

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
        $properties['mountain_groups'] = $mountainGroups;

        $geojson['properties'] = $properties;

        return response()->json($geojson);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/mitur_abruzzo/mountain_group/{id}",
     *     operationId="getMountainGroupById",
     *     tags={"Api V2 - MITUR Abruzzo"},
     *     summary="Get Mountain group by ID",
     *     description="Returns a single mountain group, including hiking routes, huts and POIs that intersect with the mountain group geometry. Sections are hardcoded for now. Will be implemented soon. The update timestamp of an item in the database is formatted as 'YYYY-MM-DD-Thh:mm:ss+HH:M', adhering to the ISO8601 standard. This notation represents the date and time with an offset of hours (HH) and minutes (MM) from Coordinated Universal Time (UTC), ensuring a consistent formatting and interpretation across the globe.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the mountain group to return",
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
     *                     example=406
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Nodo della Scoffera - Gruppo del Monte Ramaceto"
     *                 ),
     *                 @OA\Property(
     *                     property="hiking_routes",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="201",
     *                               type="string",
     *                             format="date-time",
     *                             example="2021-11-03T09:36:41+00:00",
     * description="The update timestamp of an item in the database is formatted as 'YYYY-MM-DD-Thh:mm:ss+HH:M', adhering to the ISO8601 standard. This notation represents the date and time with an offset of hours (HH) and minutes (MM) from Coordinated Universal Time (UTC), ensuring a consistent formatting and interpretation across the globe."
     *                         ),
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="huts",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="202",
     *                             type="string",
     *                             format="date-time",
     *                             example="2021-11-03T09:36:41+00:00",
     *      description="The update timestamp of an item in the database is formatted as 'YYYY-MM-DD-Thh:mm:ss+HH:M', adhering to the ISO8601 standard. This notation represents the date and time with an offset of hours (HH) and minutes (MM) from Coordinated Universal Time (UTC), ensuring a consistent formatting and interpretation across the globe."

     *                         ),
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pois",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="203",
     *                             type="string",
     *                             format="date-time",
     *                             example="2021-11-03T09:36:41+00:00",
     *      description="The update timestamp of an item in the database is formatted as 'YYYY-MM-DD-Thh:mm:ss+HH:M', adhering to the ISO8601 standard. This notation represents the date and time with an offset of hours (HH) and minutes (MM) from Coordinated Universal Time (UTC), ensuring a consistent formatting and interpretation across the globe."

     *                         ),
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="sections",
     *                     type="object",
     *                     example={
     *                         "501": "2022-12-03 12:34:25",
     *                         "502": "2023-01-15 09:30:00",
     *                         "503": "2023-02-20 14:45:10"
     *                     }
     *                 )
     *             ),
     *             @OA\Property(property="geometry", type="object",
     *                     @OA\Property( property="type", type="string",  description="Postgis geometry type: MultiPolygon, etc."),
     *                    @OA\Property( property="coordinates", type="object",  description="mountain group coordinates (WGS84)")
     *                ),
     * example={
     *"type": "Feature",
     *"properties": {
     *   "id": 406,
     *  "name": "Nodo della Scoffera - Gruppo del Monte Ramaceto",
     * "hiking_routes": {
     *    "201": "2021-11-03T09:36:41+00:00"
     *},
     *"huts": {
     *   "202": "2021-11-03T09:36:41+00:00"
     *},
     *"pois": {
     *   "203": "2021-11-03T09:36:41+00:00"
     *},
     *"sections": {
     *   "501": "2022-12-03 12:34:25",
     *  "502": "2023-01-15 09:30:00",
     * "503": "2023-02-20 14:45:10"
     *}
     *},
     *"geometry": {
     *   "type": "MultiPolygon",
     *  "coordinates": {
     *     {
     *        {
     *           {
     *              10.4495294,
     *             43.7615252
     *        },
     *       {
     *          10.4495998,
     *         43.7615566
     *    },
     *   {
     *      10.4495294,
     *     43.7615252
     *}
     *}
     *}
     *}
     *}
     *}
     *       ),
     *  ),
     * @OA\Response(
     *  response=404,
     * description="Mountain group not found"
     * )
     * )
     */
    public function miturAbruzzoMountainGroupById($id)
    {
        //get the mountain group by id
        $mountainGroup = MountainGroups::findOrfail($id);

        //get the hiking routes that intersect with the mountain group geometry
        $hikingRoutes = $mountainGroup->getHikingRoutesIntersecting();
        //get only hiking routes with osm2cai_status =  4
        $hikingRoutes = $hikingRoutes->where('osm2cai_status', 4)->pluck('updated_at', 'id')->toArray();
        //format the date 
        foreach ($hikingRoutes as $key => $value) {
            $hikingRoutes[$key] = $value->toIso8601String();
        }

        //get the huts that intersect with the mountain group geometry
        $huts = $mountainGroup->getHutsIntersecting();
        $huts = $huts->pluck('updated_at', 'id')->toArray();
        //format the date
        foreach ($huts as $key => $value) {
            $huts[$key] = $value->toIso8601String();
        }

        //get the pois that intersect with the mountain group geometry
        $pois = $mountainGroup->getPoisIntersecting();
        $pois = $pois->pluck('updated_at', 'id')->toArray();
        //format the date
        foreach ($pois as $key => $value) {
            $pois[$key] = $value->toIso8601String();
        }

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $mountainGroup->id;
        $properties['name'] = $mountainGroup->name;
        $properties['hiking_routes'] = $hikingRoutes;
        $properties['huts'] = $huts;
        $properties['pois'] = $pois;
        $properties['sections'] = [501 => "2022-12-03 12:34:25", 502 => "2023-01-15 09:30:00", 503 => "2023-02-20 14:45:10"]; //todo get the sections when the model has the geometry. Hardcoded for the moment

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $mountainGroup->getGeometry();

        return response()->json($geojson);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/mitur_abruzzo/hiking_route/{id}",
     *     operationId="getMiturAbruzzoHikingRouteById",
     *     tags={"Api V2 - MITUR Abruzzo"},
     *     summary="Get Hiking route by ID",
     *     description="Returns a single hiking route by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the hiking route to return",
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
     *                     property="ref",
     *                     type="string",
     *                     example="T001"
     *                 )
     *             ),
     
     *                 @OA\Property(property="geometry", type="object",
     *                      @OA\Property( property="type", type="string",  description="Postgis geometry type: Linestring, MultilineString, etc."),
     *                      @OA\Property( property="coordinates", type="object",  description="hut coordinates (WGS84)")
     *                 ),
     *                example={"type":"Feature","properties":{"id":1,"ref":"T001"},"geometry":{"type":"MultiLineString","coordinates":{{{10.4495294,43.7615252},{10.4495998,43.7615566}}}}}
     *            )
     *        ),
     *    @OA\Response(
     *       response=404,
     *      description="Hiking route not found"
     *   )
     * )
     */
    public function miturAbruzzoHikingRouteById($id)
    {
        $hikingRoute = HikingRoute::findOrfail($id);

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $hikingRoute->id;
        $properties['ref'] = $hikingRoute->ref;

        $geometry = $hikingRoute->getGeometry();

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $geometry;

        return response()->json($geojson);
    }
    /**
     * @OA\Get(
     *    path="/api/v2/mitur_abruzzo/hut/{id}",
     *   operationId="getMiturAbruzzoHutById",
     *  tags={"Api V2 - MITUR Abruzzo"},
     * summary="Get Hut by ID",
     * description="Returns a single hut by ID.",
     * @OA\Parameter(
     *    name="id",
     *  in="path",
     * description="ID of the hut to return",
     * required=true,
     * @OA\Schema(
     *   type="integer",
     * format="int64"
     * )
     * ),
     * @OA\Response(
     *   response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     *  type="object",
     * @OA\Property(
     * property="type",
     * type="string",
     * example="Feature"
     * ),
     * @OA\Property(
     * property="properties",
     * type="object",
     * @OA\Property(
     * property="id",
     * type="integer",
     * example=1
     * ),
     * @OA\Property(
     * property="name",
     * type="string",
     * example="Rifugio Rossi"
     * )
     * ),
     *                 @OA\Property(property="geometry", type="object",
     *                      @OA\Property( property="type", type="string",  description="Postgis geometry type: Point"),
     *                      @OA\Property( property="coordinates", type="object",  description="hut coordinates (WGS84)")
     *                 ),
     * example={"type":"Feature","properties":{"id":1,"name":"Rifugio Rossi"},"geometry":{"type":"Point","coordinates":{13.399,42.123}}}
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Hut not found"
     * )
     * )
     */
    public function miturAbruzzoHutById($id)
    {
        $hut = CaiHuts::findOrFail($id);

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $hut->id;
        $properties['name'] = $hut->name;

        $geometry = $hut->getGeometry();

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $geometry;

        return response()->json($geojson);
    }
    /**
     * @OA\Get(
     *     path="/api/v2/mitur_abruzzo/poi/{id}",
     *     operationId="getMiturAbruzzoPoiById",
     *     tags={"Api V2 - MITUR Abruzzo"},
     *     summary="Get Poi by ID",
     *     description="Returns a single poi by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the poi to return",
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
     *                     example="Monte Rosa"
     *                 )
     *             ),
     *                 @OA\Property(property="geometry", type="object",
     *                      @OA\Property( property="type", type="string",  description="Postgis geometry type: Point"),
     *                      @OA\Property( property="coordinates", type="object",  description="poi coordinates (WGS84)")
     *                 ),
     *                example={"type":"Feature","properties":{"id":1,"name":"Poi Name"},"geometry":{"type":"Point","coordinates":{102.2,2.4}}}
     *            )
     *        ),
     *    @OA\Response(
     *       response=404,
     *      description="Poi not found"
     *   )
     * )
     */
    public function miturAbruzzoPoiById($id)
    {
        $poi = EcPoi::findOrFail($id);

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $poi->id;
        $properties['name'] = $poi->name;

        $geometry = $poi->getGeometry();

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $geometry;

        return response()->json($geojson);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/mitur_abruzzo/section/{id}",
     *     operationId="getMiturAbruzzoSectionById",
     *     tags={"Api V2 - MITUR Abruzzo"},
     *     summary="Get Section by ID",
     *     description="Returns a single section by ID. Coordinates are hardcoded for now. Will be implemented soon.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the section to return",
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
     *                     example="Section Name"
     *                 )
     *             ),
     *                 @OA\Property(property="geometry", type="object",
     *                      @OA\Property( property="type", type="string",  description="Postgis geometry type: Point"),
     *                      @OA\Property( property="coordinates", type="object",  description="section coordinates (WGS84)")
     *                 ),
     *                example={"type":"Feature","properties":{"id":1,"name":"Sezione di Roma"},"geometry":{"type":"Point","coordinates":{12.4964,41.9028}}}
     *            )
     *        ),
     *    @OA\Response(
     *       response=404,
     *      description="Section not found"
     *   )
     * )
     */
    public function miturAbruzzoSectionById($id)
    {


        $section = Section::findOrFail($id);

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $section->id;
        $properties['name'] = $section->name;

        //todo implement when the model has the geometry 
        // $geometry = $section->getGeometry();
        $geometry = [
            "type" => "Point",
            "coordinates" => [12.4964, 41.9028]
        ];

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $geometry;

        return response()->json($geojson);
    }
}
