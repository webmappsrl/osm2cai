<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Support\Facades\DB;

class SectorController extends Controller
{
    public function geojson(string $id)
    {
        $sector = Sector::find($id);
        $results = DB::select('SELECT ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom FROM sectors WHERE id = ?;', [$id]);
        if (count($results) > 0)
        {
            $geojson = [
                'type' => 'Feature',
                'geometry' => json_decode($results[0]->geom),
                'properties' => [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'code' => $sector->code,
                    'full_code' => $sector->full_code,
                    'area' => $sector->area->name,
                    'province' => $sector->area->province->name,
                    'region' => $sector->area->province->region->name
                ]
            ];
            return response(json_encode($geojson));
        }
        else
            return abort(404,'Sector ' . $id . ' not found');
    }
}
