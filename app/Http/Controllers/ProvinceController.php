<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\Support\Facades\DB;

class ProvinceController extends Controller
{
    public function geojson(string $id)
    {
        $province = Province::find($id);
        $results = DB::select('SELECT ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom FROM provinces WHERE id = ?;', [$id]);
        if (count($results) > 0)
        {
            $geojson = [
                'type' => 'Feature',
                'geometry' => json_decode($results[0]->geom),
                'properties' => [
                    'id' => $province->id,
                    'name' => $province->name,
                    'code' => $province->code,
                    'full_code' => $province->full_code,
                    'region' => $province->region->name
                ]
            ];
            return response(json_encode($geojson));
        }
        else
            return abort(404,'Province ' . $id . ' not found');
    }
}
