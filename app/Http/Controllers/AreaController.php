<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AreaController extends Controller
{
    public function geojson(string $id)
    {
        $area = Area::find($id);
        $results = DB::select('SELECT ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom FROM areas WHERE id = ?;', [$id]);
        if (count($results) > 0)
        {
            $geojson = [
                'type' => 'Feature',
                'geometry' => json_decode($results[0]->geom),
                'properties' => [
                    'id' => $area->id,
                    'name' => $area->name,
                    'code' => $area->code,
                    'full_code' => $area->full_code,
                    'province' => $area->province->name,
                    'region' => $area->province->region->name
                ]
            ];
            return response(json_encode($geojson));
        }
        else
            return abort(404,'Area ' . $id . ' not found');
    }
}
