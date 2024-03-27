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
     *     path="/api/v2/mitur_abruzzo/hut/{id}",
     *     operationId="getMiturAbruzzoHutById",
     *     tags={"Api V2 - MITUR Abruzzo"},
     *     summary="Get Hut by ID",
     *     description="Returns a single hut by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the hut to return",
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
     *                     example="Rifugio"
     *                 ),
     *                 @OA\Property(
     *                     property="second_name",
     *                     type="string",
     *                     example="Rifugio 2"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     example="Bivacco"
     *                 ),
     *                 @OA\Property(
     *                     property="elevation",
     *                     type="integer",
     *                     example=2000
     *                 ),
     *                 @OA\Property(
     *                     property="mountain_groups",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="type_custodial",
     *                     type="string",
     *                     example="1"
     *                 ),
     *                 @OA\Property(
     *                    property="company_management_property",
     *                   type="string",
     *                 example="Montagna srl"
     *                ),
     *                @OA\Property(
     *                   property="addr:street",
     *                 type="string",
     *                example="via guglie alte"
     *              ),
     *             @OA\Property(
     *               property="addr:housenumber",
     *            type="string",
     *          example="23"
     *       ),
     *     @OA\Property(
     *      property="addr:postcode",
     *  type="string",
     * example="54787"
     * ),
     * @OA\Property(
     * property="addr:city",
     * type="string",
     * example="Alpi"
     * ),
     * @OA\Property(
     * property="ref:vatin",
     * type="string",
     * example="IT0000000000000"
     * ),
     * @OA\Property(
     * property="phone",
     * type="string",
     * example="+39 000 00000000"
     * ),
     * @OA\Property(
     * property="fax",
     * type="string",
     * example="+39 000 00000001"
     * ),
     * @OA\Property(
     * property="email",
     * type="string",
     * example="example@email.com"
     * ),
     * @OA\Property(
     * property="email_pec",
     * type="string",
     * example="pec@email.com"
     * ),
     * @OA\Property(
     * property="website",
     * type="string",
     * example="www.sito.it"
     * ),
     * @OA\Property(
     * property="facebook_contact",
     * type="string",
     * example="https://facebook.com/rifugio"
     * ),
     * @OA\Property(
     * property="municipality_geo",
     * type="string",
     * example="Alagna Valsesia"
     * ),
     * @OA\Property(
     * property="province_geo",
     * type="string",
     * example="Lucca"
     * ),
     * @OA\Property(
     * property="site_geo",
     * type="string",
     * example="Piemonte"
     * ),
     * @OA\Property(
     * property="source:ref",
     * type="string",
     * example="123456"
     * ),
     * @OA\Property(
     * property="description",
     * type="string",
     * example="Description of the hut"
     * ),
     * @OA\Property(
     * property="pois",
     * type="array",
     * @OA\Items(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Property(
     * property="opening",
     * type="string",
     * example="Opening hours"
     * ),
     * @OA\Property(
     * property="acqua_in_rifugio_service",
     * type="string",
     * example="1"
     * ),
     * @OA\Property(
     * property="acqua_calda_service",
     * type="string",
     * example="1"
     * ),
     * @OA\Property(
     * property="acqua_esterno_service",
     * type="string",
     * example="1"
     * ),
     * @OA\Property(
     * property="posti_letto_invernali_service",
     * type="string",
     * example="12"
     * ),
     * @OA\Property(
     * property="posti_totali_service",
     * type="string",
     * example="23"
     * ),
     * @OA\Property(
     * property="ristorante_service",
     * type="string",
     * example="1"
     * ),
     * @OA\Property(
     * property="activities",
     * type="string",
     * example="Escursionismo/Alpinismo"
     * ),
     * @OA\Property(
     * property="necessary_equipment",
     * type="string",
     * example="Normale dotazione Escursionistica / Normale dotazione Alpinistica"
     * ),
     * @OA\Property(
     * property="rates",
     * type="string",
     * example="https://www.cai.it/wp-content/uploads/2022/12/23-2022-Circolare-Tariffario-rifugi-2023_signed.pdf"
     * ),
     * @OA\Property(
     * property="payment_credit_cards",
     * type="string",
     * example="1"
     * ),
     * @OA\Property(
     * property="hiking_routes",
     * type="array",
     * @OA\Items(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Property(
     * property="accessibilitá_ai_disabili_service",
     * type="string",
     * example="1"
     * ),
     * @OA\Property(
     * property="gallery",
     * type="string",
     * example="https://galleria.it/"
     * ),
     * @OA\Property(
     * property="rule",
     * type="string",
     * example="https://www.cai.it/wp-content/uploads/2020/12/Regolamento-strutture-ricettive-del-Club-Alpino-Italiano.pdf"
     * )
     *            ),
     *                @OA\Property(property="geometry", type="object",
     *                     @OA\Property( property="type", type="string",  description="Postgis geometry type: Point, etc."),
     *                    @OA\Property( property="coordinates", type="object",  description="hut coordinates (WGS84)")
     *               ),
     *       ),
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

        //get the mountain groups for the hut based on the geometry intersection
        $mountainGroups = $hut->getMountainGroupsIntersecting()->first();

        //get the pois in a 1km buffer from the hut
        $pois = $hut->getPoisInBuffer(1000);

        //get the hiking routes in a 1km buffer from the hut
        $hikingRoutes = $hut->getHikingRoutesInBuffer(1000);

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $hut->id;
        $properties['name'] = $hut->name ?? '';
        $properties['second_name'] = $hut->second_name ?? '';
        $properties['type'] = 'Bivacco';
        $properties['elevation'] = $hut->elevation ?? '';
        $properties['mountain_groups'] = $mountainGroups ? $mountainGroups->id : '';
        $properties['type_custodial'] = $hut->type_custodial ?? '1';
        $properties['company_management_property'] = $hut->company_management_property ?? 'Montagna srl';
        $properties['addr:street'] = $hut->addr_street ?? 'via guglie alte';
        $properties['addr:housenumber'] = $hut->addr_housenumber ?? '23';
        $properties['addr:postcode'] = $hut->addr_postcode ?? '54787';
        $properties['addr:city'] = $hut->addr_city ?? 'Alpi';
        $properties['ref:vatin'] = $hut->ref_vatin ?? 'IT0000000000000';
        $properties['phone'] = $hut->phone ?? '+39 000 00000000';
        $properties['fax'] = $hut->fax ?? '+39 000 00000001';
        $properties['email'] = $hut->email ?? 'info@email.com';
        $properties['email_pec'] = $hut->email_pec ?? 'info@pec.com';
        $properties['website'] = $hut->website ?? 'www.sito.it';
        $properties['facebook_contact'] = $hut->facebook_contact ?? 'https://facebook.com/rifugio';
        $properties['municipality_geo'] = $hut->municipality_geo ?? 'Alagna Valsesia';
        $properties['province_geo'] = $hut->province_geo ?? 'Lucca';
        $properties['site_geo'] = $hut->site_geo ?? 'Piemonte';
        $properties['source:ref'] = $hut->unico_id;
        $properties['description'] = $hut->description;
        $properties['pois'] = $pois->count() > 0 ? $pois->pluck('id')->toArray() : [];
        $properties['opening'] = $hut->opening ?? '';
        $properties['acqua_in_rifugio_service'] = $hut->acqua_in_rifugio_serviced ?? '1 (possibilitá 0 o 1)';
        $properties['acqua_calda_service'] = $hut->acqua_calda_service ?? '1 (possibilitá 0 o 1)';
        $properties['acqua_esterno_service'] = $hut->acqua_esterno_service ?? '1 (possibilitá 0 o 1)';
        $properties['posti_letto_invernali_service'] = $hut->posti_letto_invernali_service ?? '12';
        $properties['posti_totali_service'] = $hut->posti_totali_service ?? '23';
        $properties['ristorante_service'] = $hut->ristorante_service ?? '1 (possibilitá 0 o 1)';
        $properties['activities'] = $hut->activities ?? 'Escursionismo/Alpinismo';
        $properties['necessary_equipment'] = $hut->necessary_equipment ?? 'Normale dotazione Escursionistica / Normale dotazione Alpinistica';
        $properties['rates'] = $hut->rates ?? 'https://www.cai.it/wp-content/uploads/2022/12/23-2022-Circolare-Tariffario-rifugi-2023_signed.pdf';
        $properties['payment_credit_cards'] = $hut->payment_credit_cards ?? '1 (possibilitá 0 o 1)';
        $properties['hiking_routes'] = $hikingRoutes->count() > 0 ? $hikingRoutes->pluck('relation_id')->toArray() : [];
        $properties['accessibilitá_ai_disabili_service'] = $hut->acessibilitá_ai_disabili_service ?? '1 (possibilitá 0 o 1)';
        $properties['gallery'] = $hut->gallery ?? 'https://galleria.it/';
        $properties['rule'] = $hut->rule ?? 'https://www.cai.it/wp-content/uploads/2020/12/Regolamento-strutture-ricettive-del-Club-Alpino-Italiano-20201.pdf';
        $properties['map'] = $hut->map ?? 'https://www.mappa-rifugio.it';

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
     *     summary="Get POI by ID",
     *     description="Returns a single POI by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the POI to return",
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
     *                     example="POI Name"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     example="POI Type"
     *                 ),
     *                 @OA\Property(
     *                     property="comune",
     *                     type="string",
     *                     example="Comune Name"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     example="Description of the POI"
     *                 ),
     *                 @OA\Property(
     *                     property="info",
     *                     type="string",
     *                     example="Info about the POI"
     *                 ),
     *                 @OA\Property(
     *                     property="difficulty",
     *                     type="string",
     *                     example="Difficulty of the POI"
     *                 ),
     *                @OA\Property(
     *                    property="from",
     *                  type="string",
     *                example="From"
     *            ),
     *           @OA\Property(
     *             property="activity",
     *          type="string",
     *        example="Activity"
     *   ),
     * 
     * @OA\Property(
     * property="has_hiking_routes",
     * type="array",
     * @OA\Items(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Property(
     * property="map",
     * type="string",
     * example="https://osm2cai.cai.it/poi/id/{}"
     * )
     *           ),
     *               @OA\Property(property="geometry", type="object",
     *                    @OA\Property( property="type", type="string",  description="Postgis geometry type: Point, etc."),
     *                  @OA\Property( property="coordinates", type="object",  description="poi coordinates (WGS84)")
     *             ),
     *        ),
     *   ),
     * @OA\Response(
     * response=404,
     * description="POI not found"
     * )
     * )
     */
    public function miturAbruzzoPoiById($id)
    {
        $poi = EcPoi::findOrFail($id);
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut nec tincidunt arcu, vel sollicitudin nisi. Fusce a nulla sit amet odio accumsan auctor.
                    Sed ultricies ullamcorper velit, ac faucibus dolor. Nullam in risus neque. Quisque in dolor et est ullamcorper commodo at vitae libero.';

        //check if there is an hiking route in a 1km buffer from the poi
        $hikingRoutes = $poi->getHikingRoutesInBuffer(1000);
        $hikingRoute = $hikingRoutes->first();

        //check the municipality intersecting
        $municipality = $poi->getMunicipalityIntersecting();
        //get the comuni from the municipalities and create a string with comma separated values
        $comuni = $municipality->pluck('comune')->implode(',');

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $poi->id;
        $properties['name'] = $poi->name;
        $properties['type'] = $poi->getTagsMapping();
        $properties['comune'] = $comuni;
        $properties['description'] = $lorem;
        $properties['info'] = $lorem;
        $properties['difficulty'] = $hikingRoute ? $hikingRoute->cai_scale : '';
        $properties['from'] = $hikingRoute ? $hikingRoute->from : '';
        $properties['activity'] = 'Escursionismo';
        $properties['has_hiking_routes'] = $hikingRoutes->count() > 0 ? $hikingRoutes->pluck('id')->toArray() : [];
        $properties['map'] = 'https://osm2cai.cai.it/poi/id/{}';

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
     *                 ),
     *                 @OA\Property(
     *                     property="addr:city",
     *                     type="string",
     *                     example="Rome"
     *                 ),
     *                   @OA\Property(
     *                     property="addr:housenumber",
     *                     type="string",
     *                     example="1"
     *                 ),
     *                    @OA\Property(
     *                     property="addr:postcode",
     *                     type="string",
     *                     example="00100"
     *                 ),
     *                 @OA\Property(
     *                     property="addr:street",
     *                     type="string",
     *                     example="Via Roma"
     *                 ),
     *                @OA\Property(
     *                    property="provinces",
     *                   type="string",
     *                  example="MOCKUP > Provincia"
     *             ),
     *                  @OA\Property(
     *                     property="source:ref",
     *                     type="string",
     *                     example="9219007"
     *                 ),
     *                    @OA\Property(
     *                     property="website",
     *                     type="string",
     *                     example="http://example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     example="info@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="opening_hours",
     *                     type="string",
     *                     example="Mo-Fr 09:00-17:00"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                     example="+390612345678"
     *                 ),
     *                 @OA\Property(
     *                     property="wheelchair",
     *                     type="string",
     *                     example="yes"
     *                 ),
     *                 @OA\Property(
     *                     property="fax",
     *                     type="string",
     *                     example="+390612345679"
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="geometry",
     *                 type="object",
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     example="Point"
     *                 ),
     *                 @OA\Property(
     *                     property="coordinates",
     *                     type="array",
     *                     @OA\Items(
     *                         type="number",
     *                         format="float"
     *                     ),
     *                     example={"longitude", "latitude"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Section not found"
     *     )
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
        $properties['addr:city'] = $section->addr_city;
        $properties['addr:housenumber'] = $section->addr_housenumber;
        $properties['addr:postcode'] = $section->addr_postcode;
        $properties['addr:street'] = $section->addr_street;
        $properties['provinces'] = 'MOCKUP > Provincia';
        $properties['source:ref'] = $section->cai_code;
        $properties['website'] = $section->website;
        $properties['email'] = $section->email;
        $properties['opening_hours'] = $section->opening_hours;
        $properties['phone'] = $section->phone;
        $properties['wheelchair'] = $section->wheelchair;
        $properties['fax'] = $section->fax;

        $geometry = $section->getGeometry();

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $geometry;

        return response()->json($geojson);
    }
}
