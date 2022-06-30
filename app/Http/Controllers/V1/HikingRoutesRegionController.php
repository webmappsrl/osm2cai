<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\HikingRoute;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HikingRoutesRegionController extends Controller
{

    /**
     * @OA\Tag(
     *     name="hiking-routes",
     *     description="Hiking route ID list based on regione and SDA",
     * )
     * 
     * @OA\Get(
     *      path="/api/v1/hiking-routes/region/{regione_code}/{sda}",
     *      tags={"hiking-routes"},
     *      @OA\Response(
     *          response=200,
     *          description="Returns all the hiking routes IDs base on the given region code and SDA number.",
     *      ),
     *     @OA\Parameter(
     *         name="regione_code",
     *         in="path",
     *         description="Regione code (e.g. 'l' for tuscany)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="varchar",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="sda",
     *         in="path",
     *         description="Number of SDA 'stato di accatastamento' (e.g. 3 or 3,1 or 0,1,2)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="varchar"
     *         )
     *     ),
     * )
     * 
     */
    public function hikingroutelist(string $region_id,string $sda) {
        $region_id = strtoupper($region_id);
        
        $sda = explode(',',$sda);
        $list = HikingRoute::query();
        $list = HikingRoute::whereHas('regions',function($query) use ($region_id) { $query->where('code',$region_id); })->where(function ($query) use ($sda) {
            if (count($sda) == 1) {
                return $query->where('osm2cai_status', $sda[0]);
            }
            if (count($sda) == 2) {
                return $query->where('osm2cai_status', $sda[0])
                             ->orWhere('osm2cai_status', '=', $sda[1]);
            }
            if (count($sda) == 3) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2]);
            }
            if (count($sda) == 4) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2])
                            ->orWhere('osm2cai_status', '=', $sda[3]);
            }
            if (count($sda) == 5) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2])
                            ->orWhere('osm2cai_status', '=', $sda[3])
                            ->orWhere('osm2cai_status', '=', $sda[4]);
            }
        })
        ->get();

        // $list = $list->pluck('ref_REI_comp')->toArray();
        $list = $list->pluck('id')->toArray();

        // Return
        return response($list, 200, ['Content-type' => 'application/json']);
    }

    /**
     * @OA\Tag(
     *     name="hiking-routes-osm",
     *     description="Hiking route OSM ID list based on regione and SDA",
     * )
     * 
     * @OA\Get(
     *      path="/api/v1/hiking-routes-osm/region/{regione_code}/{sda}",
     *      tags={"hiking-routes-osm"},
     *      @OA\Response(
     *          response=200,
     *          description="Returns all the hiking routes OSM IDs base on the given region code and SDA number.",
     *      ),
     *     @OA\Parameter(
     *         name="regione_code",
     *         in="path",
     *         description="Regione code (e.g. 'l' for tuscany)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="varchar",
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="sda",
     *         in="path",
     *         description="Number of SDA 'stato di accatastamento' (e.g. 3 or 3,1 or 0,1,2)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="varchar"
     *         )
     *     ),
     * )
     * 
     */
    public function hikingrouteosmlist(string $region_id,string $sda) {
        $region_id = strtoupper($region_id);
        
        $sda = explode(',',$sda);
        $list = HikingRoute::query();
        $list = HikingRoute::whereHas('regions',function($query) use ($region_id) { $query->where('code',$region_id); })->where(function ($query) use ($sda) {
            if (count($sda) == 1) {
                return $query->where('osm2cai_status', $sda[0]);
            }
            if (count($sda) == 2) {
                return $query->where('osm2cai_status', $sda[0])
                             ->orWhere('osm2cai_status', '=', $sda[1]);
            }
            if (count($sda) == 3) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2]);
            }
            if (count($sda) == 4) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2])
                            ->orWhere('osm2cai_status', '=', $sda[3]);
            }
            if (count($sda) == 5) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2])
                            ->orWhere('osm2cai_status', '=', $sda[3])
                            ->orWhere('osm2cai_status', '=', $sda[4]);
            }
        })
        ->get();

        // $list = $list->pluck('ref_REI_comp')->toArray();
        $list = $list->pluck('relation_id')->toArray();

        // Return
        return response($list, 200, ['Content-type' => 'application/json']);
    }
    
    /**
     * @OA\Tag(
     *     name="hiking-route",
     *     description="Geojson of a Hiking Route based on the give ID",
     * )
     * 
     * @OA\Get(
     *      path="/api/v1/hiking-route/{id}",
     *      tags={"hiking-route"},
     *      @OA\Response(
     *          response=200,
     *          description="Returns the geojson of a Hiking Route based on the give ID",
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of a specific Hiking Route (e.g. 2421)",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     * )
     * 
     */
    public function hikingroutebyid(int $id) {
        
        $item = HikingRoute::where('id', $id)->first();

        $HR = $this->createGeoJSONFromModel($item);

        // Return
        return response($HR, 200, ['Content-type' => 'application/json']);
    }
    
    /**
     * @OA\Tag(
     *     name="hiking-route-osm",
     *     description="Geojson of a Hiking Route based on the give OSM ID",
     * )
     * 
     * @OA\Get(
     *      path="/api/v1/hiking-route-osm/{id}",
     *      tags={"hiking-route-osm"},
     *      @OA\Response(
     *          response=200,
     *          description="Returns the geojson of a Hiking Route based on the give OSM ID",
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The OSM ID of a specific Hiking Route (e.g. 13442719)",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     * )
     * 
     */
    public function hikingroutebyosmid(int $id) {
        
        $item = HikingRoute::where('relation_id', $id)->first();

        $HR = $this->createGeoJSONFromModel($item);

        // Return
        return response($HR, 200, ['Content-type' => 'application/json']);
    }

    public function createGeoJSONFromModel($item) {
        $obj = HikingRoute::where('id', '=', $item->id)
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
                "properties" => [
                    "id" => $item->id,
                    "name" => $item->name,
                    "rwn_name" => $item->rwn_name,
                    "created_at" => $item->created_at,
                    "updated_at" => $item->updated_at,
                    "relation_id" => $item->relation_id,
                    "osm2cai_status" => $item->osm2cai_status,
                    "validation_date" => $item->validation_date,
                    "user_id" => $item->user_id,
                    "ref" => $item->ref,
                    "old_ref" => $item->old_ref,
                    "source" => $item->source,
                    "source_ref" => $item->source_ref,
                    "survey_date" => $item->survey_date,
                    "tags" => $item->tags,
                    "cai_scale" => $item->cai_scale,
                    "from" => $item->from,
                    "to" => $item->to,
                    "osmc_symbol" => $item->osmc_symbol,
                    "network" => $item->network,
                    "roundtrip" => $item->roundtrip,
                    "symbol" => $item->symbol,
                    "symbol_it" => $item->symbol_it,
                    "ascent" => $item->ascent,
                    "descent" => $item->descent,
                    "distance" => $item->distance,
                    "duration_forward" => $item->duration_forward,
                    "duration_backward" => $item->duration_backward,
                    "operator" => $item->operator,
                    "state" => $item->state,
                    "description" => $item->description,
                    "website" => $item->website,
                    "wikimedia_commons" => $item->wikimedia_commons,
                    "maintenance" => $item->maintenance,
                    "note" => $item->note,
                    "note_project_page" => $item->note_project_page,
                ],
                "geometry" => json_decode($geom, true)
            ];
        } 
    }
}
