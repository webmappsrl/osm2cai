<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function geojson(string $id)
    {
        $region = Region::find($id);
        $results = DB::select('SELECT ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom FROM regions WHERE id = ?;', [$id]);
        if (count($results) > 0)
        {
            $geojson = [
                'type' => 'Feature',
                'geometry' => json_decode($results[0]->geom),
                'properties' => [
                    'id' => $region->id,
                    'name' => $region->name,
                    'code' => $region->code,
                ]
            ];
            return response(json_encode($geojson));
        }
        else
            return abort(404,'Region ' . $id . ' not found');
    }
}
