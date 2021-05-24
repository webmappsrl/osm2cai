<?php

namespace App\Http\Controllers;

use App\Models\HikingRoute;
use Illuminate\Http\Request;

class HikingRouteController extends Controller
{
    public function boundingBox(Request $request)
    {
        // TODO: Cehck (bad)data and returns code
        // Retrieve data
        $geojson = HikingRoute::geojsonByBoundingBox(
            $request->input('osm2cai_status'),
            $request->input('lo0'),
            $request->input('la0'),
            $request->input('lo1'),
            $request->input('la1')
        );
        // Return
        return response($geojson, 200, ['Content-type' => 'application/json']);
    }
}
