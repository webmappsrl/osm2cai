<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\HikingRoute;
use Illuminate\Http\Request;
use App\Http\Resources\HikingRouteResource;
use App\Http\Resources\HikingRouteResourceCollection;
use App\Http\Resources\UserResource;

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

    public function UsersList()
    {

        return response()->json(User::all('id', 'updated_at')->mapWithKeys(function ($user) {

            return [$user->id => $user->updated_at];
        }));
    }

    public function UsersSingleFeature($id)
    {
        return new UserResource(User::find($id));
    }
}