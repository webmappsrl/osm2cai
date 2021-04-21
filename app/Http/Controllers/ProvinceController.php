<?php

namespace App\Http\Controllers;

use App\Models\Province;
use App\Models\Sector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProvinceController extends Controller {
    public function geojson(string $id) {
        $province = Province::find($id);
        $sectors = $province->sectorsIds();
        $results = Sector::whereIn('id', $sectors)->select('id', DB::raw('ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom'))->get();
        if (count($results) > 0) {
            $geojson = [
                'type' => 'FeatureCollection',
                'features' => [],
                'properties' => [
                    'id' => $province->id,
                    'name' => $province->name,
                    'code' => $province->code,
                    'full_code' => $province->full_code,
                    'region' => $province->region->name,
                    'geojson_url' => \route('api.geojson.province', ['id' => $province->id]),
                    'shapefile_url' => route('api.shapefile.province', ['id' => $province->id]),
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
                            'region' => $sector->area->province->region->name,
                            'geojson_url' => \route('api.geojson.sector', ['id' => $sector->id]),
                            'shapefile_url' => route('api.shapefile.sector', ['id' => $sector->id]),
                        ]
                    ];
            }

            return response(json_encode($geojson));
        } else
            return response()->json(['Error' => 'Province ' . $id . ' not found'], 404);
    }

    public function shapefile(string $id) {
        $model = Province::find($id);
        $name = str_replace(" ", "_", $model->name);
        $shapefile = $model->getShapefile();

        return Storage::disk('public')->download($shapefile, $name . '.zip');
    }
}
