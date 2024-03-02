<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

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
        $regions = Region::all()->pluck('updated_at', 'id');
        return response()->json($regions);
    }
}
