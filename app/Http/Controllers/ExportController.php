<?php

namespace App\Http\Controllers;

use App\Models\HikingRoute;
use Illuminate\Http\Request;
use App\Http\Resources\HikingRouteResource;
use App\Http\Resources\HikingRouteResourceCollection;

class ExportController extends Controller
{
    public function hikingRoutesList()
    {
        $hikingRoutes = HikingRoute::all('id', 'updated_at');

        $data =  $hikingRoutes->mapWithKeys(function ($hikingRoute) {

            return [$hikingRoute->id => $hikingRoute->updated_at];
        });

        return response()->json($data);
    }

    public function hikingRoutesSingleFeature($id)
    {
        $hikingRoute = HikingRoute::find($id);
        return new HikingRouteResource($hikingRoute);
    }
}