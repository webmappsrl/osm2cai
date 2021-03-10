<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AreaController extends Controller
{
    public function geojson(string $id)
    {
        $area = Area::find($id);
        $sectors = $area->sectorsIds();
        $results = Sector::whereIn('id', $sectors)->select('id', DB::raw('ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom'))->get();
        if (count($results) > 0) {
            $geojson = [
                'type' => 'FeatureCollection',
                'features' => [],
                'properties' => [
                    'id' => $area->id,
                    'name' => $area->name,
                    'code' => $area->code,
                    'full_code' => $area->full_code,
                    'province' => $area->province->name,
                    'region' => $area->province->region->name
                ]
            ];

            foreach ($results as $result) {
                $sector = Sector::find($result->id);
                $geojson['features'][] =
                    [
                        'type' => 'Feature',
                        'geometry' => json_decode($result->geom),
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
            }

            return response(json_encode($geojson));
        } else
            return abort(404, 'Area ' . $id . ' not found');
    }
}
